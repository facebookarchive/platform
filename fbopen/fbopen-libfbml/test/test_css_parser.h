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


#ifndef __TEST_CSS_PARSER_H__
#define __TEST_CSS_PARSER_H__

#include "test_base.h"

///////////////////////////////////////////////////////////////////////////////

class TestCSSParser : public TestBase {
 public:
  TestCSSParser();

  virtual bool RunTests(const std::string &which);

  bool TestContainerSelector();
  bool TestComment();
  bool TestUnknownProperty();
  bool TestUrlTranslator();
  bool TestDeclarationParsing();
  bool TestMozTagStripping();
  bool TestIETags();
  bool TestAtRuleStripping();
  bool TestNoSanitization();
  bool TestLineNumber();
  bool TestIdPrefix();
  bool TestBackgroundPosition();
  bool TestSelectorPatterns();

  char *TranslateUrl(char *url);

 private:
  bool Test(const char *input, const char *expected,
            const char *container_selector = NULL,
            const char *expected_error = "",
            bool decl_only = false,
            int line_no = 0,
            bool translate_url = false,
            const char *identifier_prefi = NULL);
};

///////////////////////////////////////////////////////////////////////////////

#endif // __TEST_CSS_PARSER_H__
