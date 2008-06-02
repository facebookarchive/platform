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

#ifndef __FBML_CONTENT_SINK_H__
#define __FBML_CONTENT_SINK_H__

#include "nsIHTMLContentSink.h"
#include "fbml.h"
#include <string>
#include <vector>
#include <list>

///////////////////////////////////////////////////////////////////////////////

/**
 * This ContentSink will be called from CNavDTD. What is a content sink? Read
 * http://www.mozilla.org/newlayout/doc/parser.html.
 */
class FBMLContentSink : public nsIHTMLContentSink {
 public:
  FBMLContentSink(nsIParser *parser, int preserve_comment, int skip_schema_checking,
                  fbml_css_sanitizer *css_sanitizer,
                  fbml_js_sanitizer *js_sanitizer,
                  fbml_attr_rewriter *attr_rewrite,
                  std::vector<int> &tag_flags,
                  std::vector<int> &attr_flags,
                  std::vector<contexts > &context_rules,
                  fbml_node **tree);
  virtual ~FBMLContentSink();

  /**
   * Get errors while parsing FBML/CSS.
   */
  const char *GetError() const { return mError.c_str();}

  /**
   * Overriding these callbacks to prepare parse tree.
   */
  NS_IMETHOD OpenContainer(const nsIParserNode& aNode);
  NS_IMETHOD CloseContainer(const nsHTMLTag aTag);
  NS_IMETHOD AddLeaf(const nsIParserNode& aNode);
  NS_IMETHOD AddComment(const nsIParserNode& aNode);

  // ignoring all these callbacks
  NS_IMETHOD OpenHTML(const nsIParserNode& aNode)       { return NS_OK;}
  NS_IMETHOD CloseHTML()                                { return NS_OK;}
  NS_IMETHOD BeginContext(PRInt32 aPosition)            { return NS_OK;}
  NS_IMETHOD EndContext(PRInt32 aPosition)              { return NS_OK;}
  NS_IMETHOD SetTitle(const nsString& aValue)           { return NS_OK;}
  NS_IMETHOD WillProcessTokens(void)                    { return NS_OK;}
  NS_IMETHOD DidProcessTokens()                         { return NS_OK;}
  NS_IMETHOD WillProcessAToken(void)                    { return NS_OK;}
  NS_IMETHOD DidProcessAToken(void)                     { return NS_OK;}
  NS_IMETHOD AddDocTypeDecl(const nsIParserNode& aNode) { return NS_OK;}
  NS_IMETHOD NotifyTagObservers(nsIParserNode* aNode)   { return NS_OK;}
  NS_IMETHOD AddProcessingInstruction(const nsIParserNode& aNode) {
    return NS_OK;
  }
  NS_IMETHOD IsEnabled(PRInt32 aTag, PRBool* aReturn) {
    *aReturn = PR_TRUE;
    return NS_OK;
  }

  // collapsing these
  NS_IMETHOD OpenHead(const nsIParserNode& aNode) {
    return OpenContainer(aNode);
  }
  NS_IMETHOD AddHeadContent(const nsIParserNode& aNode) {
    return AddLeaf(aNode);
  }
  NS_IMETHOD CloseHead() {
    return CloseContainer(eHTMLTag_head);
  }
  NS_IMETHOD OpenBody(const nsIParserNode& aNode) {
    return OpenContainer(aNode);
  }
  NS_IMETHOD CloseBody() {
    return CloseContainer(eHTMLTag_body);
  }
  NS_IMETHOD OpenForm(const nsIParserNode& aNode){
    mForm++;
    return OpenContainer(aNode);
  }
  NS_IMETHOD CloseForm() {
    mForm--;
    return CloseContainer(eHTMLTag_form);
  }
  NS_IMETHOD_(PRBool) IsFormOnStack() {
    return mForm ? PR_TRUE : PR_FALSE;
  }
  NS_IMETHOD OpenMap(const nsIParserNode& aNode) {
    return OpenContainer(aNode);
  }
  NS_IMETHOD CloseMap()  {
    return CloseContainer(eHTMLTag_map);
  }
  NS_IMETHOD OpenFrameset(const nsIParserNode& aNode) {
    return OpenContainer(aNode);
  }
  NS_IMETHOD CloseFrameset() {
    return CloseContainer(eHTMLTag_frameset);
  }

  // nsISupports
  NS_DECL_ISUPPORTS

  // nsIContentSink
  NS_IMETHOD WillBuildModel(void) { return NS_OK;}
  NS_IMETHOD DidBuildModel(void) { return NS_OK;}
  NS_IMETHOD WillInterrupt(void) { return NS_OK;}
  NS_IMETHOD WillResume(void) { return NS_OK;}
  NS_IMETHOD SetParser(nsIParser* aParser) { return NS_OK;}
  virtual void FlushPendingNotifications(mozFlushType aType) {}
  NS_IMETHOD SetDocumentCharset(nsACString& aCharset) { return NS_OK;}
  virtual nsISupports *GetTarget() { return NULL;}

 private:
  nsIParser *mParser;
  bool mPreserveComment;
  fbml_css_sanitizer *mCSSSanitizer;
  fbml_js_sanitizer *mJSSanitizer;
  fbml_attr_rewriter *mRewriter;
  int mSkipSchemaChecking;
  std::vector<int> &m_tag_flags;
  std::vector<int> &m_attr_flags;
  std::vector<contexts > &m_context_rules;

  fbml_node **mTree;
  fbml_node *mCurrent;
  std::list<contexts *> mRuleStack;
  int mForm;
  std::string mError;

  char GetFlag(PRInt32 eHTMLTag);
  char GetAttrFlag(PRInt32 eHTMLAttr);
};

///////////////////////////////////////////////////////////////////////////////

#endif // __FBML_CONTENT_SINK_H__
