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

#include "fbmlStyleSheet.h"
#include "../base.h"
#include "nsCSSDeclaration.h"

using namespace std;

///////////////////////////////////////////////////////////////////////////////
// constructor/destructor

FBMLStyleSheet::FBMLStyleSheet(fbml_css_sanitizer *css_sanitizer,
                               char **css, char **error)
  : m_css(css), m_error(error) {
  if (css_sanitizer) {
    if (css_sanitizer->container_selector) {
      mContainerSelector.AssignASCII(css_sanitizer->container_selector);

      string selector = css_sanitizer->container_selector;
      const char *selector_prefix = "#app_content_";
      int prefix_len = strlen(selector_prefix);
      if (selector.length() > prefix_len &&
          selector.substr(0, prefix_len) == selector_prefix) {
        selector = string("app") + selector.substr(prefix_len);
      }
      mIdentifierPrefix.AssignASCII(selector.c_str());
    }
    if (css_sanitizer->identifier_prefix) {
      mIdentifierPrefix.AssignASCII(css_sanitizer->identifier_prefix);
    }
    m_pfunc_url_translator = css_sanitizer->pfunc_url_translator;
    m_url_translate_data = css_sanitizer->url_translate_data;
  }
}

FBMLStyleSheet::~FBMLStyleSheet() {
}

///////////////////////////////////////////////////////////////////////////////

void FBMLStyleSheet::AppendStyleRule(nsICSSStyleRule *aRule,
                                     bool decl_only /* = false */) {
  nsString css;
  if (decl_only) {
    aRule->GetDeclaration()->ToString(css);
  } else {
    aRule->GetCssText(css);
  }

  if (m_css) {
    mCssText.Append(css);
    if (!decl_only) {
      mCssText.AppendLiteral("\n");
    }
  }
}

void FBMLStyleSheet::TranslateURL(nsAutoString &url) {
  if (m_pfunc_url_translator) {
    string surl;
    UTF16ToStdString(surl, url);
    char *translated = m_pfunc_url_translator((char*)surl.c_str(),
                                              m_url_translate_data);
    url.AssignASCII(translated);
    free(translated);
  }
}

void FBMLStyleSheet::OutputError(nsCSSScanner &scanner) {
  const char *error = scanner.GetError();
  if (m_error) {
    mCssError += error;
  }
  scanner.ClearError();
}

void FBMLStyleSheet::OnParseComplete() {
  if (m_css) {
    string out;
    UTF16ToStdString(out, mCssText);
    *m_css = strdup(out.c_str());
  }
  if (m_error) {
    *m_error = strdup(mCssError.c_str());
  }
}
