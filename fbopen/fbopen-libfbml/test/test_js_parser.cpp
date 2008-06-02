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

#include "test_js_parser.h"

using namespace std;

///////////////////////////////////////////////////////////////////////////////

TestJSParser::TestJSParser() {
}

bool TestJSParser::RunTests(const std::string &which) {
  bool ret = true;

  RUN_TEST(TestIdentifierPrefix);
  RUN_TEST(TestIdentifierNoPrefix);
  RUN_TEST(TestArgumentsReplacement);
  RUN_TEST(TestThisReplacement);
  RUN_TEST(TestIndexReference);
  RUN_TEST(TestComments);
  RUN_TEST(TestRegEx);
  RUN_TEST(TestWithError);
  RUN_TEST(TestNoSanitization);
  RUN_TEST(TestLineNumber);
  RUN_TEST(TestBannedProperties);
  RUN_TEST(TestLanguageConstructs);

  return ret;
}

///////////////////////////////////////////////////////////////////////////////
// helpers

static char *func_translate_eh(char *eh, void *data) {
  assert(data);
  TestJSParser *parser = (TestJSParser*)data;
  return parser->TranslateEventHandler(eh);
}

char *TestJSParser::TranslateEventHandler(char *eh) {
  string teh;
  teh = "translated";
  teh += eh;
  teh += "translated";
  return strdup(teh.c_str());
}

bool TestJSParser::Test(const char *input, const char *expected,
                        const char *identifier_prefix /* = NULL */,
                        const char *expected_error /* = "" */,
                        int line_no /* = 0 */,
                        bool translate_eh /* = false */,
                        const char *this_replacement /* = NULL */) {
  fbml_js_sanitizer sanitizer;
  fbml_js_sanitizer_init(&sanitizer);
  if (identifier_prefix) {
    sanitizer.identifier_prefix = (char*)identifier_prefix;
  }
  if (this_replacement) {
    sanitizer.this_replacement = (char*)this_replacement;
  }
  if (translate_eh) {
    sanitizer.pfunc_eh_translator = func_translate_eh;
    sanitizer.eh_translate_data = (void*)this;
  } else {
    sanitizer.pfunc_eh_translator = NULL;
    sanitizer.eh_translate_data = NULL;
  }

  char *sanitized = NULL;
  char *error = NULL;
  int ret = fbml_sanitize_js((char*)input, strlen(input), 0, line_no,
                             &sanitizer, &sanitized, &error);
  if (ret) {
    printf("fbml_sanitize_js returned errors\n");
    return false;
  }
  if (sanitized == NULL) {
    printf("fbml_sanitize_js returned NULL sanitized\n");
    return false;
  }
  if (error == NULL) {
    printf("fbml_sanitize_js returned NULL error\n");
    return false;
  }
  if (strcmp(sanitized, expected)) {
    printf("fbml_sanitize_js returned different value:\n");
    printf("Expected: [%s]\n", expected);
    printf("Actual: [%s]\n", sanitized);
    printf("Error: [%s]\n", error);
    return false;
  }
  if (strcmp(error, expected_error)) {
    printf("fbml_sanitize_js returned different error:\n");
    printf("Expected Error: [%s]\n", expected_error);
    printf("Actual Error: [%s]\n", error);
    return false;
  }
  free(sanitized);
  free(error);
  return true;
}

///////////////////////////////////////////////////////////////////////////////
// unit tests

bool TestJSParser::TestIdentifierPrefix() {
  if (!Test("var a = f();",
            "var app123_a = app123_f();",
            "app123_")) return false;

  if (!Test("var a = new f();",
            "var app123_a = new app123_f;",
            "app123_")) return false;

  if (!Test("var a = new f(b);",
            "var app123_a = new app123_f(app123_b);",
            "app123_")) return false;

  if (!Test("a = f();",
            "app123_a = app123_f();",
            "app123_")) return false;

  if (!Test("a = a + b;",
            "app123_a = app123_a + app123_b;",
            "app123_")) return false;

  if (!Test("a = a + (b - c);",
            "app123_a = app123_a + (app123_b - app123_c);",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestIdentifierNoPrefix() {
  if (!Test("a.test = 1;",
            "app123_a.test = 1;",
            "app123_")) return false;

  if (!Test("var hello = {foo:bar};",
            "var app123_hello = {foo:app123_bar};",
            "app123_")) return false;

  if (!Test("var hello = {foo:'bar'};",
            "var app123_hello = {foo:\"bar\"};",
            "app123_")) return false;

  if (!Test("var hello = {'foo':'bar'};",
            "var app123_hello = {foo:\"bar\"};",
            "app123_")) return false;

  if (!Test("var hello = {'foo':'ba\"r'};",
            "var app123_hello = {foo:\"ba\\\"r\"};",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestArgumentsReplacement() {
  if (!Test("var a = arguments;",
            "var app123_a = app123_arguments;",
            "app123_")) return false;

  if (!Test("function test(a, b) { var c = arguments + a;}",
            "\nfunction app123_test(a, b) {var app123_c = arg(arguments) + a;}\n",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestThisReplacement() {
  if (!Test("this.test = 1;",
            "(ref(this)).test = 1;",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestIndexReference() {
  if (!Test("a['test'] = 1;",
            "app123_a.test = 1;",
            "app123_")) return false;

  if (!Test("a['test1' + test2] = 1;",
            "app123_a[idx(\"test1\" + app123_test2)] = 1;",
            "app123_")) return false;

  if (!Test("a[b] = 1;",
            "app123_a[idx(app123_b)] = 1;",
            "app123_")) return false;

  if (!Test("a[1] = 1;",
            "app123_a[1] = 1;",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestComments() {
  if (!Test("test /* comment */ = 1; // more",
            "app123_test = 1;",
            "app123_")) return false;

  if (!Test("test = 1; <!-- test",
            "app123_test = 1;",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestRegEx() {
  if (!Test("test.replace(/test/)",
            "app123_test.replace(/test/);",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestWithError() {
  if (!Test("with(o) {test}",
            "with (app123_o) {app123_test;}",
            "app123_",
            "JS Error (line 0): \"with\" is not supported.\n"))
    return false;

  return true;
}

bool TestJSParser::TestNoSanitization() {
  if (!Test("var a = f();",
            "var a = f();", "")) return false;

  return true;
}

bool TestJSParser::TestLineNumber() {
  if (!Test("with(o) {test}",
            "with (app123_o) {app123_test;}",
            "app123_",
            "JS Error (line 10): \"with\" is not supported.\n",
            10))
    return false;

  return true;
}

bool TestJSParser::TestBannedProperties() {
  if (!Test("a.__proto__ = 1;",
            "app123_a.__unknown__ = 1;",
            "app123_")) return false;

  if (!Test("a['__proto__'] = 1;",
            "app123_a.__unknown__ = 1;",
            "app123_")) return false;

  if (!Test("var hello = {__proto__:bar};",
            "var app123_hello = {__unknown__:app123_bar};",
            "app123_")) return false;

  if (!Test("var hello = {__proto__ getter:bar};",
            "var app123_hello = {__unknown__ getter:app123_bar};",
            "app123_")) return false;

  if (!Test("o = {a:7, get b() {return this.a+1; }, set c(x) {this.a = x/2}};",
            "app123_o = {a:7, get b() {return (ref(this)).a + 1;}, set c(x) {(ref(this)).a = x / 2;}};",
            "app123_")) return false;

  if (!Test("o = {a:7, get watch() {return this.a+1; }, set caller(x) {this.a = x/2}};",
            "app123_o = {a:7, get __unknown__() {return (ref(this)).a + 1;}, set __unknown__(x) {(ref(this)).a = x / 2;}};",
            "app123_")) return false;

  return true;
}

bool TestJSParser::TestLanguageConstructs() {
  if (!Test("a = [b, c]",
            "app123_a = {app123_b, app123_c};",
            "app123_")) return false;

  if (!Test("for (a[b] in c);",
            "for (app123_a[idx(app123_b)] in app123_c) {}",
            "app123_")) return false;

  if (!Test("a[b] = new Test(); delete a[b];",
            "app123_a[idx(app123_b)] = new app123_Test;delete app123_a[idx(app123_b)];",
            "app123_")) return false;

  return true;
}
