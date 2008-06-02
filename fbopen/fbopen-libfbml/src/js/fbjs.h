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


#ifndef __FBJS_PARSER_H__
#define __FBJS_PARSER_H__

#include "jscntxt.h"
#include "jsparse.h"
#include "jsstr.h"
#include "fbml.h"
#include <string>
#include <set>

struct stdltstr {
  bool operator()(const std::string &s1, const std::string &s2) const {
    return strcmp(s1.c_str(), s2.c_str()) < 0;
  }
};

///////////////////////////////////////////////////////////////////////////////

class FBJSParser {
 public:
  FBJSParser(fbml_js_sanitizer *sanitizer);
  ~FBJSParser();

  /**
   * Parse an input file or a string.
   */
  int parse(const char *filename);
  int parse(const char *chars, size_t length, int lineno,
            const char *filename = NULL);

  /**
   * Output.
   */
  const char *getOutput() const { return m_output;}
  char *detachOutput();

  /**
   * What error did we get?
   */
  const char *getError() const { return m_error.c_str();}

  // error handler
  static void ErrorReporter(JSContext *cx, const char *message,
                            JSErrorReport *report);
  // code replacement hook
  static const char * DecompileHookFunc(void *obj, JSOp op, const char *s,
                                        int lineno);

 private:
  fbml_js_sanitizer *m_sanitizer;
  std::set<std::string, stdltstr> m_banned_properties;

  JSRuntime *m_rt;
  JSContext *m_cx;
  char *m_output;
  std::string m_error;

  std::string m_replaced;
  static const int BUFFER_SIZE = 256;
  char m_buffer[BUFFER_SIZE];

  JSObject *createGlobalObject();
  int parseImpl(JSScript *js);
  void clear();

  // code replacement functions
  const char *replaceIdentifier(const char *name);
  const char *replaceProperty(const char *name);
  const char *replaceElement(const char *name);
  const char *prefixFunction();
  const char *replaceThis();
  const char *replaceArguments();
  void onWith(int lineno);
};

///////////////////////////////////////////////////////////////////////////////

#endif // __FBJS_PARSER_H__
