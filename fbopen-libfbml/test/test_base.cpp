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

#include "test_base.h"

///////////////////////////////////////////////////////////////////////////////

bool TestBase::VerifySame(const char *s1, const char *s2) {
  if (s1 == NULL && s2 == NULL) return true;

  if (s1 == NULL || s2 == NULL) {
    printf(s1 == NULL ? "first string is null\n" : "second string is null\n");
    return false;
  }

  if (strcmp(s1, s2)) {
    printf("[%s] != [%s]\n", s1, s2);
    return false;
  }

  return true;
}

bool TestBase::VerifySame(int n1, int n2) {
  if (n1 != n2) {
    printf("%d != %d\n", n1, n2);
    return false;
  }
  return true;
}

bool TestBase::VerifySame(float n1, float n2) {
  if (n1 != n2) {
    printf("%g != %g\n", n1, n2);
    return false;
  }
  return true;
}
