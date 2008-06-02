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

#include "test_fbml_parser.h"

using namespace std;

///////////////////////////////////////////////////////////////////////////////

TestFBMLParser::TestFBMLParser() {
}

bool TestFBMLParser::RunTests(const std::string &which) {
  bool ret = true;

  RUN_TEST(TestSanity);
  RUN_TEST(TestWhiteSpace);
  RUN_TEST(TestTagExpanding);
  RUN_TEST(TestUnknownElement);
  RUN_TEST(TestUnclosedTags);
  RUN_TEST(TestMisplacedContents);
  RUN_TEST(TestIllegalContents);
  RUN_TEST(TestEmptyTags);
  RUN_TEST(TestSelfClosedTags);
  RUN_TEST(TestAttributes);
  RUN_TEST(TestHead);
  RUN_TEST(TestComment);
  RUN_TEST(TestEntity);
  RUN_TEST(TestStyleElementSanitization);
  RUN_TEST(TestStyleAttributeSanitization);
  RUN_TEST(TestScriptElementSanitization);
  RUN_TEST(TestScriptEventHandlerSanitization);
  RUN_TEST(TestNoSanitization);
  RUN_TEST(TestAttributeRewriting);
  RUN_TEST(TestLineNumber);
  RUN_TEST(TestExpandTwice);
  RUN_TEST(TestTagFlag);
  RUN_TEST(TestContextSchema);
  RUN_TEST(TestUnderScore);
  RUN_TEST(TestTagContainment);
  RUN_TEST(TestUTF8);
  return ret;
}

///////////////////////////////////////////////////////////////////////////////
// helpers

static void SetStyleFlags() {

  char *style_attrs[] = { "style", NULL};
  fbml_flaggable_attrs style_attr_flags;
  style_attr_flags.flag = FB_FLAG_ATTR_STYLE;
  style_attr_flags.attrs = style_attrs;

  char *style_tags[] = { "style", NULL};
  fbml_flaggable_tags style_tag_flags;
  style_tag_flags.flag = FB_FLAG_STYLE;
  style_tag_flags.tags = style_tags;

  fbml_flaggable_attrs * flagged_attrs [] = {&style_attr_flags, NULL};
  fbml_flaggable_tags * flagged_tags [] = {&style_tag_flags, NULL};
  char *attrs[] = { "style",  NULL };
  char *tags[] = { NULL };
  fbml_expand_tag_list(tags, attrs, flagged_tags, flagged_attrs, NULL);

}


static char *func_translate_url(char *url, void *data) {
  assert(data);
  TestFBMLParser *parser = (TestFBMLParser*)data;
  return parser->TranslateUrl(url);
}

char *TestFBMLParser::TranslateUrl(char *url) {
  string turl;
  turl = "urltr";
  turl += url;
  turl += "urltr";
  return strdup(turl.c_str());
}

static char *func_translate_eh(char *eh, void *data) {
  assert(data);
  TestFBMLParser *parser = (TestFBMLParser*)data;
  return parser->TranslateEventHandler(eh);
}



char *TestFBMLParser::TranslateEventHandler(char *eh) {
  string teh;
  teh = "ehtr";
  teh += eh;
  teh += "ehtr";
  return strdup(teh.c_str());
}


static char *func_rewrite(char *tag, char *name, char * attr, void *data) {
  assert(data);
  TestFBMLParser *parser = (TestFBMLParser*)data;
  return parser->RewriteAttr(tag, name, attr);
}

char *TestFBMLParser::RewriteAttr(char *tag, char *attr, char *val) {
  string teh;
  teh = "tag";
  teh += tag;
  teh += "attr";
  teh += attr;
  teh += "val";
  teh += val;
  return strdup(teh.c_str());
}



bool TestFBMLParser::Test(const char *input,
                          const char *expected,
                          const char *expected_error /* = "" */,
                          bool body_only /* = true */,
                          bool preserve_comment /* = false */,
                          bool internal_mode /* = false */,
                          const char *container_selector /* = NULL */,
                          const char *identifier_prefix /* = NULL */,
                          bool translate_url /* = false */,
                          bool translate_eh /* = false */,
                          const char *this_replacement /* = NULL */,
                          bool rewrite_attr /*=false*/
                          ) {
  fbml_css_sanitizer css_sanitizer;
  css_sanitizer.container_selector = (char*)container_selector;
  css_sanitizer.identifier_prefix = NULL;
  if (translate_url) {
    css_sanitizer.pfunc_url_translator = func_translate_url;
    css_sanitizer.url_translate_data = (void*)this;
  } else {
    css_sanitizer.pfunc_url_translator = NULL;
    css_sanitizer.url_translate_data = NULL;
  }

  fbml_js_sanitizer js_sanitizer;
  fbml_js_sanitizer_init(&js_sanitizer);
  if (identifier_prefix) {
    js_sanitizer.identifier_prefix = (char*)identifier_prefix;
  }
  if (this_replacement) {
    js_sanitizer.this_replacement = (char*)this_replacement;
  }
  if (translate_eh) {
    js_sanitizer.pfunc_eh_translator = func_translate_eh;
    js_sanitizer.eh_translate_data = (void*)this;
  } else {
    js_sanitizer.pfunc_eh_translator = NULL;
    js_sanitizer.eh_translate_data = NULL;
  }

  fbml_attr_rewriter attr_rewriter;
  if (rewrite_attr) {
    attr_rewriter.pfunc_rewriter = func_rewrite;
    attr_rewriter.rewrite_data   = (void *)this;
  } else {
    attr_rewriter.pfunc_rewriter = NULL;
    attr_rewriter.rewrite_data   = NULL;
  }

  char *sanitized = NULL;
  char *error = NULL;
  fbml_node *tree = NULL;

  int ret = fbml_parse((char*)input, body_only, preserve_comment, internal_mode,
                       &css_sanitizer, &js_sanitizer, &attr_rewriter,  NULL, NULL, &tree, &error);
  if (ret) {
    printf("fbml_parse returned errors\n");
    return false;
  }
  if (tree == NULL) {
    printf("fbml_parse returned NULL tree\n");
    return false;
  }
  sanitized = fbml_node_print(tree);
  if (sanitized == NULL) {
    printf("fbml_parse returned NULL sanitized\n");
    return false;
  }
  if (error == NULL) {
    printf("fbml_parse returned NULL error\n");
    return false;
  }
  if (strcmp(sanitized, expected)) {
    printf("fbml_parse returned different value:\n");
    printf("Expected: [%s]\n", expected);
    printf("Actual: [%s]\n", sanitized);
    printf("Error: [%s]\n", error);
    return false;
  }
  if (strcmp(error, expected_error)) {
    printf("fbml_parse returned different error:\n");
    printf("Expected Error: [%s]\n", expected_error);
    printf("Actual Error: [%s]\n", error);
    return false;
  }
  free(sanitized);
  free(error);
  fbml_node_free(tree);
  return true;
}

bool TestFBMLParser::ParseTree(const char *input, fbml_node **tree,
                               fbml_flaggable_tags **flaggable_tags) {
  char *error = NULL;
  int ret = fbml_parse((char*)input, 0, 0, 0, 0, 0, NULL, flaggable_tags, NULL, tree, &error);
  if (ret) {
    printf("fbml_parse returned errors\n");
    return false;
  }
  if (tree == NULL) {
    printf("fbml_parse returned NULL tree\n");
    return false;
  }
  if (error == NULL) {
    printf("fbml_parse returned NULL error\n");
    return false;
  }
  free(error);
  return true;
}

///////////////////////////////////////////////////////////////////////////////
// unit tests

bool TestFBMLParser::TestSanity() {
  if (!Test("<b>test</b>",
            "<body><b>test</b></body>")) return false;
  return true;
}

bool TestFBMLParser::TestWhiteSpace() {
  if (!Test("\r\n<b>test1</b>\r\n<b>test2</b>\r\n",
            "<body>\n<b>test1</b>\n<b>test2</b>\n</body>")) return false;
  return true;
}

bool TestFBMLParser::TestUnclosedTags() {
  SetStyleFlags();
  if (!Test("<table<tr><td>test</table>",
            "<body><table><tr><td>test</td></tr></table></body>"))
    return false;

  if (!Test("<form><input id=a1><input id=a2>",
            "<body><form><input id=\"a1\"></input><input id=\"a2\"></input></form></body>"))
    return false;

  if (!Test("<style>.test { color: red;}</style>",
            "<body><style>.test { color: red; }\n</style></body>"))
    return false;

  if (!Test("<style>.test { color: red;}",
            "<body><style></style>.test { color: red;}</body>"))
    return false;

  return true;
}

bool TestFBMLParser::TestMisplacedContents() {
  if (!Test("<table><form id=f1><tr><td><input id=i1></td></tr></form></table>",
            "<body><table><form id=\"f1\"><tr><td><input id=\"i1\"></input></td></tr></form></table></body>"))
    return false;

  if (!Test("<table><form id=f1><tr><td><input id=i1></td></tr></form>"
            "<form id=f2><tr><td><input id=i2></td></tr></form></table>",
            "<body><table><form id=\"f1\"><tr><td><input id=\"i1\"></input></td></tr></form><form id=\"f2\"><tr><td><input id=\"i2\"></input></td></tr></form></table></body>"))
    return false;

  return true;
}

bool TestFBMLParser::TestIllegalContents() {
  if (!Test("<table><tr><td>row1</td></tr><form><input><tr><td>row2</td></tr></table",
            "<body><table><tr><td>row1</td></tr><form><input></input><tr><td>row2</td></tr></form></table></body>"))
    return false;

  if (!Test("<table><tr><td>row1</td></tr> \r\n<tr><td>row2</td></tr></table>",
            "<body><table><tr><td>row1</td></tr> \n<tr><td>row2</td></tr></table></body>"))
    return false;

  if (!Test("<table>\n<tr><td>row1</td></tr></table>",
            "<body><table>\n<tr><td>row1</td></tr></table></body>"))
    return false;

  if (!Test("<table><input b='c' d='e' /> </table>",
            "<body><table><input b=\"c\" d=\"e\"></input> </table></body>"))
    return false;

  return true;
}

bool TestFBMLParser::TestEmptyTags() {
  if (!Test("<b/><a>test</a>",
            "<body><b></b><a>test</a></body>")) return false;

  if (!Test("<br/><a>test</a>",
            "<body><br><a>test</a></body>")) return false;

  if (!Test("<textarea/><a>test</a>",
            "<body><textarea></textarea><a>test</a></body>")) return false;

  return true;
}

bool TestFBMLParser::TestSelfClosedTags() {
  if (!Test("<iframe /><a>test</a>",
            "<body><iframe></iframe><a>test</a></body>")) return false;

  if (!Test("<b a=1 /><a>test</a>",
            "<body><b a=\"1\"></b><a>test</a></body>")) return false;

  if (!Test("<br a=1 /><a>test</a>",
            "<body><br a=\"1\"><a>test</a></body>")) return false;

  if (!Test("<textarea /><a>test</a>",
            "<body><textarea></textarea><a>test</a></body>")) return false;

  if (!Test("<iframe border=1 /><a>test</a>",
            "<body><iframe border=\"1\"></iframe><a>test</a></body>")) return false;

  if (!Test("<textarea a=\"a\" /><a>test</a>",
            "<body><textarea a=\"a\"></textarea><a>test</a></body>")) return false;

  return true;
}

bool TestFBMLParser::TestUnknownElement() {
  if (!Test("<test>content</test>",
            "<body>content</body>",
            "FBML Error (line 1): unknown tag \"test\"\n")) return false;

  if (!Test("<test>cont<ent</test>\n<test>cont>ent</test>",
            "<body>cont\ncont&gt;ent</body>",
            "FBML Error (line 1): unknown tag \"test\"\n"
            "FBML Error (line 1): unknown tag \"ent\"\n"
            "FBML Error (line 2): unknown tag \"test\"\n")) return false;

  return true;
}

bool TestFBMLParser::TestTagExpanding() {
  if (fbml_tag_list_expanded()) {
    printf("Tag list shouldn't be expanded yet.\n");
    return false;
  }

  if (!Test("<fb:b>content</fb:b>",
            "<body>content</body>",
            "FBML Error (line 1): unknown tag \"fb:b\"\n")) return false;

  char *tags[] = { "fb:b", "fb:if-can-see", NULL };
  char *attrs[] = {NULL};
  fbml_expand_tag_list(tags, attrs, NULL, NULL, NULL);

  if (!fbml_tag_list_expanded()) {
    printf("Tag list should be expanded already.\n");
    return false;
  }

  if (!Test("<fb:b>content</fb:b>",
            "<body><fb:b>content</fb:b></body>")) return false;

  if (!Test("<FB:b>content</fb:b>",
            "<body><fb:b>content</fb:b></body>")) return false;

  if (!Test("<fb:if-can-see uid=\"1234515\"><b>test</b></fb:if-can-see>",
            "<body><fb:if-can-see uid=\"1234515\"><b>test</b></fb:if-can-see></body>")) return false;

  if (!Test("<fb:if-can-see><div/></fb:if-can-see>",
            "<body><fb:if-can-see><div></div></fb:if-can-see></body>")) return false;

  if (!Test("<div><fb:b>content</fb:b></div>",
            "<body><div><fb:b>content</fb:b></div></body>")) return false;

  // should be okay to expand it again with no crash or memory leak
  fbml_expand_tag_list(tags, attrs, NULL, NULL, NULL);

  if (!Test("<fb:b>content</fb:b>",
            "<body><fb:b>content</fb:b></body>")) return false;
  if (!Test("<fb:newer_tags>content</fb:newer_tags>",
            "<body>content</body>",
            "FBML Error (line 1): unknown tag \"fb:newer_tags\"\n")) return false;

  char *newer_tags[] = { "fb:b", "fb:newer_tags", "fb:if-can-see", NULL };
  fbml_expand_tag_list(newer_tags, attrs, NULL, NULL, NULL);

  if (!Test("<fb:b>content</fb:b>",
            "<body><fb:b>content</fb:b></body>")) return false;
  if (!Test("<fb:newer-tags>content</fb:newer_tags>",
            "<body><fb:newer-tags>content</fb:newer-tags></body>")) return false;

  return true;
}

bool TestFBMLParser::TestAttributes() {
  if (!Test("<b a=2>test</b>",
            "<body><b a=\"2\">test</b></body>")) return false;

  if (!Test("<b fb:k=2>test</b>",
            "<body><b fb:k=\"2\">test</b></body>")) return false;

  if (!Test("<b <t=2>test</b>",
            "<body><b>test</b></body>",
            "FBML Error (line 1): unknown tag \"t=2\"\n")) return false;

  if (!Test("<b \"<t\"=2>test</b>",
            "<body><b>test</b></body>",
            "FBML Error (line 1): unknown tag \"t\"=2\"\n")) return false;

  if (!Test("<b>test</b a=2><a>link</a>",
            "<body><b>test</b><a>link</a></body>")) return false;

  if (!Test("<input type=radio checked>",
            "<body><input type=\"radio\" checked=\"\"></input></body>")) return false;

  if (!Test("<input type=radio checked />",
            "<body><input type=\"radio\" checked=\"\"></input></body>")) return false;

  return true;
}

bool TestFBMLParser::TestHead() {
  SetStyleFlags();
  if (!Test("<head><title>Test Title</title><style>.test { color: red;}</style><script>function test() { alert(1); }</script></head><b>test</b>",
            "<head><title>Test Title</title><style>.test { color: red; }\n</style><script>\nfunction app_test() {app_alert(1);}\n</script></head><body><b>test</b></body>",

            "", false)) return false;

  return true;
}

bool TestFBMLParser::TestComment() {

  if (!Test("<b \"none\"<!--x--><<!--e-->script language=\"javascript\">"
            "alert> (\"hi!\");<<!--e-->/script>Hi!-->=\"yes\">",
            "<body><b none=\"\">&lt;script language=&quot;javascript&quot;&gt;alert&gt; (&quot;hi!&quot;);&lt;/script&gt;Hi!--&gt;=&quot;yes&quot;&gt;</b></body>")) return false;


  if (!Test("<b>test1</b><!-- comment --><b>test2</b>",
            "<body><b>test1</b><b>test2</b></body>")) return false;

  if (!Test("<b>test1</b><!-- comment --><b>test2</b>",
            "<body><b>test1</b><!-- comment --><b>test2</b></body>",
            "", true, true)) return false;

  return true;
}

bool TestFBMLParser::TestEntity() {

  if (!Test("<textarea>&amp;amp;</textarea>",
            "<body><textarea>&amp;amp;</textarea></body>")) return false;


  if (!Test("<b>&lt;&gt;&amp;&#039;</b>",
            "<body><b>&lt;&gt;&amp;&#039;</b></body>")) return false;

  if (!Test("<b a=\"&lt;&gt;&amp;&#039;\">test</b>",
            "<body><b a=\"&lt;&gt;&amp;'\">test</b></body>")) return false;

  if (!Test("<b a=\"&rsquo;\">&rsquo;</b>",
            "<body><b a=\"\x92\">&rsquo;</b></body>")) return false;

  return true;
}


bool TestFBMLParser::TestStyleElementSanitization() {
  SetStyleFlags();
  if (!Test("<head><style>.test { color: red;}</style></head><b>test</b>",
            "<head><style>#app123 .test { color: red; }\n</style></head><body><b>test</b></body>",

            "", false, false, false, "#app123")) return false;

  if (!Test("<style>.test { color: red;}</style>",
            "<body><style>#app123 .test { color: red; }\n</style></body>",

            "", true, false, false, "#app123")) return false;


  if (!Test("<style>.test { background-image: url(TESTURI);}</style>",
            "<body><style>#app123 .test { background-image: url(urltrTESTURIurltr); }\n</style></body>",
            "", true, false, false, "#app123", NULL, true)) return false;

  return true;
}

bool TestFBMLParser::TestStyleAttributeSanitization() {
  SetStyleFlags();
  if (!Test("<b style='color: red'>test</b>",
            "<body><b style=\"color: red;\">test</b></body>")) return false;

  if (!Test("<b style='background-image: url(TESTURI)'>test</b>",
            "<body><b style=\"background-image: url(urltrTESTURIurltr);\">test</b></body>",
            "", true, false, false, "#app123", NULL, true)) return false;
  return true;
}

bool TestFBMLParser::TestScriptElementSanitization() {
  SetStyleFlags();
  if (!Test("<head><script>a = 1 < 2</script></head>",
            "<head><script>APPJS_a = 1 < 2;</script></head><body></body>",
            "", false, false, false, "#app123", "APPJS_")) return false;


  if (!Test("<head><script>this.test = 1 < 2;</script></head>",
            "<head><script>THAT.test = 1 < 2;</script></head><body></body>",

            "", false, false, false, "#app123", "APPJS_", false, false, "THAT"))
    return false;

  if (!Test("<script>a = 1</script>",
            "<body><script>APPJS_a = 1;</script></body>",
            "", true, false, false, "#app123", "APPJS_")) return false;

  if (!Test("<script>this.test = 1;</script>",
            "<body><script>THAT.test = 1;</script></body>",
            "", true, false, false, "#app123", "APPJS_", false, false, "THAT"))
    return false;

  return true;
}

bool TestFBMLParser::TestScriptEventHandlerSanitization() {
  char *script_attrs[] = { "onclick", NULL};
  fbml_flaggable_attrs script_attr_flags;
  script_attr_flags.flag = FB_FLAG_ATTR_SCRIPT;
  script_attr_flags.attrs = script_attrs;
  fbml_flaggable_attrs * flagged_attrs [] = {&script_attr_flags, NULL};
  char *attrs[] = { "onclick",  NULL };
  char *tags[] = { NULL };
  fbml_expand_tag_list(tags, attrs, NULL, flagged_attrs, NULL);

  if (!Test("<b onclick='a = 1'>test</b>",
            "<body><b onclick=\"ehtrAPPJS_a = 1;ehtr\">test</b></body>",
            "", true, false, false, "#app123", "APPJS_", false, true))
    return false;

  if (!Test("<b OnClick='a = 1'>test</b>",
            "<body><b onclick=\"ehtrAPPJS_a = 1;ehtr\">test</b></body>",
            "", true, false, false, "#app123", "APPJS_", false, true))
    return false;

  return true;
}

bool TestFBMLParser::TestNoSanitization() {
  SetStyleFlags();


  if (!Test("<b style='background-image: url(TESTURI)'>test</b>",
            "<body><b style=\"background-image: url(TESTURI);\">test</b></body>")) return false;

  if (!Test("<b onclick='a = 1'>test</b>",
            "<body><b onclick=\"a = 1\">test</b></body>"))
    return false;

  return true;
}

bool TestFBMLParser::TestAttributeRewriting() {
  char *rewrite_attrs[] = { "rewrite", NULL};
  fbml_flaggable_attrs rewrite_attr_flags;
  rewrite_attr_flags.flag = FB_FLAG_ATTR_REWRITE;
  rewrite_attr_flags.attrs = rewrite_attrs;
  fbml_flaggable_attrs * flagged_attrs [] = {&rewrite_attr_flags, NULL};
  char *attrs[] = { "rewrite",  NULL };
  char *tags[] = { NULL };
  fbml_expand_tag_list(tags, attrs, NULL, flagged_attrs, NULL);

  if (!Test("<b rewrite='color'>test</b>",
            "<body><b rewrite=\"color\">test</b></body>")) return false;

  if (!Test("<b rewrite='color'>test</b>",
            "<body><b rewrite=\"tagbattrrewritevalcolor\">test</b></body>",
            "", true, false, false, NULL, NULL, false, false, NULL, true)) return false;

  return true;
}


bool TestFBMLParser::TestLineNumber() {
  SetStyleFlags();
  if (!Test("\n\n<style>.test { colorr: red;}</style>\n"
            "<script>test = 'test</script>",
            "<body>\n\n<style>#app123 .test {  }\n</style>\n<script><script></script></script></body>",
            "CSS Error (line 3 char 16): PEUnknownProperty  PEDeclDropped\nJS Error (line 4 char 7): SyntaxError: unterminated string literal", true, false, false, "#app123", "APPJS_")) return false;

  if (!Test("\n\n<input style='colorr: red;' />\n",
            "<body>\n\n<input style=\"\"></input>\n</body>",
            "CSS Error (line 3 char 8): PEUnknownProperty  PEDeclDropped\n")) {
    return false;
  }

  return true;
}

bool TestFBMLParser::TestExpandTwice() {
  // important to have two tags here, so that "fb:tag1" will have an enum
  // that's not present when fb:tag2 is defined
  char *tags1[] = { "fb:b", "fb:tag1", NULL };
  char *attrs1[] = { "title",  NULL };

  fbml_expand_tag_list(tags1, attrs1, NULL, NULL, NULL);

  char *tags2[] = { "fb:tag2", NULL };
  char *attrs2[] = { "title",  NULL };
  fbml_expand_tag_list(tags2, attrs2, NULL, NULL, NULL);

  if (!Test("<fb:tag1>test</fb:tag1>",
            "<body>test</body>",
            "FBML Error (line 1): unknown tag \"fb:tag1\"\n")) return false;

  return true;
}

bool TestFBMLParser::TestTagFlag() {
  char *special_tags[] = { "b", NULL};
  fbml_flaggable_tags special_tag_flags;
  special_tag_flags.flag = 2;
  special_tag_flags.tags = special_tags;
  char *precache_tags[] = { "b", "fb:b", NULL};
  fbml_flaggable_tags precache_tag_flags;
  precache_tag_flags.flag = 4;
  precache_tag_flags.tags = precache_tags;

  char *expanded_tags[] = { "fb:b", NULL };
  char *attrs[] = { "style", NULL };
  fbml_flaggable_tags *flaggable_tags[] = {
    &special_tag_flags, &precache_tag_flags, NULL
  };
  fbml_expand_tag_list(expanded_tags, attrs, flaggable_tags, NULL, NULL);

  char *extended_tags[] = { "p", NULL };
  fbml_flaggable_tags extended_tag_flags;
  extended_tag_flags.flag = 8;
  extended_tag_flags.tags = extended_tags;
  fbml_flaggable_tags *new_flaggable_tags[] = {
    &extended_tag_flags, NULL
  };

  fbml_flaggable_tags **flags[] = { NULL, flaggable_tags, new_flaggable_tags };

  for (int i = 0; i < 3; i++) {
    fbml_node *tree = NULL;
    if (!ParseTree("<b>b</b><fb:b>b</fb:b><a>a</a>"
                   "<p><a>a</a></p><p><b>b</b></p>"
                   "<b><b>b</b></b>",
                   &tree, flags[i])) {
      return false;
    }

    fbml_node *root = tree->children[0];
    if (root->children[0]->flag != 6) {
      printf("b tag is not 6, it is %d\n", root->children[0]->flag);
      return false;
    }
    if (root->children[1]->flag != 4) {
      printf("fb:b tag is not 4\n");
      return false;
    }
    if (root->children[2]->flag != 0) {
      printf("a tag is not 0\n");
      return false;
    }
    if (i < 2) {
      if (root->children[3]->children_flagged != 0) {
        printf("p tag without no flagged children is not 0\n");
        return false;
      }
      if (root->children[4]->children_flagged != 6) {
        printf("p tag with flagged children is not 1\n");
        return false;
      }
    } else {
      if (root->children[3]->flag != 8) {
        printf("extended p tag without no flagged children is not 8\n");
        return false;
      }
      if (root->children[4]->flag != 8 && root->children[4]->children_flagged != 6) {
        printf("extended tag with flagged children is not 9\n");
        return false;
      }
    }
    if (root->children[5]->flag != 6 && root->children[5]->children_flagged != 6) {
      printf("b tag with flagged children is not 7\n");
      return false;
    }

    fbml_node_free(tree);
  }

  return true;
}

bool TestFBMLParser::TestContextSchema() {
  char *A[] = { "img", "div", NULL };
  char *Aa[] = { "attrimg", "attrdiv", NULL };
  fbml_schema schema_a;
  schema_a.ancestor_tag = "fb:a";
  schema_a.illegal_children = A;
  schema_a.illegal_children_attr = Aa;

  char *B[] = { "fb:a","fb:c", NULL };
  char *Ba[] = { "attra", "attrc" , NULL };
  fbml_schema schema_b;
  schema_b.ancestor_tag = "fb:b";
  schema_b.illegal_children = B;
  schema_b.illegal_children_attr = Ba;

  char *C[] = { "fb:b", NULL };
  char *Ca[] = { "attrb", NULL };
  fbml_schema schema_c;
  schema_c.ancestor_tag = "fb:c";
  schema_c.illegal_children = C;
  schema_c.illegal_children_attr = Ca;

  fbml_context_schema context_schema_body;
  context_schema_body.context_tag = "body";
  fbml_schema *body_schemas[] = { &schema_a, &schema_b, &schema_c, NULL };
  context_schema_body.schema = body_schemas;

  fbml_context_schema context_schema_a;
  context_schema_a.context_tag = "div";
  fbml_schema *a_schemas[] = { &schema_b, &schema_c, NULL };
  context_schema_a.schema = a_schemas;

  fbml_context_schema *schemas[] =
    { &context_schema_body, &context_schema_a, NULL};

  char *tags[] = { "fb:a", "fb:b", NULL };
  char *attrs[] = { "style", "attrdiv", "attrimg", "attra", "attrb",  NULL };
  fbml_expand_tag_list(tags, attrs, NULL, NULL, schemas);

  if (!Test("<fb:a><img a=1>test</img></fb:a>",
            "<body><fb:a>test</fb:a></body>",
            "FBML Error (line 1): illegal tag \"img\" under \"fb:a\"\n"))
    return false;

  if (!Test("<fb:a><fb:b attrdiv=1>test</fb:b></fb:a>",
            "<body><fb:a><fb:b>test</fb:b></fb:a></body>",
            "FBML Error (line 1): illegal attr \"attrdiv\" in tag \"fb:b\" under \"body\"\n"))
    return false;


  if (!Test("<fb:a><b><img a=1>test</img></b></fb:a>",
            "<body><fb:a><b>test</b></fb:a></body>",
            "FBML Error (line 1): illegal tag \"img\" under \"fb:a\"\n"))
    return false;

  if (!Test("<fb:a><b><fb:b attrimg=1>test</fb:b></b></fb:a>",
            "<body><fb:a><b><fb:b>test</fb:b></b></fb:a></body>",
            "FBML Error (line 1): illegal attr \"attrimg\" in tag \"fb:b\" under \"body\"\n"))
    return false;


  if (!Test("<fb:a><div a=1>test</div></fb:a>",
            "<body><fb:a>test</fb:a></body>",
            "FBML Error (line 1): illegal tag \"div\" under \"fb:a\"\n"))
    return false;


  if (!Test("<fb:a>hello</fb:a><div a=1>test</div>",
            "<body><fb:a>hello</fb:a><div a=\"1\">test</div></body>",
            ""))
    return false;

  if (!Test("<fb:a><div a=1>hello</div></fb:a>\n<div a=1>test</div>",
            "<body><fb:a>hello</fb:a>\n<div a=\"1\">test</div></body>",
            "FBML Error (line 1): illegal tag \"div\" under \"fb:a\"\n"))
    return false;


  if (!Test("<div><fb:a><div a=1>test</div></fb:a></div>",
            "<body><div><fb:a><div a=\"1\">test</div></fb:a></div></body>"))
    return false;

  if (!Test("<html><head></head><body><fb:a><div a=1>hello</div></fb:a>\n<div a=1>test</div></body></html>",
            "<head></head><body><fb:a>hello</fb:a>\n<div a=\"1\">test</div></body>",
            "FBML Error (line 1): illegal tag \"div\" under \"fb:a\"\n",
            false))
    return false;

  if (!Test("<html><head></head><body><div><fb:a><div a=1>test</div></fb:a></div></body></html>",
            "<head></head><body><div><fb:a><div a=\"1\">test</div></fb:a></div></body>", "",
            false))
    return false;

  // Test to see if schema is ignored in internal_mode
  if (!Test("<html><head></head><body><fb:a><div a=1>hello</div></fb:a>\n<div a=1>test</div></body></html>",
            "<head></head><body><fb:a><div a=\"1\">hello</div></fb:a>\n<div a=\"1\">test</div></body>",
            "",
            false, false, true))
    return false;
  return true;
}


bool TestFBMLParser::TestUnderScore() {
  char *tags[] = { "fb:a_b", "fb:if-can-see", "fb:d_e", "fb:d-e", NULL };
  char *attrs[] = { "style", NULL };

  fbml_expand_tag_list(tags, attrs, NULL, NULL, NULL);

  if (!Test("<fb:a_b/><fb:if-can-see/><fb:if_can_see/><fb:d_e/><fb:d-e/>",
            "<body><fb:a-b></fb:a-b><fb:if-can-see></fb:if-can-see><fb:if-can-see></fb:if-can-see><fb:d-e></fb:d-e><fb:d-e></fb:d-e></body>")) return false;

  return true;
}

bool TestFBMLParser::TestTagContainment() {
  char *tags1[] = { "fb:b", "fb:tag1", "fb:editor", NULL };
  char *attrs[] = { "style", NULL };
  fbml_expand_tag_list(tags1, attrs, NULL, NULL, NULL);

  if (!Test("<fb:editor><tr><th>test</th></tr></fb:editor>",
            "<body><fb:editor><tr><th>test</th></tr></fb:editor></body>"))
    return false;

  if (!Test("<table><tr><fb:b><td>test</td></fb:b></tr></table>",
            "<body><table><tr><fb:b><td>test</td></fb:b></tr>"
            "</table></body>"))
    return false;

  if (!Test("<table><fb:b><tr><td>test</td></tr></fb:b></table>",
            "<body><table><fb:b><tr><td>test</td></tr></fb:b></table></body>"))
    return false;

  if (!Test("<table><tr><b>bad</b><td>test</td></tr></table>",
            "<body><table><tr><b>bad</b><td>test</td></tr>"
            "</table></body>"))
    return false;

  if (!Test("<table><tr><fb:b>bad</fb:b><td>test</td></tr></table>",
            "<body><table><tr><fb:b>bad</fb:b><td>test</td></tr>"
            "</table></body>"))
    return false;

  return true;
}

bool TestFBMLParser::TestUTF8() {
  if (!Test("<b>\xE2\x80</b>",
            "<body><b>\xE2\x80</b></body>")) return false;
  if (!Test("<b>\x00</b>",
            "<body><b></b></body>")) return false;

  return true;
}


/*bool TestFBMLParser::TestSpecialAttributes() {
  char *special_attrs[] = { "clicktohide", "clicktoshow", NULL};
  fbml_flaggable_attrs special_attr_flags;
  special_attr_flags.flag = FB_FLAG_ATTR_SPECIAL;
  special_attr_flags.attrs = special_attrs;
  fbml_flaggable_attrs * flagged_attrs [] = {&special_attr_flags, NULL};
  char *attrs[] = { "clicktohide",  "style", NULL };
  char *tags[] = { NULL };
  fbml_expand_tag_list(tags, attrs, NULL, flagged_attrs, NULL);

  if (!Test("<div><a clicktohide=\"foo\" clicktoshow=\"bar\"></a></div>",
            "<body><div><a script=\"foobar\"></a></div></body>"))
    return false;
}
*/

bool TestFBMLParser::TestNotBodyOnly() {
  char *tags1[] = { "fb:b", "fb:tag1", "fb:editor", NULL };
  char *attrs[] = { NULL };
  fbml_expand_tag_list(tags1, attrs, NULL, NULL, NULL);

  if (!Test("<b>Hello</b>", "<body><b>Hello</b></body>", "",
            false, false))
    return false;

  if (!Test("<fb:b>Hello</fb:b>", "<body><fb:b>Hello</fb:b></body>", "",
            false, false))
    return false;


  return true;
}
