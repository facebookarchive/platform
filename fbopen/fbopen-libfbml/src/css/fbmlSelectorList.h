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

#ifndef __FBML_SELECTOR_LIST_H__
#define __FBML_SELECTOR_LIST_H__

#include "nsICSSStyleRule.h"

///////////////////////////////////////////////////////////////////////////////

class FBMLSelectorList : public nsCSSSelectorList {
 public:
  FBMLSelectorList(const nsString& aContainerSelector,
                   const nsString& aIdentifierPrefix);

  void ToString(nsAString& aResult, nsICSSStyleSheet* aSheet);

 private:
  nsString mContainerSelector;
  nsString mIdentifierPrefix;
};

///////////////////////////////////////////////////////////////////////////////

#endif // __FBML_SELECTOR_LIST_H__
