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


#ifndef __TEST_FBML_PARSER_H__
#define __TEST_FBML_PARSER_H__

#include "test_base.h"

///////////////////////////////////////////////////////////////////////////////

class TestFBMLParser : public TestBase {
 public:
  TestFBMLParser();

  virtual bool RunTests(const std::string &which);

  bool TestSanity();
  bool TestWhiteSpace();
  bool TestUnknownElement();
  bool TestUnclosedTags();
  bool TestMisplacedContents();
  bool TestIllegalContents();
  bool TestEmptyTags();
  bool TestSelfClosedTags();
  bool TestTagExpanding();
  bool TestAttributes();
  bool TestHead();
  bool TestComment();
  bool TestEntity();
  bool TestStyleElementSanitization();
  bool TestStyleAttributeSanitization();
  bool TestScriptElementSanitization();
  bool TestScriptEventHandlerSanitization();
  bool TestNoSanitization();
  bool TestLineNumber();
  bool TestExpandTwice();
  bool TestTagFlag();
  bool TestContextSchema();
  bool TestUnderScore();
  bool TestTagContainment();
  bool TestUTF8();
  bool TestNotBodyOnly();
  bool TestAttributeRewriting();

  char *TranslateUrl(char *url);
  char *TranslateEventHandler(char *eh);
  char *RewriteAttr(char *tag, char *name, char *attr);
 private:
  bool Test(const char *input,
            const char *expected,
            const char *expected_error = "",
            bool body_only = true,
            bool preserve_comment = false,
            bool internal_mode  = false ,
            const char *container_selector = NULL,
            const char *identifier_prefix = NULL,
            bool translate_url = false,
            bool translate_eh = false,
            const char *this_replacement = NULL,
            bool rewrite_attr = false
            );

  bool ParseTree(const char *input, fbml_node **tree,
                 fbml_flaggable_tags **flaggable_tags);
};

///////////////////////////////////////////////////////////////////////////////

#endif // __TEST_FBML_PARSER_H__
