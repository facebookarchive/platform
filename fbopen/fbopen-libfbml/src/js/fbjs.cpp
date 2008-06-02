/******BEGIN LICENSE BLOCK*******
*
* Common Public Attribution License Version 1.0.
*
* The contents of this file are subject to the Common Public Attribution
* License Version 1.0 (the "License") you may not use this file except in
* compliance with the License. You may obtain a copy of the License at
* http://developers.facebook.com/fbopen/cpal.html. The License is based
* on the Mozilla Public License Version 1.1 but Sections 14 and 15 have
* been added to cover use of software over a computer network and provide
* for limited attribution for the Original Developer. In addition, Exhibit A
* has been modified to be consistent with Exhibit B.
* Software distributed under the License is distributed on an "AS IS" basis,
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
* for the specific language governing rights and limitations under the License.
* The Original Code is Facebook Open Platform.
* The Original Developer is the Initial Developer.
* The Initial Developer of the Original Code is Facebook, Inc.  All portions
* of the code written by Facebook, Inc are
* Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
*
*
********END LICENSE BLOCK*********/

#include "fbjs.h"
#include "base.h"
#include "jsscript.h"

using namespace std;

///////////////////////////////////////////////////////////////////////////////
// statics

static JSBool global_enumerate(JSContext *cx, JSObject *obj) {
  return JS_TRUE;
}

static JSBool global_resolve(JSContext *cx, JSObject *obj, jsval id,
                             uintN flags, JSObject **objp) {
  return JS_TRUE;
}

static JSClass global_class = {
  "global", JSCLASS_NEW_RESOLVE | JSCLASS_GLOBAL_FLAGS,
  JS_PropertyStub,  JS_PropertyStub,
  JS_PropertyStub,  JS_PropertyStub,
  global_enumerate, (JSResolveOp) global_resolve,
  JS_ConvertStub,   JS_FinalizeStub,
  JSCLASS_NO_OPTIONAL_MEMBERS
};

void FBJSParser::ErrorReporter(JSContext *cx, const char *message,
                               JSErrorReport *report) {
  string *error = (string*)cx->data;
  if (report) {
    char buf[128];
    snprintf(buf, sizeof(buf), "JS Error (line %d char %d): ",
             report->lineno, (int)(report->tokenptr - report->linebuf));
    *error += buf;
  } else {
    *error += "JS Error: ";
  }
  *error += message;
}

const char * FBJSParser::DecompileHookFunc(void *obj, JSOp op, const char *s,
                                           int lineno) {
  FBJSParser *parser = reinterpret_cast<FBJSParser*>(obj);
  switch (op) {
  case -1:
    return parser->replaceProperty(s);
  case -2:
    return parser->replaceElement(s);
  case JSOP_THIS:
    return parser->replaceThis();
  case JSOP_ARGUMENTS:
    return parser->replaceArguments();
  case JSOP_DEFFUN:
    return parser->prefixFunction();
  case JSOP_ENTERWITH:
    parser->onWith(lineno);
    break;
  case JSOP_NAME:
  case JSOP_GETVAR:
  case JSOP_GETGVAR:
  case JSOP_SETVAR:
  case JSOP_SETGVAR:
  case JSOP_SETNAME:
  case JSOP_SETCONST:
    return parser->replaceIdentifier(s);
  default:
    break;
  }
  return s;
}

///////////////////////////////////////////////////////////////////////////////
// constructor/destructor

FBJSParser::FBJSParser(fbml_js_sanitizer *sanitizer)
  : m_sanitizer(sanitizer), m_rt(0), m_cx(0), m_output(0) {
  ASSERT(m_sanitizer);
  ASSERT(m_sanitizer->identifier_prefix);
  ASSERT(m_sanitizer->this_replacement);
  ASSERT(m_sanitizer->arguments_replacement);
  ASSERT(m_sanitizer->banned_properties);
  ASSERT(m_sanitizer->banned_property_replacement);

  for (char **p = m_sanitizer->banned_properties; *p; p++) {
    m_banned_properties.insert(*p);
  }

  m_rt = JS_NewRuntime(64L * 1024L * 1024L);
  if (!m_rt) {
    m_error = "JS_NewRuntime failed";
    return;
  }

  m_cx = JS_NewContext(m_rt, 8192);
  if (!m_cx) {
    m_error = "JS_NewContext failed";
    return;
  }
  m_cx->decompileHookObject = (void*)this;
  m_cx->decompileHookFunc = DecompileHookFunc;

  m_cx->data = &m_error;
  JS_SetErrorReporter(m_cx, ErrorReporter);
}

FBJSParser::~FBJSParser() {
  clear();
  if (m_cx) JS_DestroyContext(m_cx);
  if (m_rt) JS_DestroyRuntime(m_rt);
  JS_ShutDown();
}

void FBJSParser::clear() {
  if (m_output) free(m_output);
  m_error.clear();
}

char *FBJSParser::detachOutput() {
  char *ret = m_output;
  m_output = NULL;
  return ret;
}

///////////////////////////////////////////////////////////////////////////////
// parse

JSObject *FBJSParser::createGlobalObject() {
  JSObject *glob = JS_NewObject(m_cx, &global_class, NULL, NULL);
  if (!glob) m_error = "JS_NewObject failed";
  return glob;
}

int FBJSParser::parse(const char *filename) {
  clear();
  return parseImpl(JS_CompileFile(m_cx, createGlobalObject(), filename));
}

int FBJSParser::parse(const char *chars, size_t length, int lineno,
                      const char *filename /* = NULL */) {
  clear();
  return parseImpl(JS_CompileScriptForPrincipals(m_cx, createGlobalObject(),
                                                 NULL, chars, length,
                                                 filename, lineno));
}

int FBJSParser::parseImpl(JSScript *js) {
  if (!js) {
    if (m_error.empty()) m_error = "JS_Compile failed";
    return -1;
  }

  unsigned int indent = (m_sanitizer->pretty ? 0 : JS_DONT_PRETTY_PRINT);
  JSString *decompiled = JS_DecompileScript(m_cx, js, NULL, indent);
  js_DestroyScript(m_cx, js);
  if (!decompiled) {
    if (m_error.empty()) m_error = "JS_DecompileScript failed";
    return -1;
  }

  m_output = js_DeflateString(NULL, decompiled->chars, decompiled->length);
  if (!m_output) {
    if (m_error.empty()) m_error = "js_DeflateString failed";
    return -1;
  }

  return 0;
}

///////////////////////////////////////////////////////////////////////////////
// code replacement

const char *FBJSParser::replaceIdentifier(const char *name) {
  ASSERT(name && *name);

  m_replaced = m_sanitizer->identifier_prefix;
  m_replaced += name;
  return m_replaced.c_str();
}

const char *FBJSParser::replaceProperty(const char *name) {
  if (m_banned_properties.find(name) == m_banned_properties.end()) {
    return name;
  }
  return m_sanitizer->banned_property_replacement;
}

const char *FBJSParser::replaceElement(const char *name) {
  bool numeric = true;
  for (const char *p = name; *p; p++) {
    if (*p < '0' || *p > '9') {
      numeric = false;
      break;
    }
  }
  if (numeric) return name;
  snprintf(m_buffer, BUFFER_SIZE, m_sanitizer->array_element_format, name);
  return m_buffer;
}

const char *FBJSParser::prefixFunction() {
  return m_sanitizer->identifier_prefix;
}

const char *FBJSParser::replaceThis() {
  return m_sanitizer->this_replacement;
}

const char *FBJSParser::replaceArguments() {
  return m_sanitizer->arguments_replacement;
}

void FBJSParser::onWith(int lineno) {
  char buf[128];
  snprintf(buf, sizeof(buf),
           "JS Error (line %d): \"with\" is not supported.\n", lineno);
  m_error += buf;
}
