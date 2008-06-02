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


#ifndef __TEST_BASE_H__
#define __TEST_BASE_H__

#include <string>
#include <assert.h>
#include "fbml.h"

///////////////////////////////////////////////////////////////////////////////

class TestBase {
 public:
  TestBase() {}
  virtual ~TestBase() {}

  virtual bool RunTests(const std::string &which) = 0;

 protected:
  bool VerifySame(const char *s1, const char *s2);
  bool VerifySame(int n1, int n2);
  bool VerifySame(float n1, float n2);
};

///////////////////////////////////////////////////////////////////////////////
// macros

#define RUN_TEST(test)                                                  \
  if (!which.empty() && which != #test) {                               \
    if (0) printf(#test " skipped\n");                                  \
  } else if (test()) {                                                  \
    printf(#test " passed\n");                                          \
  } else {                                                              \
    printf(#test " failed\n");                                          \
    ret = false;                                                        \
  }                                                                     \

///////////////////////////////////////////////////////////////////////////////

#endif // __TEST_BASE_H__
