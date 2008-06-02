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

#include "fbmlContentSink.h"
#include "../base.h"
#include "../fbml.h"
#include "nsHTMLTokens.h"
#include "nsIDTD.h"
#include "nsIParser.h"
#include "nsComponentManager.h"
#include "nsHTMLAttrs.h"
using namespace std;

//#define VERBOSE_LOGGING

///////////////////////////////////////////////////////////////////////////////
// constructors/destructors
NS_IMPL_ADDREF(FBMLContentSink)
NS_IMPL_RELEASE(FBMLContentSink)
NS_INTERFACE_MAP_BEGIN(FBMLContentSink)
NS_INTERFACE_MAP_ENTRY(nsIHTMLContentSink)
NS_INTERFACE_MAP_ENTRY(nsIContentSink)
NS_INTERFACE_MAP_END

FBMLContentSink::FBMLContentSink(nsIParser *parser, int preserve_comment, int skip_schema_checking,
                                 fbml_css_sanitizer *css_sanitizer,
                                 fbml_js_sanitizer *js_sanitizer,
                                 fbml_attr_rewriter *attr_rewriter,
                                 std::vector<int> &tag_flags,
                                 std::vector<int> &attr_flags,
                                 vector<contexts > &context_rules,
                                 fbml_node **tree)
  : mParser(parser), mPreserveComment(preserve_comment), mSkipSchemaChecking(skip_schema_checking),
    mCSSSanitizer(css_sanitizer), mJSSanitizer(js_sanitizer), mRewriter(attr_rewriter),
    m_tag_flags(tag_flags), m_attr_flags(attr_flags),
    m_context_rules(context_rules),
    mTree(tree), mForm(0) {
  ASSERT(tree);
  mCurrent = *tree = new fbml_node();
  memset(mCurrent, 0, sizeof(fbml_node));
}

FBMLContentSink::~FBMLContentSink() {
}

///////////////////////////////////////////////////////////////////////////////
// parse tree construction

static string GetTagName(nsHTMLTag eHTMLTag) {
  nsString stag(nsHTMLTags::GetStringValue(eHTMLTag));
  string tag;
  UTF16ToStdString(tag, stag);
  return tag;
}

char FBMLContentSink::GetFlag(PRInt32 eHTMLTag) {
  if (eHTMLTag >= 0 && eHTMLTag < m_tag_flags.size()) {
    return m_tag_flags[eHTMLTag];
  }
  return 0;
}

char FBMLContentSink::GetAttrFlag(PRInt32 eHTMLAttr) {
  if (eHTMLAttr >= 0 && eHTMLAttr < m_attr_flags.size()) {
    return m_attr_flags[eHTMLAttr];
  }
  return 0;
}

nsresult FBMLContentSink::OpenContainer(const nsIParserNode& aNode) {
  PRInt32 tagId = aNode.GetNodeType();
  bool attrSpecial = false;
  contexts *back_contexts;
  PRInt32 attrId;
#ifdef VERBOSE_LOGGING
  printf("OpenContainer: %d [%s]\n", tagId,
         GetTagName((nsHTMLTag)tagId).c_str());
#endif
  if (mRuleStack.empty()) {
    back_contexts = NULL;
  } else {
    back_contexts = mRuleStack.back();
  }
  if (tagId == eHTMLTag_userdefined) {
    string text;
    nsString stext(aNode.GetText());
    UTF16ToStdString(text, stext);

    PRInt32 lineNo = aNode.GetSourceLineNumber();
    char errorMsg[512];
    snprintf(errorMsg, sizeof(errorMsg),
             "FBML Error (line %d): unknown tag \"%s\"\n",
             (int)lineNo, text.c_str());
    mError += errorMsg;
  } else if (!mSkipSchemaChecking && mCurrent && back_contexts) {
    // check illegal children

    vector<vector<char> > &rules = back_contexts->tag_rules;
    if (tagId >= 0 && tagId < rules.size()) {
      for (fbml_node *p = mCurrent; p; p = p->parent) {
        unsigned short parentTagId = p->eHTMLTag;
        if (parentTagId >= rules.size() || rules[tagId][parentTagId]) {
          PRInt32 lineNo = aNode.GetSourceLineNumber();
          char errorMsg[512];
          snprintf(errorMsg, sizeof(errorMsg),
                   "FBML Error (line %d): illegal tag \"%s\" under \"%s\"\n",
                   (int)lineNo,
                   GetTagName((nsHTMLTag)tagId).c_str(),
                   GetTagName((nsHTMLTag)parentTagId).c_str());
          mError += errorMsg;
          tagId = eHTMLTag_userdefined; // nuke it
        }
      }
    }
  }

  PRInt32 lineNo = aNode.GetSourceLineNumber();
  fbml_node *node = fbml_node_add_child(mCurrent, tagId, GetFlag(tagId), lineNo);
  if (node) {
    int count = aNode.GetAttributeCount();
    for (int i = 0; i < count; i++) {
      string n; nsString sn(aNode.GetKeyAt(i));   UTF16ToStdString(n, sn);
      string v; nsString sv(aNode.GetValueAt(i)); UTF16ToUTF8String(v, sv);
      attrId = nsHTMLAttrs::LookupAttr(sn);
      char attrFlag = GetAttrFlag(attrId);
      // If we are in internal page skip all these checks
      if (mSkipSchemaChecking) {
        fbml_node_add_attribute(node, (char*)n.c_str(), (char*)v.c_str(), attrFlag, 1);
        continue;
      }


      bool fail = false;
      for (char *p = (char*)n.c_str(); *p; p++) {
        if (*p >= 'A' && *p <= 'Z') *p |= 0x20;
        // make sure attrs are well formed
        if (*p != ':' && *p !='_' && *p != '-' && (*p<'a' || *p>'z') && (*p<'0' || *p>'9') ) {
          fail = true;
          break;
        }

      }
      if (fail) {
        PRInt32 lineNo = aNode.GetSourceLineNumber();
        char errorMsg[512];
        snprintf(errorMsg, sizeof(errorMsg),
                 "FBML Error (line %d): illegal attr \"%s\" in tag \"%s\". Attribute names can only contain alphanumeric characters, underscores, and hyphens.",
                 (int)lineNo,
                 n.c_str(),
                 GetTagName((nsHTMLTag)tagId).c_str()
                 );
        mError += errorMsg;

      }
      // check schema
      else if (back_contexts && attrId >= 0 &&
          attrId < back_contexts->attr_rules.size() &&
          back_contexts->attr_rules[attrId]) {
        PRInt32 lineNo = aNode.GetSourceLineNumber();
        char errorMsg[512];
        snprintf(errorMsg, sizeof(errorMsg),
                 "FBML Error (line %d): illegal attr \"%s\" in tag \"%s\" under \"%s\"\n",
                 (int)lineNo,
                 n.c_str(),
                 GetTagName((nsHTMLTag)tagId).c_str(),
                 back_contexts->tag_name.c_str());
        mError += errorMsg;
      }  else if (mCSSSanitizer && (attrFlag & FB_FLAG_ATTR_STYLE)) {
        char *sanitized_css = NULL;
        char *error = NULL;
        int ret = fbml_sanitize_css((char*)v.c_str(), 1, lineNo,
                                    mCSSSanitizer, &sanitized_css, &error);
        if (sanitized_css) {
          fbml_node_add_attribute(node, (char*)n.c_str(), sanitized_css, attrFlag, 0);
        }
        if (error) {
          mError += error;
          free(error);
        }
      } else if (mJSSanitizer && (attrFlag & FB_FLAG_ATTR_SCRIPT)) {
        char *sanitized_js = NULL;
        char *error = NULL;
        int ret = fbml_sanitize_js((char*)v.c_str(), v.length(), 1, lineNo,
                                   mJSSanitizer, &sanitized_js, &error);
        if (sanitized_js) {

          if (mJSSanitizer->pfunc_eh_translator) {
            char *translated = mJSSanitizer->pfunc_eh_translator
              (sanitized_js, mJSSanitizer->eh_translate_data);
            free(sanitized_js);
            sanitized_js = translated;
          }


          fbml_node_add_attribute(node, (char*)n.c_str(), sanitized_js, attrFlag, 0);
        }
        if (error) {
          mError += error;
          free(error);
        }
      } else if (mRewriter && mRewriter->pfunc_rewriter && (attrFlag & FB_FLAG_ATTR_REWRITE)){
        char *rewritten_attr =
          mRewriter->pfunc_rewriter((char *)GetTagName((nsHTMLTag)tagId).c_str(),
                                    (char *)n.c_str(),
                                    (char *)v.c_str(), mRewriter->rewrite_data);
        fbml_node_add_attribute(node, (char*)n.c_str(), rewritten_attr, attrFlag, 0);

      } else {
        if (attrFlag & FB_FLAG_ATTR_SPECIAL) {
          attrSpecial = true;
        }
        fbml_node_add_attribute(node, (char*)n.c_str(), (char*)v.c_str(), attrFlag, 1);
      }
    }

    if (attrSpecial) {
      fbml_node_add_flags(node, FB_FLAG_HAS_SPECIAL_ATTR);
    }
    mCurrent = node;

    if (!mSkipSchemaChecking && tagId >= 0 && tagId < m_context_rules.size()) {
      contexts *rule = &m_context_rules[tagId];
      if (rule &&
          (!rule->tag_rules.empty() || !rule->attr_rules.empty())) {
        mRuleStack.push_back(rule);
      } else if (mRuleStack.empty()) {
        mRuleStack.push_back(NULL);
      } else {
        mRuleStack.push_back(mRuleStack.back());
      }
    } else {
      mRuleStack.push_back(NULL);
    }
  }
  return NS_OK;
}

nsresult FBMLContentSink::CloseContainer(const nsHTMLTag aTag) {
#ifdef VERBOSE_LOGGING
  printf("CloseContainer: %d [%s]\n", aTag, GetTagName(aTag).c_str());
#endif
  ASSERT(aTag);

  if ((mCurrent->eHTMLTag == aTag || mCurrent->eHTMLTag == eHTMLTag_userdefined)
      && mCurrent->parent) {
    mCurrent = mCurrent->parent;
    if (!mSkipSchemaChecking) {
      mRuleStack.pop_back();
    }
  }
  return NS_OK;
}

nsresult FBMLContentSink::AddLeaf(const nsIParserNode& aNode) {
  PRInt32 tagId = aNode.GetNodeType();

#ifdef VERBOSE_LOGGING
  printf("AddLeaf: %d [%s] Attribute Count: [%d]\n", tagId,
         GetTagName((nsHTMLTag)tagId).c_str(), aNode.GetAttributeCount());
  string text;
  nsString stext(aNode.GetText());
  UTF16ToStdString(text, stext);
  printf("\tLeaf Text: [%s]\n", text.c_str());
#endif
  if (tagId == eHTMLTag_title || tagId == eHTMLTag_script ||
      (GetFlag(tagId) & FB_FLAG_STYLE)) {
    nsCOMPtr<nsIDTD> dtd;
    mParser->GetDTD(getter_AddRefs(dtd));
    nsAutoString script;
    PRInt32 lineNo = 0;
    dtd->CollectSkippedContent(tagId, script, lineNo);
    string text;
    UTF16ToStdString(text, script);
#ifdef VERBOSE_LOGGING
    printf("\tCollected Content:: [%s]\n", text.c_str());
#endif

    OpenContainer(aNode);
    fbml_node *node = fbml_node_add_child(mCurrent, tagId, GetFlag(tagId), lineNo);

    if (mCSSSanitizer && (GetFlag(tagId) & FB_FLAG_STYLE)) {
      char *sanitized_css = NULL;
      char *error = NULL;
      int ret = fbml_sanitize_css((char*)text.c_str(), 0, lineNo,
                                  mCSSSanitizer, &sanitized_css, &error);
      if (sanitized_css) {
        node->text = sanitized_css;
      }
      if (error) {
        mError += error;
        free(error);
      }
    } else if (mJSSanitizer && tagId == eHTMLTag_script) {
      char *sanitized_js = NULL;
      char *error = NULL;
      int ret = fbml_sanitize_js((char*)text.c_str(), text.length(), 0,
                                 lineNo, mJSSanitizer, &sanitized_js,
                                 &error);
      if (sanitized_js) {
        node->text = sanitized_js;
      }
      if (error) {
        mError += error;
        free(error);
      }
    } else {
      node->text = strdup(text.c_str());
    }

    CloseContainer((nsHTMLTag)tagId);
    return NS_OK;
  }


  switch (aNode.GetTokenType()) {
  case eToken_start:
    OpenContainer(aNode);
    CloseContainer((nsHTMLTag)tagId);
    break;
  case eToken_text:
  case eToken_whitespace:
  case eToken_newline:
    {
      PRInt32 lineNo = aNode.GetSourceLineNumber();
      fbml_node *node = fbml_node_add_child(mCurrent, tagId, GetFlag(tagId), lineNo);
      string text;
      nsString stext(aNode.GetText());
      UTF16ToStdString(text, stext);
      node->text = strdup(text.c_str());
    }
    break;
  }
  return NS_OK;
}

nsresult FBMLContentSink::AddComment(const nsIParserNode& aNode) {
  PRInt32 tagId = aNode.GetNodeType();

#ifdef VERBOSE_LOGGING
  printf("AddComment: %d\n", tagId);
  string text;
  nsString stext(aNode.GetText());
  UTF16ToStdString(text, stext);
  printf("\tComment Text: [%s]\n", text.c_str());
#endif

  if (mPreserveComment) {
    OpenContainer(aNode);
    PRInt32 lineNo = aNode.GetSourceLineNumber();
    fbml_node *node = fbml_node_add_child(mCurrent, tagId, GetFlag(tagId), lineNo);
    string text;
    nsString stext(aNode.GetText());
    UTF16ToStdString(text, stext);
    node->text = strdup(text.c_str());

    CloseContainer((nsHTMLTag)tagId);
  }
  return NS_OK;
}
