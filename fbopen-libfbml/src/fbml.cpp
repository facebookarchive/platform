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

#include "fbml.h"
#include "nsICSSParser.h"
#include "nsICSSStyleSheet.h"
#include "nsIUnicharInputStream.h"
#include "nsComponentManager.h"
#include "fbmlStyleSheet.h"
#include "nsCSSProps.h"
#include "nsStringBundleService.h"
#include "nsContentUtils.h"
#include "nsParser.h"
#include "fbmlContentSink.h"
#include "base.h"
#include "nsHTMLEntities.h"
#include "nsHTMLAttrs.h"
#include "nsElementTable.h"

#include "nsColorNames.h"
#include "nsCSSPseudoClasses.h"
#include "nsCSSPseudoElements.h"
#include "nsCSSDeclaration.h"
#include "jscntxt.h" // required for FBJS v1
#include "jsscan.h"  // required for FBJS v1
#include "jsstr.h"   // required for FBJS v1

#include "fbjs.h"    // required for FBJS v1

#include <map>
#include <iostream>
using namespace std;

typedef map<unsigned short, list <fbml_node *> * > node_map;

/**
 * Utility function designed to construct an error string out of the
 * supplied 'msg' and 'rv', and place the new message at the address
 * supplied via 'error'.
 *
 * @param error the address where the new, dynamically allocated
 *              error message should be placed.
 */

static int compose_error(char **error, const char *msg, nsresult rv)
{
  char buf[1024];
  snprintf(buf, sizeof(buf), "%s: 0x%08X", msg, rv);
  *error = strdup(buf);
  return -1;
}

#define NS_CALL(call)                                 \
  rv = (call);                                        \
  if (NS_FAILED(rv)) {                                \
    return compose_error(error, #call " failed", rv); \
  }                                                   \

#define NS_VERIFY(call)                         \
  rv = (call);                                  \
  if (NS_FAILED(rv)) {                          \
    char *error = NULL;                         \
    compose_error(&error, #call " failed", rv); \
    free(error);                                \
    ASSERT(false);                              \
  }                                             \

///////////////////////////////////////////////////////////////////////////////
// XPCOM and LIBFBML Initialization

nsISupports *nsContentUtils::GetClassInfoInstance(nsDOMClassInfoID aID) {
  ASSERT(false);
  return nsnull;
}

class LibFbmlStaticInitializer {
public:
  LibFbmlStaticInitializer() {
    nsCSSProps::AddRefTable();
    nsCSSKeywords::AddRefTable();
    nsColorNames::AddRefTable();
    nsCSSPseudoClasses::AddRefAtoms();
    nsCSSPseudoElements::AddRefAtoms();

    nsresult rv;
    NS_VERIFY(nsHTMLAttrs::AddRefTable());
    NS_VERIFY(nsHTMLTags::AddRefTable());
    NS_VERIFY(nsHTMLEntities::AddRefTable());
#ifdef NS_DEBUG
    CheckElementTable();
#endif
    CNewlineToken::AllocNewline();
  }

  ~LibFbmlStaticInitializer() {
    nsCSSProps::ReleaseTable();
    nsCSSKeywords::ReleaseTable();
    nsColorNames::ReleaseTable();

    nsHTMLTags::ReleaseTable();
    nsHTMLEntities::ReleaseTable();
    nsDTDContext::ReleaseGlobalObjects();
    CNewlineToken::FreeNewline();
  }
};
static LibFbmlStaticInitializer s_init;

///////////////////////////////////////////////////////////////////////////////

int fbml_html_userdefined_tag = eHTMLTag_userdefined;
int fbml_html_style_tag = eHTMLTag_style;
int fbml_html_comment_tag = eHTMLTag_comment;

/**
 * Accepts the specified htmlTag, synthesizes
 * the corresponding tag text, and returns it.
 * The returned string is dynamically allocated, so it
 * needs to be freed.
 *
 * @param eHTMLTag two-byte integer code backing an
 *                 HTML tag.
 * @return text representation of the actual HTML tag backed by
 *         by the eHTMLTag.
 */

char *fbml_lookup_tag_name(unsigned short eHTMLTag)
{
  string tag;
  const PRUnichar *s = nsHTMLTags::GetStringValue((nsHTMLTag)eHTMLTag);
  if (s != nsnull) {
    nsString stag(s);
    UTF16ToStdString(tag, stag);
  }

  return strdup(tag.c_str());
}

/**
 * Returns the number backing the specified HTML tag.
 *
 * @param name the textual representation of an HTML tag.
 * @return the corresponding, two-byte integer code for the
 *         specified HTML tag.
 */

unsigned short fbml_lookup_tag_by_name(char *name)
{
  if (!name || !*name) return eHTMLTag_unknown /* 0 */;
  nsString aTagName;
  aTagName.AssignASCII(name);
  return nsHTMLTags::LookupTag(aTagName);
}

/**
 * Gives birth to a new fmbl_node and appends it to the end of the the specified
 * parent's (raw, exposed C) array of children.  The new child corresponds to
 * the specified tag type, is flagged appropropriately (FB_FLAG_PRECACHE, for example),
 * and comes from the specified line of the original source file.
 *
 * Most of the work comes in maintaining the C arrays and properly managing memory.
 * Note that fmbl_nodes are exposed as data structures to the PHP layer of our servers,
 * and that means they're best written in pure, unadulterated C.  This further requires
 * that we manually manage our dynamically allocated C arrays.
 *
 * @param parent the node within the parse tree that's getting a new child.
 * @param eHTMLTag the tag type (expressed as a two-byte integer) of the new child
 *                 node being created.
 * @param flag a bit flag reminding us of any special processing this new node requires/deserves
 *             during pre-rendering and/or rendering.
 * @param line_num the line number where the corresponding node in the original document can be
 *                 found.
 * @return the address of the new, fully initialized child node (which is also embedded within the
 *         children array of the node addressed by parent.)
 */

fbml_node *fbml_node_add_child(fbml_node *parent, short eHTMLTag,
			       char flag, int line_num)
{
  if (parent->children == NULL) {
    ASSERT(parent->children_count == 0);
    ASSERT(parent->children_alloc == 0);
    parent->children_alloc = 4; // start with a larger value, since we have the memory, and malloc is expensive
    parent->children =
      (fbml_node **) malloc(parent->children_alloc * sizeof(fbml_node *));
  }

  unsigned short children_count = parent->children_count;
  unsigned short children_alloc = parent->children_alloc;

  if (children_count >= children_alloc) {
    children_alloc <<= 1;
    if (children_alloc < parent->children_alloc) return NULL; // overflowed
    fbml_node **new_children =
      (fbml_node **) malloc(children_alloc * sizeof(fbml_node *));
    memcpy(new_children, parent->children,
	   parent->children_alloc * sizeof(fbml_node *));
    free(parent->children);
    parent->children = new_children;
    parent->children_alloc = children_alloc;
  }

  fbml_node *new_node = new fbml_node();
  memset(new_node, 0, sizeof(fbml_node));
  new_node->parent = parent;
  new_node->eHTMLTag = eHTMLTag;
  new_node->text = NULL;
  new_node->tag_name = fbml_lookup_tag_name(eHTMLTag);
  new_node->line_num = line_num;
  new_node->flag = 0;
  new_node->children_flagged = 0;
  new_node->attributes_flagged = 0;
  fbml_node_add_flags(new_node, flag);
  parent->children[children_count++] = new_node;
  parent->children_count = children_count;
  return new_node;
}

/**
 * The children_flagged bitstring is taken to be the
 * union of all the flags marking any of a node's children.
 * We maintain this information to help optimize the traversal
 * of the parse tree, since there's no reason to descend into
 * a subtree if you can tell ahead of time there's nothing to be
 * done on behalf of any of the children.
 *
 * This function ensures that the specified node's flag includes
 * the specified flag.  It then proceeds to walk up the tree toward
 * the parent, making sure that each node along the walk is aware
 * that at least one of its children carries the specified flag.
 *
 * @param node the node being marked as carrying the specified flag.
 * @param flag the flag being carried (FB_FLAG_PRECACHE, for example)
 *
 * No return value.
 */

void fbml_node_add_flags(fbml_node *node, char flag)
{
  node->flag |= flag;
  if (flag) {
    for (fbml_node *p = node->parent; p != NULL; p = p->parent) {
      // check if flag is a subset of children_flagged
      if ((~p->children_flagged & flag) == 0 ) break; // ancestors already know
      p->children_flagged |= flag;
    }
  }
}

/**
 * Appends the specified name/value pair to the attributes array
 * of the specified node.  The code is much like that within
 * fmbl_node_add_child, except that the attributes array is being
 * updated instead.  Note that attributes can themselves carry flags.
 *
 * @param parent the node getting a new name/value attribute pair.
 * @param name the name of the attribute being added (deep copy always made)
 * @param value the value of the attribute being added (deep copy conditionally
 *              made.)
 * @param flag the flag value that should be attached to the new name/value pair.
 * @param copy_value boolean (in the form of an int: pure C needed here) that
 *                   states whether or not a deep copy of the value is needed.
 *
 * No return value.
 */

void fbml_node_add_attribute(fbml_node *parent, char *name, char *value,
                             char flag, int copy_value)
{
  if (parent->attributes == NULL) {
    ASSERT(parent->attribute_count == 0);
    ASSERT(parent->attribute_alloc == 0);
    parent->attribute_alloc = 4; // reasonable default allocation length
    parent->attributes =
      (fbml_attribute **) malloc(parent->attribute_alloc *
				 sizeof(fbml_attribute *));
  }

  unsigned short attribute_count = parent->attribute_count;
  unsigned short attribute_alloc = parent->attribute_alloc;

  if (attribute_count >= attribute_alloc) {
    attribute_alloc <<= 1; // double allocation length
    if (attribute_alloc < parent->attribute_alloc) return; // overflow, unlikely
    fbml_attribute **new_attributes =
      (fbml_attribute **) malloc(attribute_alloc *
                                 sizeof(fbml_attribute *));
    memcpy(new_attributes, parent->attributes,
	   parent->attribute_alloc * sizeof(fbml_attribute *));
    free(parent->attributes);
    parent->attributes = new_attributes;
    parent->attribute_alloc = attribute_alloc;
  }

  fbml_attribute *new_attribute = new fbml_attribute();
  new_attribute->name = strdup(name);
  new_attribute->value = copy_value ? strdup(value) : value;
  new_attribute->flag = flag;

  parent->attributes[attribute_count++] = new_attribute;
  parent->attribute_count = attribute_count;
  parent->attributes_flagged |= flag; // make sure owner of attribute knows about flag
}

/**
 * Scans the attributes array of the specified fbml_node for
 * the specified name, and if found, returns a shallow copy of
 * the corresponding value.  If the specified name is not included
 * in the attribute list, then we return NULL.
 *
 * @param node the address of the fbml_node to search.
 * @param name the name of the attribute of interest.
 * @return a shallow (shared) copy of the C-string value associated
 *         with the specified name, or NULL if the name isn't an
 *         attribute of this node.
 */

char *fbml_node_get_attribute(fbml_node *node, char *name)
{
  if (node == NULL) return NULL;

  for (int i = 0; i < node->attribute_count; i++) {
    fbml_attribute *attribute = node->attributes[i];
    if (strcasecmp(name, attribute->name) == 0) {
      return attribute->value;
    }
  }

  return NULL;
}

/**
 * Internal function used to decide whether or not the
 * specified 'value' (expressed as a C string) is
 * actually a legitimate integer.
 *
 * @param value the string being examined.
 * @return true if and only if the incoming string is
 *         numeric.
 */

static bool is_numeric(char *value)
{
  for (char *p = (*value == '-' ? value + 1 : value); *p; p++) {
    if (*p < '0' || *p > '9')
      return false;
  }

  return true;
}

/**
 * Convenience function that places an integer
 * in the space identified by result, provided the text
 * identified by value is something easily interpreted as
 * a boolean.  If the value string identifies a numeric
 * string, then the numeric equivalent is laid down in the
 * space addressed by result.  If the text addressed by
 * value is "yes","no","true", or "false", then the corresponding
 * 0 or 1 is placed in the space addressed by result.  If value
 * addresses something else, then we ignore result and return -1
 * to expressed conversion failure.
 *
 * @param value a C string that (hopefully) can be interpreted as
 *              a boolean value.
 * @param result the address of the space where the integer equivalent
 *               of the value text should be placed should things work out.
 * @result an error code signaling whether or not the conversion succeeded.
 *         1 means success, and -1 means failure.
 */

int fbml_node_attr_to_bool(char *value, int *result)
{
  if (is_numeric(value)) {
    *result = atoi(value) ? 1 : 0;
    return 1;
  } else if (strcasecmp(value, "yes") == 0 || strcasecmp(value, "true") == 0) {
    *result = 1;
    return 1;
  } else if (strcasecmp(value, "no") == 0 || strcasecmp(value, "false") == 0) {
    *result = 0;
    return 1;
  }

  return -1;
}

/**
 * Accepts the specific value (assumed to be the value attached to a color attribute)
 * and constructs the equivalent CSS string of the form "color: cccccc;", or one of the
 * predefined HTML color strings, such as "red", "blue", and "green".  Provided
 * there aren't any problems, we return a dynamically allocated C string that must be freed.
 *
 * @param value the text value attached to some color attribute within some tag.
 * @param result the address of the char * where we should place the C string containing
 *               the color information (i.e., the deep copy of the the "cccccc" mentioned
 *               above.)
 * @return 1 stating success, and -1 stating failure.
 */

int fbml_node_attr_to_color(char *value, char **result)
{
  char *css = NULL;
  char *error = NULL;
  string decl = "color: ";
  decl += value;

  int ret = fbml_sanitize_css((char *) decl.c_str(), 1, 0, 0, &css, &error);
  if (ret != 0 || ((error != NULL) && (*error != '\0'))) {
    if (css != NULL) free(css);
    if (error != NULL) free(error);
    return -1;
  }

  decl = css; // C++ string operator= out of C string
  free(css);

  if (error != NULL) free(error);
  if (decl.length() < 9 || // integrity check
      decl.substr(0, 7) != "color: " ||
      decl[decl.length() - 1] != ';') {
    return -1;
  }

  *result = strdup(decl.substr(7, decl.length() - 8).c_str());
  return 1;
}

/**
 * Recursively (in postorder manner) frees all dynamically
 * allocated memory associated with the tree.  This includes
 * all dynamically allocated C strings, attributes, arrays, and
 * fmbl_nodes.  It's careful to levy free against addresses produced
 * by malloc/realloc, and delete against addresses produced by new.
 *
 * @param tree the root of the entire tree being freed.
 */

void fbml_node_free(fbml_node *tree)
{
  if (tree->tag_name) {
    free(tree->tag_name);
  }

  if (tree->text) {
    free(tree->text);
  }

  for (int i = 0; i < tree->attribute_count; i++) {
    fbml_attribute *attribute = tree->attributes[i];
    free(attribute->name);
    free(attribute->value);
    delete attribute;
  }

  free(tree->attributes);

  for (int i = 0; i < tree->children_count; i++) {
    fbml_node_free(tree->children[i]);
  }
  free(tree->children);

  delete tree;
}

/**
 * Sanitizes the supplied attribute string so that neither
 * "\x" nor "\u" can appear within the last four characters
 * of the string.  (Something like "\uC0C0" is fine, but
 * something like "\uC00" or "\xC0" is not.)  The threat
 * here is that older browsers--most notably IE6, and perhaps
 * others--are sloppy about checking whether or not a unicode
 * or hexademical escape is followed by four hexademical digits.
 * As a result, something like 'foo="\uC0" bar=" onload="dobadthings()"
 * can be misinterpreted and unwittingly introduce '\uC0" bar='
 * and 'dobadthings()' as attributes when that probably wasn't
 * the intent (and certainly shouldn't be allowed.)  This is more
 * agressive than we really need to be, but better to be aggressive
 * and not have a security hole than to be too lax.
 *
 * @param attribute reference to the attribute string that should
 *                  be cleansed, as outlined above.
 */

static const char kEscapedChars[] = {'x', 'u'};
static const int kNumEscapedChars =
  sizeof(kEscapedChars)/sizeof(kEscapedChars[0]);
static const int kNumCharsBeyondEscapeNeeded = sizeof("\\u0000") - 1;
static void remove_trailing_unicodes(string& attribute)
{
  bool done = false; // need to make at least one pass
  while (!done) {
    string::size_type escapeIndex = attribute.find_last_of('\\');
    if (escapeIndex == string::npos) return; // nothing more to do
    string::size_type attributeLength = attribute.size();
    done = true; // assume we're done unless we see otherwise
    for (int i = 0; i < kNumEscapedChars; i++) {
      if ((attribute[escapeIndex + 1] == kEscapedChars[i]) &&
          (attributeLength - escapeIndex < kNumCharsBeyondEscapeNeeded)) {
        attribute = attribute.substr(0, escapeIndex);
        done = false;
        break;
      }
    }
  }
}

/**
 * Plants the serialization of the node addressed by
 * tree into the string referenced by out.  Note that just
 * the node itself is being serialized.  There's no recursion here.
 * This function is used by the recursive version defined as fbml_node_output.
 *
 * @param tree the address of the node being serialized.
 * @param out a reference to the C++ string where the serialization should
 *            placed.
 *
 * No return value.
 */

static void fbml_node_output_front(fbml_node *tree, string &out)
{
  if (tree->attribute_count == 0) {
    out += "<";
    out += tree->tag_name;
    out += ">";
  } else {
    out += "<"; out += tree->tag_name;
    for (int i = 0; i < tree->attribute_count; i++) {
      fbml_attribute *attribute = tree->attributes[i];
      out += " ";
      out += attribute->name;
      out += "=\"";
      if (attribute->flag & FB_FLAG_ATTR_SCRIPT) {
        out += attribute->value;
      } else {
        string rawAttribute;
        for (char *p = attribute->value; *p; p++) {
          switch (*p) {
          case '"': rawAttribute += "&quot;"; break;
          case '<': rawAttribute += "&lt;";   break;
          case '>': rawAttribute += "&gt;";   break;
          case '&': rawAttribute += "&amp;";  break;
          default:  rawAttribute += *p;       break;
          }
        }

        remove_trailing_unicodes(rawAttribute);
        out += rawAttribute;
      }
      out += "\"";
    }
    out += ">";
  }
}

/**
 * Recursive function that produces the serialization of the entire
 * tree rooted by 'tree', and places that serialization in the
 * string referenced by 'out'.
 *
 * @param tree the address of the root of the entire tree being serialized.
 * @param out the string to which the full serialization should be appended.
 * @param no_escape a Boolean value indicating whether or not characters
 *                  like ", >, and < should be replaced by their equivalent
 *                  HTML escape sequence.
 */

static void fbml_node_output(fbml_node *tree, string& out, bool no_escape)
{
  ASSERT(tree != NULL);
  if (tree->text) {
    if (no_escape) {
      out += tree->text;
    } else {
      for (char *p = tree->text; *p != '\0'; p++) {
        switch (*p) {
        case '"': out += "&quot;"; break;
        case '<': out += "&lt;";   break;
        case '>': out += "&gt;";   break;
        default:  out += *p;       break;
        }
      }
    }

    return;
  }

  if (tree->tag_name && *tree->tag_name) {
    fbml_node_output_front(tree, out);
  } else if (tree->eHTMLTag == eHTMLTag_comment) {
    out += "<!--";
  }

  int dont_escape = tree->eHTMLTag == eHTMLTag_script || tree->eHTMLTag == eHTMLTag_comment;
  int subtag_count = 0;
  for (int i = 0; i < tree->children_count; i++) {
    fbml_node *child = tree->children[i];
    if (child->text == NULL) subtag_count++;
    fbml_node_output(child, out, dont_escape);
  }

  if (tree->tag_name && *tree->tag_name) {
    if (tree->eHTMLTag != eHTMLTag_br) {
      out += "</";
      out += tree->tag_name;
      out += ">";
    }
  } else if (tree->eHTMLTag == eHTMLTag_comment) {
    out += "-->";
  }
}

/**
 * Returns a dynamically allocated C string containing the full
 * serialization of the parse tree rooted at tree.
 *
 * @param tree the address of the root of the entire tree to be
 *             traversed and serialized.
 * @return a dynamically allocated C string storing the full serialization
 *         of the parse tree rooted at tree.
 */

char *fbml_node_print(fbml_node *tree)
{
  string out;
  out.reserve(1024);
  fbml_node_output(tree, out, false);
  return strdup(out.c_str());
}

/**
 * Function designed to serialize just the text of the <style> and
 * <fb:conditional-style> tags.
 *
 * @param node the root of the DOM tree to be traversed.
 * @param the string to which all content should be printed.
 */

static void fbml_node_collect_css_rec(fbml_node *node, string& out)
{
  bool cond = false;
  if (node->flag & FB_FLAG_STYLE && node->children_count) {
    string n(node->tag_name);
    if (n == "fb:conditional-style" && node->attribute_count == 1) {
      out += "<!--[if";
      out +=  node->attributes[0]->value;
      out += "]>";
      cond = true;
    }
    out += node->children[0]->text;
    if (cond) {
      out += "<![endif]-->";
    }
  }

  if (node->children_flagged & FB_FLAG_STYLE) {  // only traverse children if
    for (int i = 0; i < node->children_count; i++) { // you know there's style content somewhere.
      fbml_node_collect_css_rec(node->children[i], out);
    }
  }
}

/**
 * Rcursively renders the FBML tree rooted at the 'node' according the
 * the functionality embedded within the fbml_node_renderer address by 'rend'
 * The rendering product is appended to the string referenced by out.
 *
 * @param node the root of the full subtree being rendered.
 * @param no_special_html true if rendering include comments.
 * @param rend the address of the fbml_node_renderer struct that helps to guide
 *             rendering.
 * @param out the C++ string where the full rendering of the FBML tree rooted
 *            at node should be appended.
 */

static void fbml_node_render_html_rec(fbml_node *node, int no_special_html,
				      fbml_node_renderer *rend, string& out)
{
  if (node->eHTMLTag == eHTMLTag_userdefined ||
      node->eHTMLTag == eHTMLTag_unknown) return;

  if (node->flag & FB_FLAG_FBNODE) {
    char *renderer_result = rend->pfunc_renderer(node, rend->fb_node_data);
    out += renderer_result;
    free(renderer_result);
  } else if (node->eHTMLTag == fbml_html_comment_tag) {
    //output comments
    if (no_special_html) {
      fbml_node_output(node, out, false);
    }
  } else {
    if (!(no_special_html) &&
        (node->flag & FB_FLAG_SPECIAL_HTML ||
         node->flag & FB_FLAG_HAS_SPECIAL_ATTR)
        ) {
      char *renderer_result = rend->pfunc_renderer(node, rend->html_node_data);
      out += renderer_result;
      free(renderer_result);
    } else if (node->children_flagged & FB_FLAG_FBNODE ||
               ( !(no_special_html) &&
                 (node->children_flagged & FB_FLAG_SPECIAL_HTML ||
                  node->children_flagged & FB_FLAG_HAS_SPECIAL_ATTR))
               ) {

      // just render the skeleton
      fbml_node_output_front(node, out);

      for (int i = 0; i < node->children_count; i++) {
        fbml_node_render_html_rec(node->children[i], no_special_html, rend, out);
      }
      out += "</";
      out += node->tag_name;
      out += ">";

    } else {
      //score! Just print it.
      fbml_node_output(node, out, false);
    }
  }
}

/**
 * Global variables storing the configuration that guides
 * FBML compilation, precacahing, and rendering.
 */

static bool g_fbml_expanded = false;
static std::vector<int> g_attr_flags;
static std::vector<int> g_tag_flags;
static std::vector<contexts> g_context_schemas;

/**
 * Trivial function that exists only because the bool data
 * type doesn't exist in pure C, so we need a trivial wrapper
 * to convert a true or false to a 1 or 0.
 *
 * @return 1 if and only if g_fbml_expanded is true, 0 otherwise.
 */

int fbml_tag_list_expanded()
{
  return g_fbml_expanded ? 1 : 0;
}

/**
 * Accepts the 'flaggable_attrs' array, which maps attribute flag
 * types (such as FB_NODE_ATTR_SPECIAL) to an array of all those
 * attribute types that should be marked with that flag type.  The
 * vector<int>, referenced by 'attr_flags', is really a compact
 * dictionary mapping ints to ints.  The keys (implicitly represented
 * by the indices within the vector) are the integers that back the
 * attribute types, and the values are the bitwise unions of all of the
 * flags that should decorate the attribute they map back to.
 *
 * @param attr_flags the vector where all of the bitwise unions of
 *                   applicable attribute flags should be placed.  Information
 *                   for the attribute type backed by the number 0 is stored
 *                   in position 0, information for the attribute type
 *                   backed by the number 1 is stored in position 1, and
 *                   so forth.  The bitwise union of all those attribute
 *                   flags that apply to the ith attribute type are placed
 *                   in position i of the vector<int>
 * @pararm flaggable_attrs array of fbml_flaggable_attrs *s, where each
 *                         fbml_flaggable_attrs maps an attribute flag type
 *                         to the set of all those node types carrying that
 *                         flag.
 */

static void fbml_prepare_attr_flags(std::vector<int>& attr_flags,
                                    fbml_flaggable_attrs **flaggable_attrs)
{
  if (flaggable_attrs) {
    for (fbml_flaggable_attrs **p = flaggable_attrs; *p; p++) {
      int flag = (*p)->flag;
      char **attrs = (*p)->attrs;
      for (char **attr = attrs; *attr; attr++) {
        nsString aAttrName;
        aAttrName.AssignASCII(*attr);
        nsHTMLAttr htmlAttr = nsHTMLAttrs::LookupAttr(aAttrName);
        if (htmlAttr >= 0 && htmlAttr < attr_flags.size()) {
          attr_flags[htmlAttr] |= flag;
        }
      }
    }
  }
}

/**
 * Constructs the 'tag_flags' vector--already sized so there's
 * one integer for every tag type--to contain the bitwise union
 * of all of the tag flags that apply to any given tag type.  Index i
 * within 'tag_flags' is set to store flag information on behlf of the
 * tag type backed by the number i.  The value stores at position
 * i is the bitwise union of all of the tag flags that apply to the
 * ith tag type.  All of this tag and flag information resides in
 * the 'flaggable_tags' array.
 *
 * @param tag_flags a reference to a perfectly sized vector where
 *                  all of these bitmask unions should be placed.
 * @param flaggable_tags a less compact model of all the flag types
 *                       and the node types that each of the flags applies
 *                       to.
 */

static void fbml_prepare_tag_flags(vector<int>& tag_flags,
                                   fbml_flaggable_tags **flaggable_tags)
{
  if (flaggable_tags) {
    for (fbml_flaggable_tags **p = flaggable_tags; *p; p++) {
      int flag = (*p)->flag;
      char **tags = (*p)->tags;
      for (char **tag = tags; *tag; tag++) {
        nsString aTagName;
        aTagName.AssignASCII(*tag);
        nsHTMLTag htmlTag = nsHTMLTags::LookupTag(aTagName);
        if (htmlTag != eHTMLTag_userdefined && htmlTag != eHTMLTag_unknown &&
            htmlTag >= 0 && htmlTag < tag_flags.size()) {
          tag_flags[htmlTag] |= flag;
        }
      }
    }
  }
}

/**
 * Quick and dirty predicate macros that decides whether or not
 * a tag id or an attribute id are valid.
 */

#define VALID_TAG(tag)                                          \
  tag != eHTMLTag_userdefined && tag != eHTMLTag_unknown &&     \
  tag >= 0 && tag < total_tag_count                             \

#define VALID_ATTR(attr)                                        \
  attr != eHTMLAttr_userdefined && attr != eHTMLAttr_unknown && \
  attr >= 0 && attr < total_attr_count                          \


/**
 * Configures a collection of global variables that are constantly
 * consulted during the compilation of any FBML document.
 *
 * @param new_tags a NULL terminated array of C strings, where each string is
 *                 some new tag type that should be recognized as a legitimate
 *                 HTML/FBML tag type.
 * @param new_attrs a NULL terminated array of C strings, where each string is
 *                  some new attribute type that should be recognized by the
 *                  FBML parser.
 * @param flaggable_tags a short array of records that map node flag types to
 *                       a list of all node types that should be marked with that
 *                       flag.  The array is NULL-terminated, but the effective size
 *                       of the array is equal to the number of #define constants
 *                       defining node flags.
 * @param flaggable_attrs a short array of records that map attribute flag types to
 *                        the list of all those attributes that should be marked with that
 *                        flag.  The array is NULL-terminated, but the size of the
 *                        array is equal to the number of #define constants above
 *                        defining attribute flags.
 * @param fbml_context_schema an array of all of the different schemas relevant to
 *                            parsing FBML documents.
 */

void fbml_expand_tag_list(char **new_tags,
                          char **new_attrs,
                          fbml_flaggable_tags **flaggable_tags,
                          fbml_flaggable_attrs **flaggable_attrs,
                          fbml_context_schema **schemas) {
  // expand HTML tags
  if (g_fbml_expanded) {
    nsHTMLTags::RemoveExpandedTags();
    nsHTMLAttrs::RemoveExpandedAttrs();
  }
  int i = 0;
  for (char **p = new_tags; *p; p++) {
    i++;
    nsHTMLTags::AddTag(eHTMLTag_userdefined + i, *p);
  }
  nsHTMLElement::ExpandTable(i);

  int total_tag_count = eHTMLTag_userdefined + i + 1;

  // expand tag flag data structure
  g_tag_flags.clear();
  g_tag_flags.resize(total_tag_count);
  fbml_prepare_tag_flags(g_tag_flags, flaggable_tags);

  i = 0;
  for (char **p = new_attrs; *p; p++) {
    i++;
    nsHTMLAttrs::AddAttr(eHTMLAttr_userdefined + i, *p);
  }

  int total_attr_count = eHTMLAttr_userdefined + i + 1;

  // expand attr flag data structure
  g_attr_flags.clear();
  g_attr_flags.resize(total_attr_count);
  fbml_prepare_attr_flags(g_attr_flags, flaggable_attrs);

  // prepare context schemas
  g_context_schemas.clear();
  g_context_schemas.resize(total_tag_count);
  if (schemas) {
    for (fbml_context_schema **p = schemas; *p; p++) {
      nsString aTagName;
      nsString aAttrName;
      aTagName.AssignASCII((*p)->context_tag);
      nsHTMLTag context_tag = nsHTMLTags::LookupTag(aTagName);
      if (VALID_TAG(context_tag)) {
        g_context_schemas[context_tag].tag_name = (*p)->context_tag;
        vector<vector<char> > &rules = g_context_schemas[context_tag].tag_rules;
        vector<char > &attr_rules = g_context_schemas[context_tag].attr_rules;
        rules.resize(total_tag_count);
        for (unsigned int i = 0; i < total_tag_count; i++) {
          rules[i].resize(total_tag_count);
        }

        attr_rules.resize(total_attr_count);

        for (fbml_schema **schema = (*p)->schema; *schema; schema++) {
          if (!(*schema)->ancestor_tag) continue;
          aTagName.AssignASCII((*schema)->ancestor_tag);

          nsHTMLTag parent = nsHTMLTags::LookupTag(aTagName);
          if (VALID_TAG(parent)) {
            for (char **p = (*schema)->illegal_children; *p; p++) {
              aTagName.AssignASCII(*p);
              nsHTMLTag child = nsHTMLTags::LookupTag(aTagName);
              if (VALID_TAG(child)) {
                rules[child][parent] = 1;
              }
            }
            for (char **p = (*schema)->illegal_children_attr; *p; p++) {
              aAttrName.AssignASCII(*p);
              nsHTMLAttr attr = nsHTMLAttrs::LookupAttr(aAttrName);
              if (VALID_ATTR(attr)) {
                attr_rules[attr] = 1;
              }
            }
          }
        }
      }
    }
  }

  g_fbml_expanded = true;
}

/**
 * Oversees all of the stages needed to construct a sanitized, FBML doc tree
 * out of the supplied FBML string.  If all goes well, the root of the
 * parse tree is planted in the space addressed by 'tree'.  The accumulation
 * of any and all error messages is placed in the space addressed by 'error'.
 *
 * 'body_only', 'preserve_comments', and 'skip_schema_checking' are all user
 * supplied flags.  'css_sanitizer', 'js_sanitizer', 'attr_rewriter' identify
 * the pipeline of components that scrub the FBML text.
 *
 * @param fbml a C string containing some FBML text.
 * @param body_only true if the supplied FBML is fragment representing
 *                  the <body> portion of a full FBML document.
 * @param preserve_comment true if the parse tree should retain the comments and
 *                         allow them to contribute to the FBML parse tree, and
 *                         false if they should be stripped out and not contribute.
 * @param css_sanitizer the address of the fully initialized fbml_css_sanitizer
 *                      that will transform all of the CSS enough to zero out
 *                      the chances that the applications CSS will impact Facebook's
 *                      CSS.  The CSS sanitizer really dictactes what these transformations
 *                      are.
 * @param js_sanitizer the address of the fully initialized js_santizer designed to
 *                     transform any and all Javascript just enough that it won't
 *                     cause any problems when loaded and executed.  The JS sanitizer
 *                     decided what these transformations are.
 * @param attr_rewriter the address of the fully initialized fbml_attr_rewriter that knows
 *                      how to examine all attribute lists and remove/rewrite them in
 *                      such a way that they won't negatively impact the user.
 * @param flaggable_tags a NULL-terminated array of fbml_flaggable_tags record pointers, or NULL
 *                       if there aren't any flaggable tags to worry about.
 * @param flaggable_attrs a NULL-terminated array of fbml_flaggable_attrs record pointers, or NULL
 *                        if there aren't any flaggable attributes to worry about.
 * @param tree the address of the fmbl_node * that should be updated with the address of the root node
 *             of the FBML tree.
 * @param error the address of the C string where the accumulation of parse error information should
 *              be placed.  If NULL is supplied, then that's taken as a flag to not bother with the
 *              error message.
 */

int fbml_parse(char *fbml, int body_only,
	       int preserve_comment, int skip_schema_checking,
               fbml_css_sanitizer *css_sanitizer,
               fbml_js_sanitizer *js_sanitizer,
               fbml_attr_rewriter *attr_rewriter,
               fbml_flaggable_tags **flaggable_tags,
               fbml_flaggable_attrs **flaggable_attrs,
               fbml_node **tree, char **error)
{
  ASSERT(fbml);
  nsresult rv;
  nsString aSourceBuffer;

  if (body_only)
    aSourceBuffer.AssignLiteral("<html><body>");
  aSourceBuffer.AppendASCII(fbml);
  if (body_only)
    aSourceBuffer.AppendLiteral("</body></html>");

  nsCOMPtr<nsIParser> parser(new nsParser());

  std::vector<int> tag_flags(g_tag_flags);
  fbml_prepare_tag_flags(tag_flags, flaggable_tags);

  std::vector<int> attr_flags(g_attr_flags);
  fbml_prepare_attr_flags(attr_flags, flaggable_attrs);

  nsCOMPtr<FBMLContentSink>
    sink(new FBMLContentSink(parser.get(), preserve_comment, skip_schema_checking,
                             css_sanitizer, js_sanitizer, attr_rewriter, tag_flags, attr_flags,
                             g_context_schemas, tree));
  parser->SetContentSink(sink.get());

  nsCString aContentType;
  aContentType.AssignLiteral(kHTMLTextContentType);
  NS_CALL(parser->Parse(aSourceBuffer, 0, aContentType, PR_TRUE, PR_TRUE,
                        eDTDMode_quirks));

  if (error != NULL)
    *error = strdup(sink->GetError());

  return 0;
}

static void fbml_js_error_reporter(JSContext *cx, const char *message,
                                   JSErrorReport *report) {
  string *sError = (string*)cx->data;
  if (report) {
    char buf[128];
    snprintf(buf, sizeof(buf), "JS Error (line %d char %d): ",
             report->lineno, report->tokenptr - report->linebuf);
    *sError += buf;
  } else {
    *sError += "JS Error: ";
  }
  *sError += message;
}

/**
 * Constructs a sanitized CSS string out of the CSS string supplied via
 * 'css' and places it at the address identified by 'sanitized_css'.  The
 * accumulation of any error information is placed in the C string addressed
 * by 'error'.
 *
 * @param css the CSS string to sanitize.
 * @param declaration_only a Boolean value: true if the CSS was supplied
 *                         as a style attribute value (as in style="width: 60px;'),
 *                         and false if the CSS was embedded inside style tags (as
 *                         in <style> .myStyle { width: 60px; } </style>.
 * @param line_number the line number where the CSS being sanitized resides
 *                    within the original file.
 * @param css_sanitizer the address of the fbml_css_sanitizer record that guides the
 *                      CSS sanitization.
 * @param sanitized_css the address identifying the space where the sanitized CSS string
 *                      should be placed.
 * @param error the address where an error string should be placed.  The error string,
 *              is dynamically allocated, and storing the accumulation of all of the
 *              error messages that presented themselves during the parse.
 * @return always 0
 */

int fbml_sanitize_css(char *css, int declaration_only, int line_number,
                      fbml_css_sanitizer *css_sanitizer,
                      char **sanitized_css, char **error)
{
  ASSERT(css);
  ASSERT(line_number >= 0);
  nsresult rv;

  if ((css == NULL) || (*css == '\0')) {
    if (sanitized_css) {
      *sanitized_css = strdup("");
    }

    return 0;
  }

  nsCOMPtr<nsICSSParser> parser;
  NS_CALL(NS_NewCSSParser(getter_AddRefs(parser)));

  nsString buffer;
  buffer.AssignASCII(css);

  FBMLStyleSheet style_sheet(css_sanitizer, sanitized_css, error);
  nsICSSStyleSheet *pSheet = (nsICSSStyleSheet*)&style_sheet;

  if (declaration_only) {
    parser->SetStyleSheet(pSheet);

    nsCOMPtr<nsICSSStyleRule> rule;
    NS_CALL(parser->ParseStyleAttribute(buffer, nsnull, nsnull,
                                        getter_AddRefs(rule), line_number));
    style_sheet.AppendStyleRule(rule, true);
  } else {
    nsCOMPtr<nsIUnicharInputStream> input;
    NS_CALL(NS_NewStringUnicharInputStream(getter_AddRefs(input), &buffer,
                                           PR_FALSE));
    NS_CALL(parser->Parse(input, NULL, NULL, line_number, pSheet));
  }
  style_sheet.OnParseComplete();

  return 0;
}

///////////////////////////////////////////////////////////////////////////////
// JavaScript functions

static char *DEFAULT_JS_BANNED_PROPERTIES[] = {
  "__proto__",
  "__parent__",
  "constructor",
  "caller",
  "watch",
  "__defineGetter__",
  "__defineSetter__",
  NULL
};

/**
 * Utility function designed to populate the fbml_js_sanitizer
 * addressed by 'js_sanitizer'.  The implementation is brute force,
 * but self-explanatory.
 *
 * @param js_sanitizer the address of the fbml_js_sanitizer being
 *        configured.
 */

void fbml_js_sanitizer_init(struct fbml_js_sanitizer *js_sanitizer)
{
  memset(js_sanitizer, 0, sizeof(fbml_js_sanitizer));
  js_sanitizer->identifier_prefix = "app_";
  js_sanitizer->this_replacement = "(ref(this))";
  js_sanitizer->arguments_replacement = "arg(arguments)";
  js_sanitizer->banned_properties = DEFAULT_JS_BANNED_PROPERTIES;
  js_sanitizer->banned_property_replacement = "__unknown__";
  js_sanitizer->array_element_format = "idx(%s)";
}

/**
 * Sanitizes a fragment of JavaScript using a cannibalization of
 * Mozilla's SpiderMonkey implementation.
 *
 * @param js a string of Javascript.
 * @param js_len the length of the Javascript string supplied via 'js'.
 * @param reserved reserved for future releases.
 * @param line_number the line number in the original source file where
 *                    the string of Javascript came from.
 * @param js_sanitizer the address of the fbml_js_sanitizer record programmed
 *                     to sanitize Javascript strings.
 * @param sanitized_js the address where the dynamically allocated
 *                     string of sanitized Javascript should be placed.
 * @param error the address where the accumulation of all error
 *              messages should be placed.
 * @return 0 if there were problems, and 1 otherwise.
 */

int fbml_sanitize_js(char *js, int js_len, int reserved, int line_number,
                     fbml_js_sanitizer *js_sanitizer,
                     char **sanitized_js, char **error) {
  ASSERT(js);
  ASSERT(line_number >= 0);

  if (!js || !*js) {
    if (sanitized_js) {
      *sanitized_js = m(strdup(""));
    }
    if (error) {
      *error = m(strdup(""));
    }
    return 0;
  }
  if (!js_sanitizer || !js_sanitizer->identifier_prefix ||
      !*js_sanitizer->identifier_prefix) {
    if (sanitized_js) {
      *sanitized_js = m(strdup(js));
    }
    if (error) {
      *error = m(strdup(""));
    }
    return 0; // forgiving
  }

  string sanitized_chars;
  bool more_tokens = true;

  // 1. SpiderMonkey initialization
  JSRuntime *rt = JS_NewRuntime(8192);
  if (!rt) {
    return compose_error(error, "JS_NewRuntime failed", 0);
  }
  JSContext *cx = JS_NewContext(rt, 8192);
  if (!cx) {
    JS_DestroyRuntime(rt);
    return compose_error(error, "JS_NewContext failed", 0);
  }
  string sError;
  cx->data = &sError;
  JS_SetErrorReporter(cx, fbml_js_error_reporter);

  // 2. Convert UTF8 to UCS4
  int ret = 0;
  size_t length = js_len;
  jschar *chars = js_InflateString(cx, js, &length);
  if (!chars) goto exit;

  // 3. Initialize tokenizer
  JSTokenStream *ts =
    js_NewTokenStream(cx, chars, length, NULL, line_number, NULL);
  if (!ts) goto exit;

  // 4. Tokenize and prefix identifiers
  size_t prefix_len = strlen(js_sanitizer->identifier_prefix);
  jschar *prefix = js_InflateString(cx, js_sanitizer->identifier_prefix,
                                    &prefix_len);
  prefix_len *= sizeof(jschar);

  char *this_replacement = js_sanitizer->this_replacement;
  if (!this_replacement || !*this_replacement) {
    this_replacement = "this";
  }
  size_t this_len = strlen(this_replacement);
  jschar *this_rep = js_InflateString(cx, this_replacement, &this_len);
  this_len *= sizeof(jschar);

  sanitized_chars.reserve(length << 1);
  jschar *userbuf_pos = ts->userbuf.ptr; // last copied position
  while (more_tokens) {
    switch (js_GetToken(cx, ts)) {
    case TOK_NAME:
      {
        size_t len = ts->userbuf.ptr - userbuf_pos -
          (ts->linebuf.limit - ts->linebuf.ptr + 1);
        size_t token_len = ts->tokenbuf.ptr - ts->tokenbuf.base;
        len -= token_len;
        if (len) {
          sanitized_chars.append((char*)userbuf_pos, len * sizeof(jschar));
          userbuf_pos += len;
          if (*(userbuf_pos-1) == '.') break; // properties or methods
        }
        sanitized_chars.append((char *)prefix, prefix_len);
        sanitized_chars.append((char*)userbuf_pos, token_len * sizeof(jschar));
        userbuf_pos += token_len;
      }
      break;
    case TOK_PRIMARY:
      if (ts->tokens[ts->cursor].t_op == JSOP_THIS) {
        size_t len = ts->userbuf.ptr - userbuf_pos -
          (ts->linebuf.limit - ts->linebuf.ptr + 1);
        size_t token_len = ts->tokenbuf.ptr - ts->tokenbuf.base;
        len -= token_len;
        if (len) {
          sanitized_chars.append((char*)userbuf_pos, len * sizeof(jschar));
          userbuf_pos += len;
        }
        sanitized_chars.append((char *)this_rep, this_len);
        userbuf_pos += token_len;
      }
      break;
    case TOK_ERROR:
    case TOK_EOF:
      {
        size_t len = ts->userbuf.limit - userbuf_pos -
          (ts->linebuf.limit > ts->linebuf.ptr ?
           (ts->linebuf.limit - ts->linebuf.ptr) : 0);
        if (len) {
          sanitized_chars.append((char*)userbuf_pos, len * sizeof(jschar));
        }
        more_tokens = false;
      }
      break;
    case TOK_WITH:
      {
        char buf[128];
        snprintf(buf, sizeof(buf),
                 "JS Error (line %d char %d): \"with\" is not supported\n",
                 ts->tokens[ts->cursor].pos.begin.lineno,
                 ts->tokens[ts->cursor].pos.begin.index);
        sError += buf;
      }
      break;
    default:
      break;
    }
  }
  free(prefix);
  free(this_rep);

  // 5. Convert UCS4 back to UTF8
  char *deflated =
    js_DeflateString(NULL, (jschar*)sanitized_chars.data(),
                     (sanitized_chars.length()/sizeof(jschar)));

  if (sanitized_js) {
    *sanitized_js = deflated;
  } else {
    free(deflated);
  }

  if (error) {
    *error = m(strdup(sError.c_str()));
  }

 exit:
  if (chars) JS_free(cx, chars);

  JS_DestroyContext(cx);
  JS_DestroyRuntime(rt);
  JS_ShutDown();
  return ret;
}

/**
 * Employs the FBJSParser type to actually do the sanitizing.
 * The FBJSParser class actually tokenizes and otherwise compiles
 * the supplied Javascript and synthesizes an FBJS-compliant version.
 *
 * @param js a string of Javascript.
 * @param js_len the length of the Javascript string supplied via 'js'.
 * @param reserved reserved for future releases.
 * @param line_number the line number in the original source file where
 *                    the string of Javascript came from.
 * @param js_sanitizer the address of the fbml_js_sanitizer record programmed
 *                     to sanitize Javascript strings.
 * @param sanitized_js the address where the dynamically allocated
 *                     string of sanitized Javascript should be placed.
 * @param error the address where the accumulation of all error
 *              messages should be placed.
 * @return 0 if there were problems, and 1 otherwise.
 *
 * This is an updated version of FBJS parsing that isn't quite ready
 * for prime time just yet.  Eventually it will replace what's above.
 */

/*
int fbml_sanitize_js(char *js, int js_len, int reserved, int line_number,
                     fbml_js_sanitizer *js_sanitizer,
                     char **sanitized_js, char **error) {
  ASSERT(js);
  ASSERT(line_number >= 0);

  if (!js || !*js || !js_sanitizer) {
    if (sanitized_js) *sanitized_js = strdup(js ? js : "");
    if (error) *error = strdup("");
    return 0;
  }

  FBJSParser parser(js_sanitizer);
  int ret = parser.parse(js, js_len, line_number, NULL);
  if (sanitized_js) {
    *sanitized_js = parser.detachOutput();
  }
  if (error) {
    *error = strdup(parser.getError());
  }
  return ret;
}
*/
/**
 * Constructs a dynamically allocated C string that stores the full serialization
 * of the FBML tree rooted at the fbml_node addressed by 'node'.  The format of the
 * full serialization is dictated by the fbml_node_renderer addressed by 'rend'.
 *
 * @param root the address of the root node of the full FBML tree.
 * @param no_special_html 0 if characters like <, >, and " should be written using their
 *                        HTML escpae sequence equivalents, and 1 otherwise.
 */

static const int kDefaultReservationLength = 1 << 12;
char *fbml_node_render_children(fbml_node *node, int no_special_html, fbml_node_renderer *rend)
{
  string out;
  out.reserve(kDefaultReservationLength);
  for (int i = 0; i < node->children_count; i++) {
    fbml_node_render_html_rec(node->children[i], no_special_html, rend, out);
  }
  return strdup(out.c_str());
}

/**
 * Applies a user-supplied function against all nodes in the supplied
 * FBML tree that are marked with the FB_FLAG_PRECACHE bit.  The user-supplied
 * function (along with any client data) is packaged within the fbml_node_precacher
 * record addressed by 'precacher'.
 *
 * @param node the root of the FBML tree being traversed.
 * @param precacher the address of an fbml_node_precacher record, which packages
 *                  two fields: 'pfunc_precacher', which is the function which
 *                  should be applied to all of those nodes carrying the FB_FLAG_PRECACHE
 *                  flag, and 'precache_node_data', which is any auxiliary data
 *                  the caller would like to be passed along as the second argument
 *                  of the 'pfunc_precacher' function.
 */

void fbml_node_precache(fbml_node *node, fbml_node_precacher *precacher) {

  if (node->flag & FB_FLAG_PRECACHE) {
    char *precache_result = precacher->pfunc_precacher(node, precacher->precache_node_data);
    free(precache_result);
  }

  //short circuit if no children set, children_flagged field maintained to help with this
  if (node->children_flagged & FB_FLAG_PRECACHE) {
    for (int i=0; i < node->children_count; i++) {
      fbml_node_precache(node->children[i], precacher);
    }
  }
}

/**
 * Recursively visits the FBML tree rooted at 'node', and updates
 * the supplied 'precache_map' to include information about those
 * nodes carrying the FB_FLAG_PRECACHE.  The precache_map is understood
 * to only carry information about FBML nodes marked with the
 * FB_FLAG_PRECACHE bit.  The intent here is to group nodes by HTML tag
 * type, so that 'img' maps to a list of those fbml_node *s modeling
 * <img ...> nodes, 'link' maps to a list of those fbml *s modeling
 * <a ...> nodes, and so forth.
 *
 * Notice that this doesn't actually apply any functionality to those
 * nodes with precache needs.  Instead, it bundles/batches all of those
 * nodes that have precache needs into the supplied node_map.
 *
 * @param node the address of the root node of the tree being traversed.
 * @param precache_map the map<unsigned short, list<fbml_node *> *> that
 *        maps tag types (such as eHTMLTag_img, eHTMLTag_a, and so forth)
 *        to the list of pointers to fbml_nodes that carry that tag type.
 */

void fbml_node_batch_precache_rec(fbml_node *node,
                                  node_map& precache_map)
{
  if (node->flag & FB_FLAG_PRECACHE) {
    node_map::iterator check = precache_map.find(node->eHTMLTag);
    if (check == precache_map.end()) {
      list<fbml_node *> *ls = new list<fbml_node *>();
      ls->push_back(node);
      precache_map[node->eHTMLTag] = ls;
    } else {
      list<fbml_node *> *ls = check->second;
      ls->push_back(node);
    }
  } // else skip, but we still need to consider the children

  if (node->children_flagged & FB_FLAG_PRECACHE) {
    for (int i = 0; i < node->children_count; i++) {
      fbml_node_batch_precache_rec(node->children[i], precache_map);
    }
  }
}

/**
 * Dynamically allocates an fbml_precache_bunch on behalf of the
 * supplied list of fbml_node addresses.  The fbml_precache_bunch is
 * the exposed C version of the pair<unsigned short, list<fbml_node *> *>'s
 * owned by the precache_map of the above function, but C++ pairs don't pass
 * over the PHP/C-and-C++ boundary as cleanly as pure C structs do.
 *
 * @param nodes the list<fmbl_node *> * containing those pointers that should
 *              be planted inside the fbml_precache_bunch being constructed.
 * @return a freshly allocated fbml_precache_bunch constructed to be a logical
 *         replica of the incoming list<fbml_node *> addressed by 'nodes'.
 *         Note that the nodes array within the fbml_precache_bunch stores
 *         a NULL in the last position to mark the end of the array.  Also
 *         note that the fbml_precache_bode owns the array memory, but it
 *         does not own the fbml_nodes nor does it own tag string.
 */

fbml_precache_bunch *fbml_node_bunch_create(list<fbml_node *> *nodes)
{
  fbml_precache_bunch *b = (fbml_precache_bunch *) malloc(sizeof(fbml_precache_bunch));
  list<fbml_node *>::const_iterator it;
  b->nodes = (fbml_node **) calloc(nodes->size() + 1, sizeof(fbml_node *));

  int i = 0;
  for (it = nodes->begin(); it != nodes->end(); it++) {
    b->nodes[i] = *it;
    i++;
  }

  b->nodes[i] = NULL;
  b->tag = b->nodes[0]->tag_name;
  return b;
}

/**
 * Destructor that frees the array within the identified fbml_precache_bunch,
 * and then frees the fbml_precache_bunch itself.  Note that the fbml_precache_bunch
 * doesn't actually own the fbml_nodes address by the 'nodes' array, nor does
 * it own the C string stored in its tag field.  This is consistent with the
 * documentation of the fbml_node_bunch_create function above.
 *
 * @param bunch the address of the fbml_precache_bunch to be disposed of.
 */

void fbml_node_bunch_free(fbml_precache_bunch *bunch)
{
  free(bunch->nodes);
  free(bunch);
}

/**
 * Traverses the tree addressed by 'node' and builds a
 * node_map where each node type is mapped to a list
 * of all those fbml_nodes carrying that tag type.  The
 * C++ node_map is then marshalled to a pure C equivalent, which
 * takes the form of an array of fbml_precache_bunch *s.
 * Each one of those pointers addressed an fbml_precache_bunch
 * dedicated to one of the tag types known to have precaching needs.
 *
 * @param node the root of the full FBML tree being traversed.
 * @return a dynmaically allocated, NULL terminated array of
 *         fbml_precache_bunch pointers, where each pointer
 *         addresses a fbml_precache_bunch that maps a single
 *         tag type to all of those fbml_nodes of that type.
 */

fbml_precache_bunch **fbml_node_batch_precache(fbml_node *node)
{
  node_map p_map;
  fbml_precache_bunch **bunches;
  fbml_precache_bunch *b;

  fbml_node_batch_precache_rec(node, p_map);
  bunches = (fbml_precache_bunch **) calloc(p_map.size() + 1, sizeof( fbml_precache_bunch *));

  int i = 0;
  for (node_map::iterator it = p_map.begin(); it != p_map.end(); it++) {
    list<fbml_node *> * ls = it->second;
    b = fbml_node_bunch_create(ls);
    bunches[i] = b;
    i++;
  }

  bunches[i] = NULL;
  return bunches;
}
