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


#include "test_css_parser.h"

using namespace std;

///////////////////////////////////////////////////////////////////////////////

TestCSSParser::TestCSSParser() {
}

bool TestCSSParser::RunTests(const std::string &which) {
  bool ret = true;

  // functional testing
  RUN_TEST(TestContainerSelector);
  RUN_TEST(TestComment);
  RUN_TEST(TestUrlTranslator);
  RUN_TEST(TestDeclarationParsing);
  RUN_TEST(TestMozTagStripping);
  RUN_TEST(TestIETags);

  // negative testing
  RUN_TEST(TestUnknownProperty);
  RUN_TEST(TestAtRuleStripping);

  // parameter testing
  RUN_TEST(TestNoSanitization);
  RUN_TEST(TestLineNumber);

  RUN_TEST(TestIdPrefix);
  RUN_TEST(TestBackgroundPosition);
  RUN_TEST(TestSelectorPatterns);

  return ret;
}

///////////////////////////////////////////////////////////////////////////////
// helpers

static char *func_translate_url(char *url, void *data) {
  assert(data);
  TestCSSParser *parser = (TestCSSParser*)data;
  return parser->TranslateUrl(url);
}

char *TestCSSParser::TranslateUrl(char *url) {
  string turl;
  turl = "translated";
  turl += url;
  turl += "translated";
  return strdup(turl.c_str());
}

bool TestCSSParser::Test(const char *input, const char *expected,
                         const char *container_selector /* = NULL */,
                         const char *expected_error /* = "" */,
                         bool decl_only /* = false */,
                         int line_no /* = 0 */,
                         bool translate_url /* = false */,
                         const char *identifier_prefix /* = NULL */) {
  fbml_css_sanitizer sanitizer;
  sanitizer.container_selector = (char*)container_selector;
  sanitizer.identifier_prefix = (char*)identifier_prefix;
  if (translate_url) {
    sanitizer.pfunc_url_translator = func_translate_url;
    sanitizer.url_translate_data = (void*)this;
  } else {
    sanitizer.pfunc_url_translator = NULL;
    sanitizer.url_translate_data = NULL;
  }

  char *sanitized = NULL;
  char *error = NULL;
  int ret = fbml_sanitize_css((char*)input, decl_only ? 1 : 0, line_no,
                              &sanitizer, &sanitized, &error);
  if (ret) {
    printf("fbml_sanitize_css returned errors\n");
    return false;
  }
  if (sanitized == NULL) {
    printf("fbml_sanitize_css returned NULL sanitized\n");
    return false;
  }
  if (error == NULL) {
    printf("fbml_sanitize_css returned NULL error\n");
    return false;
  }
  if (strcmp(sanitized, expected)) {
    printf("fbml_sanitize_css returned different value:\n");
    printf("Expected: [%s]\n", expected);
    printf("Actual: [%s]\n", sanitized);
    printf("Error: [%s]\n", error);
    return false;
  }
  if (strcmp(error, expected_error)) {
    printf("fbml_sanitize_css returned different error:\n");
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

bool TestCSSParser::TestContainerSelector() {
  if (!Test("selector { color: red;}",
            "#app123 selector { color: red; }\n",
            "#app123")) return false;

  if (!Test("h1,h2 { color: red }",
            "#app123 h1, #app123 h2 { color: red; }\n",
            "#app123")) return false;

  if (!Test(".center { text-align: center }",
            "#app123 .center { text-align: center; }\n",
            "#app123")) return false;

  if (!Test("input[type=\"text\"] { background-color: blue }",
            "#app123 input[type=\"text\"] { background-color: blue; }\n",
            "#app123")) return false;

  if (!Test("#green { color: green }",
            "#app123 #myid_green { color: green; }\n",
            "#app123", "", false, 0, false, "myid")) return false;

  if (!Test("p#para1 { text-align: center; color: green }",
            "#app123 p#myid_para1 { text-align: center; color: green; }\n",
            "#app123", "", false, 0, false, "myid")) return false;

  if (!Test("h1, h2, .center, input[type=\"text\"], #green, p#para1 { text-align: center; color: green }",
            "#app123 h1, #app123 h2, #app123 .center, #app123 input[type=\"text\"], #app123 #myid_green, #app123 p#myid_para1 { text-align: center; color: green; }\n",
            "#app123", "", false, 0, false, "myid")) return false;

  if (!Test("selector1 selector2 { color: red; color: green}",
            "#app123 selector1 selector2 { color: green; }\n",
            "#app123")) return false;

  if (!Test("a:link { color: #FF0000}",
            "#app123 a:link { color: #ff0000; }\n",
            "#app123")) return false;

  if (!Test("p:first-line { color: #FF0000}",
            "#app123 p:first-line { color: #ff0000; }\n",
            "#app123")) return false;

  return true;
}

bool TestCSSParser::TestUnknownProperty() {
  if (!Test("selector1 selector2 { colorr: red;}",
            "#app123 selector1 selector2 {  }\n",
            "#app123",
            "CSS Error (line 0 char 30): PEUnknownProperty  PEDeclDropped\n"))
    return false;

  return true;
}

bool TestCSSParser::TestComment() {
  if (!Test("selector { color: /* yeah, some strong color */ red;}",
            "#app123 selector { color: red; }\n",
            "#app123"))
    return false;

  return true;
}

bool TestCSSParser::TestUrlTranslator() {
  if (!Test("td { background-image: url() }",
            "#app123 td { background-image: url(); }\n",
            "#app123"))
    return false;

  if (!Test("td { background-image: url() }",
            "#app123 td { background-image: url(translatedtranslated); }\n",
            "#app123", "", false, 0, true))
    return false;

  if (!Test("td { background-image: url(TESTURI) }",
            "#app123 td { background-image: url(TESTURI); }\n",
            "#app123"))
    return false;

  if (!Test("td { background-image: url(TESTURI) }",
            "#app123 td { background-image: url(translatedTESTURItranslated); }\n",
            "#app123", "", false, 0, true))
    return false;

  return true;
}

bool TestCSSParser::TestDeclarationParsing() {
  if (!Test("color: /* yeah, some strong color */ red;",
            "color: red;",
            "#app123", "", true))
    return false;

  if (!Test("background-image: url(TESTURI)",
            "background-image: url(TESTURI);",
            "#app123", "", true))
    return false;

  if (!Test("background-image: url(TESTURI)",
            "background-image: url(translatedTESTURItranslated);",
            "#app123", "", true, 0, true))
    return false;

  return true;
}

bool TestCSSParser::TestMozTagStripping() {
  if (!Test(".th_list { border-top: none; }",
            "#app123 .th_list { border-top: medium none #ffffff; }\n",
            "#app123", "", false))
    return false;

  if (!Test(".th_list { border-top: none; border: 1px solid #bdc7d8; }",
            "#app123 .th_list { border: 1px solid #bdc7d8; }\n",
            "#app123", "", false))
    return false;

  if (!Test(".th_list { border: 1px solid #bdc7d8; border-top: none;}",
            "#app123 .th_list { border-style: none solid solid; border-color: #ffffff #bdc7d8 #bdc7d8; border-width: medium 1px 1px; }\n",
            "#app123", "", false))
    return false;

  return true;
}

bool TestCSSParser::TestAtRuleStripping() {
  if (!Test("@import url(TESTURI); \nselector { color: #FF0000}",
            "#app123 selector { color: #ff0000; }\n",
            "#app123",
            "CSS Error (line 0 char 8): PEUnknownAtRule\n")) return false;

  return true;
}

bool TestCSSParser::TestNoSanitization() {
  if (!Test("selector { color: red;}",
            "selector { color: red; }\n")) return false;

  return true;
}

bool TestCSSParser::TestLineNumber() {
  if (!Test("@import url(TESTURI); \nselector { color: #FF0000}",
            "#app123 selector { color: #ff0000; }\n",
            "#app123",
            "CSS Error (line 10 char 8): PEUnknownAtRule\n",
            false, 10)) return false;

  return true;
}

bool TestCSSParser::TestIETags() {
  if (!Test("td { filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='',  sizingMethod='sc\"ale'); }",
            "#app123 td { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='translatedtranslated', sizingMethod='scale'); }\n",
            "#app123", "", false, 0, true))
    return false;

  if (!Test("td { filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=\"sc'ale\"); }",
            "#app123 td { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale'); }\n",
            "#app123", "", false, 0, true))
    return false;

  if (!Test("td { filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(); }",
            "#app123 td { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(); }\n",
            "#app123", "", false, 0, true))
    return false;

  if (!Test("td { filter:progid:DXImageTransform.Microsoft.Alpha(opacity=50) }",
            "#app123 td { filter: progid:DXImageTransform.Microsoft.Alpha(opacity=50); }\n",
            "#app123", "", false, 0, true))
    return false;

  if (!Test("td { filter:progid:DXImageTransform.Microsoft.Alpha(opacity=50) progid:DXImageTransform.Microsoft.Alpha(opacity=50) } th { color: #FFFFFF;}",
            "#app123 td { filter: progid:DXImageTransform.Microsoft.Alpha(opacity=50) progid:DXImageTransform.Microsoft.Alpha(opacity=50); }\n#app123 th { color: #ffffff; }\n",
            "#app123", "", false, 0, true))
    return false;

  if (!Test("td { filter:alpha(opacity=50) }",
            "#app123 td { filter: alpha(opacity=50); }\n",
            "#app123", "", false, 0, true))
    return false;

  if (!Test("td { cursor: hand;}",
            "#app123 td { cursor: hand; }\n",
            "#app123")) return false;

  if (!Test("td { display: inline-block;}",
            "#app123 td { display: inline-block; }\n",
            "#app123")) return false;

  if (!Test("td { word-wrap:break-word;}",
            "#app123 td { word-wrap: break-word; }\n",
            "#app123")) return false;

  return true;
}

bool TestCSSParser::TestIdPrefix() {
  if (!Test("#green { color: green }",
            "#app_content_123 #app123_green { color: green; }\n",
            "#app_content_123")) return false;

  if (!Test("#app123_green { color: green }",
            "#app_content_123 #app123_green { color: green; }\n",
            "#app_content_123")) return false;

  return true;
}

bool TestCSSParser::TestBackgroundPosition() {
  if (!Test("a { background-position: 0 0;}",
            "#app_content_123 a { background-position: 0pt 0pt; }\n",
            "#app_content_123")) return false;

  return true;
}

bool TestCSSParser::TestSelectorPatterns() {
  if (!Test("tr > td { color: green }",
            "#app123 tr > td { color: green; }\n",
            "#app123")) return false;

  if (!Test("tr + td { color: green }",
            "#app123 tr + td { color: green; }\n",
            "#app123")) return false;

  if (!Test("tr > * { color: green }",
            "#app123 tr > * { color: green; }\n",
            "#app123")) return false;

  return true;
}
