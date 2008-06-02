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

#ifndef __FBML_STYLE_SHEET_H__
#define __FBML_STYLE_SHEET_H__

#include "nsICSSStyleRule.h"
#include "nsCSSScanner.h"
#include <string>
#include "fbml.h"

///////////////////////////////////////////////////////////////////////////////

/**
 * Data structure passing in and out parameters to nsCSSParser::Parse().
 */
class FBMLStyleSheet {
 public:
  /**
   * Constructor.
   *
   * container_selector: selector prefix for sanitization
   * pfunc_url_translator: URL translator
   * css: store sanitized css
   * error: store parser errors
   */
  FBMLStyleSheet(fbml_css_sanitizer *css_sanitizer, char **css, char **error);
  ~FBMLStyleSheet();

  const nsString &GetContainerSelector() const { return mContainerSelector;}
  const nsString &GetIdentifierPrefix() const { return mIdentifierPrefix;}

  /**
   * Called when a style rule is newly parsed.
   */
  void AppendStyleRule(nsICSSStyleRule *aRule, bool decl_only = false);

  /**
   * Translate an URL to a safe one.
   */
  void TranslateURL(nsAutoString &url);

  /**
   * Called when a syntax error occurred.
   */
  void OutputError(nsCSSScanner &scanner);

  /**
   * Call this function when parsing is done, so final result can be copied
   * back to char **css and char **error.
   */
  void OnParseComplete();

 private:
  char **m_css;
  char **m_error;

  nsString mContainerSelector;  // what to prefix to selectors
  nsString mIdentifierPrefix;   // what to prefix to identifiers
  char *(*m_pfunc_url_translator)(char *, void *); // URL translator
  void *m_url_translate_data;   // what to pass to the translator function
  nsString mCssText;            // final result
  std::string mCssError;        // parser errors
};

///////////////////////////////////////////////////////////////////////////////

#endif // __FBML_STYLE_SHEET_H__
