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


#include "test_fbml_node.h"

using namespace std;

///////////////////////////////////////////////////////////////////////////////

TestFBMLNode::TestFBMLNode() {
}

bool TestFBMLNode::RunTests(const std::string &which) {
  bool ret = true;

  RUN_TEST(TestNodeGetAttribute);
  RUN_TEST(TestNodeGetBoolAttribute);
  RUN_TEST(TestNodeGetColorAttribute);
  RUN_TEST(TestPrintNode);

  RUN_TEST(TestNoEscaping);

  RUN_TEST(TestRenderHtml);
  RUN_TEST(TestPrecache);
  RUN_TEST(TestBatchPrecache);

  return ret;
}

fbml_node *TestFBMLNode::ParseTree(const char *input) {
  char *error = NULL;
  fbml_node *tree = NULL;
  int ret = fbml_parse((char*)input, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, &tree, &error);
  assert(ret == 0);
  if (error && *error) {
    printf("Parser Error: %s\n", error);
    assert(!error || !*error);
  }
  if (error) free(error);
  return tree;
}

///////////////////////////////////////////////////////////////////////////////

#define BEGIN_TEST(s) \
  {                                                            \
    fbml_node *tree = ParseTree(s);                            \
    fbml_node *node = tree->children[0]->children[0];          \

#define END_TEST                                  \
    fbml_node_free(tree);                         \
  }                                               \

bool TestFBMLNode::TestNodeGetAttribute() {
  BEGIN_TEST("<b small=value Mixed=34 />");

  if (!VerifySame(fbml_node_get_attribute(node, "small"), "value"))
    return false;

  if (!VerifySame(fbml_node_get_attribute(node, "mIXEd"), "34"))
    return false;

  if (!VerifySame(fbml_node_get_attribute(node, "none"), NULL))
    return false;

  END_TEST;

  return true;
}

bool TestFBMLNode::TestNodeGetBoolAttribute() {
  BEGIN_TEST("<b a=1 b=0 c=yES d=NO e=TRUe f=False g=23 h=-1 i=1s />");
  node = NULL;
  int result = 0;
  if (!VerifySame(fbml_node_attr_to_bool( "1",  &result), 1) ||
      !VerifySame(result, 1)) {
    return false;
  }

  if (!VerifySame(fbml_node_attr_to_bool( "0",  &result), 1) ||
      !VerifySame(result, 0)) {
    return false;
  }
  if (!VerifySame(fbml_node_attr_to_bool( "yES",  &result), 1) ||
      !VerifySame(result, 1)) {
    return false;
  }
  if (!VerifySame(fbml_node_attr_to_bool( "NO",  &result), 1) ||
      !VerifySame(result, 0)) {
    return false;
  }
  if (!VerifySame(fbml_node_attr_to_bool( "TRUe",  &result), 1) ||
      !VerifySame(result, 1)) {
    return false;
  }
  if (!VerifySame(fbml_node_attr_to_bool( "False",  &result), 1) ||
      !VerifySame(result, 0)) {
    return false;
  }
  if (!VerifySame(fbml_node_attr_to_bool( "23",  &result), 1) ||
      !VerifySame(result, 1)) {
    return false;
  }
  if (!VerifySame(fbml_node_attr_to_bool( "-1",  &result), 1) ||
      !VerifySame(result, 1)) {
    return false;
  }

  if (!VerifySame(fbml_node_attr_to_bool( "1s",  &result), -1)) {
    return false;
  }

  END_TEST;
  return true;
}

bool TestFBMLNode::TestNodeGetColorAttribute() {
  BEGIN_TEST("<b a=#FF0000 b='rgb(255, 0,0)' c=red d=Red e=reed f=#F g=25 />");
  node = NULL;
  char *result = NULL;

  if (!VerifySame(fbml_node_attr_to_color( "#FF0000", &result), 1) ||
      !VerifySame(result, "#ff0000")) {
    return false;
  }
  free(result);

  if (!VerifySame(fbml_node_attr_to_color( "rgb(255, 0,0)",  &result), 1) ||
      !VerifySame(result, "#ff0000")) {
    return false;
  }
  free(result);

  if (!VerifySame(fbml_node_attr_to_color( "red",  &result), 1) ||
      !VerifySame(result, "red")) {
    return false;
  }
  free(result);

  if (!VerifySame(fbml_node_attr_to_color( "Red",  &result), 1) ||
      !VerifySame(result, "Red")) {
    return false;
  }
  free(result);

  if (!VerifySame(fbml_node_attr_to_color( "reed",  &result), -1)) {
    return false;
  }

  if (!VerifySame(fbml_node_attr_to_color( "25",  &result), -1)) {
    return false;
  }

  END_TEST;
  return true;
}

bool TestFBMLNode::TestPrintNode() {
  BEGIN_TEST("<a a='&quot;&lt;'><b>&quot;&lt;</b></a>");

  char *output = fbml_node_print(node);
  if (!VerifySame(output, "<a a=\"&quot;&lt;\"><b>&quot;&lt;</b></a>")) {
    return false;
  }

  free(output);
  END_TEST;
  return true;
}



bool TestFBMLNode::TestNoEscaping() {
  BEGIN_TEST("<a onclick=\"Hello'goodbye'\"><script>asdfasdf\"\"</script><!--adf\"asd\"asdf--></a>");

  char *output = fbml_node_print(node);
  if (!VerifySame(output, "<a onclick=\"Hello'goodbye'\"><script>asdfasdf\"\"</script><!--adf\"asd\"asdf--></a>")) {
    return false;
  }

  free(output);
  END_TEST;
  return true;
}




static char *callback(fbml_node *node, void *data){
  int param = *(int *)data;
  if (param ==1) {
    return strdup("fbml_node");

  } else {
    return strdup("html_node");
  }
}

bool TestFBMLNode::TestRenderHtml() {

  fbml_flaggable_tags html_tag;
  html_tag.flag = FB_FLAG_SPECIAL_HTML;
  char *html_tags[] = {"a", NULL};
  html_tag.tags = html_tags;

  fbml_flaggable_tags fb_tag;
  fb_tag.flag = FB_FLAG_FBNODE;
  char *fb_tags[] = {"fb:a", "fb:b", NULL};
  fb_tag.tags = fb_tags;

  fbml_flaggable_tags *flags[] = {&html_tag, &fb_tag, NULL};

  fbml_flaggable_attrs special_attr;
  special_attr.flag = FB_FLAG_ATTR_SPECIAL;
  char *special_attrs[] = {"clicktohide", NULL};
  special_attr.attrs = special_attrs;

  fbml_flaggable_attrs *attr_flags[] = {&special_attr, NULL};

  char *tags[] = {"fb:a", "fb:b", NULL};
  char *attrs[] = {"style", "clicktohide", NULL};

  fbml_expand_tag_list(tags, attrs, flags, attr_flags, NULL);

  char *error = NULL;
  fbml_node *node = NULL;

  fbml_parse("<div><a></a></div><p><fb:a></fb:a></p><span><div clicktohide=\"test\"></div></span><div><!-- Hello --></div>",
             true, true, false, NULL, NULL, NULL, NULL, NULL, &node, &error);


  int fb = 1, html = 2;
  fbml_node_renderer rend;
  rend.fb_node_data   = &fb;
  rend.html_node_data = &html;
  rend.pfunc_renderer = callback;

  char *output = fbml_node_render_children(node, false,  &rend);
  if (!VerifySame(output, "<body><div>html_node</div><p>fbml_node</p><span>html_node</span><div><!-- Hello --></div></body>")) {
    return false;
  }
  if (error) {
    free(error);
  }
  fbml_node_free(node);
  free(output);

  //internal mode check
  fbml_parse("<div><a></a></div><p><fb:a></fb:a></p><span><div clicktohide=\"test\"></div></span><div><!-- Hello --></div>",
             true, true, true, NULL, NULL, NULL, NULL, NULL, &node, &error);



  output = fbml_node_render_children(node, true, &rend);
  if (!VerifySame(output, "<body><div><a></a></div><p>fbml_node</p><span><div clicktohide=\"test\"></div></span><div><!-- Hello --></div></body>")) {

    return false;
  }

  if (error) {
    free(error);
  }
  fbml_node_free(node);
  free(output);


  return true;
}

string collect;

static char *callback_precache(fbml_node *node, void *data){
  char *test;
  test = fbml_node_get_attribute(node, "test");

  collect += test;
  return strdup("");

}

bool TestFBMLNode::TestPrecache() {
  fbml_flaggable_tags precache_tag;
  precache_tag.flag = FB_FLAG_PRECACHE;
  char *precache_tags[] = {"fb:a", NULL};
  precache_tag.tags = precache_tags;

  fbml_flaggable_tags *flags[] = {&precache_tag, NULL};

  char *tags[] = {"fb:a", "fb:b", NULL};
  char *attrs[] = { NULL};

  fbml_expand_tag_list(tags, attrs, flags, NULL, NULL);

  char *error = NULL;
  fbml_node *node = NULL;

  fbml_parse("<fb:a test=\"one\"><a/><fb:b/></fb:a><fb:b><fb:a test=\"two\"></fb:a>",
             true, false, false, NULL, NULL, NULL, NULL, NULL, &node, &error);


  fbml_node_precacher pre;
  pre.precache_node_data = NULL;
  pre.pfunc_precacher = callback_precache;

  collect = "";
  collect.reserve(1024);
  fbml_node_precache(node, &pre);

  if (!VerifySame(collect.c_str(), "onetwo")) {
    return false;
  }
  if (error) {
    free(error);
  }
  fbml_node_free(node);
  return true;
}


bool TestFBMLNode::TestBatchPrecache() {
  fbml_precache_bunch ** batches;
  fbml_precache_bunch ** iter;

  fbml_flaggable_tags precache_tag;
  precache_tag.flag = FB_FLAG_PRECACHE;
  char *precache_tags[] = {"fb:a", "fb:c", NULL};

  precache_tag.tags = precache_tags;

  fbml_flaggable_tags *flags[] = {&precache_tag, NULL};

  char *tags[] = {"fb:a", "fb:b", "fb:c", NULL};
  char *attrs[] = { NULL};

  fbml_expand_tag_list(tags, attrs, flags, NULL, NULL);

  char *error = NULL;
  fbml_node *node = NULL;

  fbml_parse("<fb:a test=\"one\"><a/><fb:b/></fb:a><fb:c></fb:c><fb:a test=\"two\"></fb:a>",
             true, false, false, NULL, NULL, NULL, NULL, NULL, &node, &error);



  batches = fbml_node_batch_precache(node);

  if (!VerifySame(batches[0]->tag, "fb:a")) {
    return false;
  }

  if (!VerifySame(batches[1]->tag, "fb:c")) {
    return false;
  }

  for( iter = batches; *iter; iter++) {
    fbml_node_bunch_free(*iter);
  }
  free(batches);
  if (error) {
    free(error);
  }
  fbml_node_free(node);
  return true;
}
