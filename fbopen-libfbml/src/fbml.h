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

#ifndef __FBML_H__
#define __FBML_H__

#ifdef __cplusplus

#include <vector>
#include <string>
#include <map>
#include <list>
using namespace std;

struct contexts {
  string tag_name;
  vector<char > attr_rules;
  vector<vector<char> > tag_rules;
};

#endif

#ifdef __cplusplus
extern "C" {
#endif

  /**
   * Flags we use to mark certain nodes in an FBML tree.
   * These flags typically indicate that the HTML node
   * has special rendering needs.
   *
   *    SPECIAL_HTML: HTML nodes that have special rendering needs.
   *    PRECACHE: nodes for which some precaching functionality should
   *              be applied, because it'll ultimately speed up rendering.
   *    FBNODE: Nodes that are FBML extensions to the standard HTML node set.
   *    STYLE: Nodes that are style or style-like tags that might contain CSS.
   *    FB_FLAG_HAS_SPECIAL_ATTR: Nodes that have special attributes that require
   *                              special treatment.  This implies one or more
   *                              of its attributes are marked with the
   *                              FB_FLAG_ATTR_SPECIAL tag.
   */

#define FB_FLAG_SPECIAL_HTML 2
#define FB_FLAG_PRECACHE 4
#define FB_FLAG_FBNODE 8
#define FB_FLAG_STYLE 16
#define FB_FLAG_HAS_SPECIAL_ATTR 32

  /**
   * Flags used to mark individual attributes within an FBML node.
   * Attributes carrying one or more of these flags have special needs
   * come rendering time.  All the rest can be rendered as traditional
   * HTML attributes without any additional translation.
   *
   *    REWRITE: attribute that needs to be rewritten.
   *    STYLE: attribute that includes some CSS.
   *    SCRIPT: attribute that includes some Javascript, and therefore
   *            requires special treatment.
   *    SPECIAL: attributes that need special treatment during rendering.
   */

#define FB_FLAG_ATTR_REWRITE 2
#define FB_FLAG_ATTR_STYLE 4
#define FB_FLAG_ATTR_SCRIPT 8
#define FB_FLAG_ATTR_SPECIAL 32

  /**
   * Record type that bundles name/value attributes
   * for us.  Attributes can carry flags, which are used to help
   * optimize pre-rendering and rending of the FBML doc into
   * the equivalent HTML.
   */

  struct fbml_attribute {
    char *name;
    char *value;
    char flag;
  };

  /**
   * Record type that models a node in a FBML DOM tree.
   * Each node keeps track of its parent and its
   * children.  Most of the other fields are obvious if you recognize
   * that they help to model the node in an HTML-like DOM tree,
   * but a few are present for implementation and optimization
   * purposes.
   *
   * + flag: nodes can be flagged for special treatment for rendering
   *         purposes.  One such flag is FB_FLAG_PRECACHE, which
   *         tells us the node relies on resources that can be loaded
   *         prior to actual rendering.  Check the #defines above for
   *         the full list of node flags.
   *
   * + children_flagged the bitwise union of all of the flags marking
   *                    all of the children, grandchildren, great
   *                    grandchildren, etc.  This helps us prune a traversal
   *                    when we know there's no need to descend into the
   *                    subtrees hanging from this node.
   *
   * + attributes_flagged bitwise union of all of the attribute flags carried
   *                      by the collection of the node's attributes.
   *                      Likewise, it can be used to decide whether we
   *                      should bother examining a node's list of attributes
   *                      for a particular flag.
   */

  struct fbml_node {
    struct fbml_node *parent;
    unsigned short eHTMLTag; /* defined in nsHTMLTags.h && nsHTMLTagList.h */
    char *tag_name;
    char *text;
    unsigned short children_count;
    unsigned short children_alloc;
    struct fbml_node **children;
    unsigned short attribute_count;
    unsigned short attribute_alloc;
    struct fbml_attribute **attributes;
    char flag;
    char children_flagged;
    char attributes_flagged; // union of all bitflags carried by node's attrs
    int line_num;
  };

  /**
   * Packages all of the material needed when the FBML parser recognizes
   * the presence of CSS in the FBML tree and needs to sanitize it.
   */

  struct fbml_css_sanitizer {
    char *container_selector;
    char *identifier_prefix;
    char *(*pfunc_url_translator)(char *, void *);
    void *url_translate_data;
  };

  /**
   * Packages all of the material needed when the FBML parser digests
   * some Javascript and needs to sanitize it.
   */

  struct fbml_js_sanitizer {
    int pretty;
    char *identifier_prefix;
    char *this_replacement;
    char *arguments_replacement;
    char **banned_properties;
    char *banned_property_replacement;
    char *array_element_format;
    char *(*pfunc_eh_translator)(char *, void *);
    void *eh_translate_data;
  };

  /**
   * Packages the information needed to rewrite any given
   * attribute in an FBML tree.
   */

  struct fbml_attr_rewriter {
    char *(*pfunc_rewriter)(char *, char *, char *, void *);
    void *rewrite_data;
  };

  /**
   * Packages all of the information needed to render an
   * arbitrary node in an FBML tree.  The pfunc_renderer
   * field is the C callback function that knows how to
   * render all FBML and certain HTML nodes.  When the
   * callback is fired on behalf of an FBML node, the
   * fb_node_data field is passed in as client data, because
   * it knows how to callback into PHP to render FBML nodes.
   * When the callback is fired on behalf of a special HTML
   * node (or one that has special attributes), the html_node_data
   * field is passed as client data to pfunc_renderer, because
   * it knows how to render those HTML tags that have been
   * registered as special.  The node field is the root of the
   * FBML tree being rendered.
   */

  struct fbml_node_renderer {
    void *fb_node_data;
    void *html_node_data;
    char *(*pfunc_renderer)(struct fbml_node *, void *);
    struct fbml_node *node;
  };

  /**
   * Utility struct used to map functionality over all of those
   * nodes in an FBML tree marked with FB_FLAG_PRECACHE.  The
   * addresses of such nodes are always passed as the first argument
   * to the 'pfunc_precacaher' function, and the 'precache_node_data'
   * is client data that's always passed as the second argument to
   * the mapping function.  This client data is the thing that
   * knows which precache functionality to apply to each node type.
   */

  struct fbml_node_precacher {
    void *precache_node_data;
    char *(*pfunc_precacher)(struct fbml_node *, void *);
  };

  /**
   * Convenience record built to keep track of all of the attribute
   * strings that call under a particular attribute flag category.
   * For instance, the flag field might be FB_FLAG_ATTR_SCRIPT, in which
   * case the attrs field would be a NULL terminated array of C strings,
   * where each C string is an attribute carrying that particular flag.
   */

  struct fbml_flaggable_attrs {
    char flag;
    char **attrs;
  };

  /**
   * Similar to the fbml_flaggable_attrs record, except this exists
   * on behalf of FBML/HTML tags (like img' and 'fb:name'), not attributes.
   */

  struct fbml_flaggable_tags {
    char flag;
    char **tags;
  };

  /**
   * Record type designed to model an FBML schema.  In a nutshell, the
   * schema maintains a black list of all those tags and those attrbutes
   * that can not exist in the subtree rooted at the node of tag type
   * ancestor_tag.
   */

  struct fbml_schema {
    char *ancestor_tag;
    char **illegal_children; /* illegal descendent of the specified tag */
    char **illegal_children_attr; /*illegal attributes in descendents of the specified tag */
  };

  /**
   * Record type that manages a full contextual schema type.
   */

  struct fbml_context_schema {
    char *context_tag;
    struct fbml_schema **schema;
  };

  /**
   * Exposed struct keyed on an HTML tag (such as "a", "img", etc.).
   * The 'nodes' field is a NULL-terminated C array of pointers to fbml_node records.
   * These records are normally constructed from pair<unsigned short, list<fmbl_node *> *>, but
   * C++ template pairs don't marshal well over the PHP/C boundary, which is why
   * we have this type as well.
   *
   * The purpose of the record?  To assemble a list of all those nodes which have
   * some precaching need, so that we can push the precache_bunch over the PHP-C
   * boundary just once to apply precache function (as opposed to the situation
   * where you cross that boundary once for every single node.)
   */

  struct fbml_precache_bunch {
    char *tag;
    struct fbml_node **nodes;
  };

  /**
   * Convenience function that constructs a new fbml_node with the specified
   * tag type and tag flag, and appends it to the end of the list of children
   * maintained by the parent.
   *
   * @param parent the address of the fbml_node getting a brand new child.
   * @param eHTMLTag the constant identifying the type of node the new child
   *                 should be.
   * @param flag the flag that the new node should carry.
   * @param line_num the line within the original source document where the node
   *                 being created can be found.
   * @return the address of the new fbml_node child.
   */

  struct fbml_node *fbml_node_add_child(struct fbml_node *parent, short eHTMLTag,
					char flag, int line_num);

  /**
   * Marks the specified node with the specified flag.
   *
   * @param node the address of the fbml_node being flagged.
   * @param flag the flag in question (FB_NODE_PRECACHE, for instance)
   */

  void fbml_node_add_flags(struct fbml_node *node, char flag);

  /**
   * Adds a new attribute to the node identified by 'parent'.
   * A deep copy of 'name' is always made, but a deep copy of
   * 'value' is made if and only if 'copy_value' is set to anything
   * other than 0.
   *
   * @param parent the address of the node getting a new attribute.
   * @param name the name of the new attribute.
   * @param value the value of the new attribute.
   * @param flag the flag that this new attribute should be marked with.
   *             0 corresponds to no flag, but other examples are
   *             FB_FLAG_ATTR_REWRITE, FB_FLAG_ATTR_SCRIPT, etc.
   * @param copy_value zero if only a shallow copy of the supplied value should
   *                   be made, and anything non-zero otherwise.
   */

  void fbml_node_add_attribute(struct fbml_node *parent, char *name,
                               char *value, char flag, int copy_value);

  /**
   * Retrieves an alias to whatever value is attached to the named
   * attribute within the specified node.  The name comparison is
   * case-insensitive.  Note that you should not free the return
   * value.
   *
   * @param node the fbml_node whose attributes should be searched.
   * @param name the name of the attribute we're interested in.
   * @return an alias to the value associated with the attribute identified
   *         by name, or NULL if the named attribute doesn't exist.
   */

  char *fbml_node_get_attribute(struct fbml_node *node, char *name);

  /**
   * Accepts the named color attribute and places a dynamically allocated,
   * canonicalized version of that string in the space addressed by 'result'.
   * The C string placed in the spaced addressed by result *should* be
   * freed.
   *
   * @name an attribute value, presumably a color of the form "white", or "#FC0044".
   * @result the address of the char * where a canonicalized form of the incoming
   *         string should be placed.
   * @return 1 if successful, and -1 if not.  If -1 is returned, then you needn't
   *         free *result.
   */

  int fbml_node_attr_to_color(char *name, char **result);

  /**
   * Accepts the string value and places an integer in the space addressed
   * by result.  A 1 is placed if name addresses either some case-insensitive
   * variation of "true" or "yes", or if the initial part of the string is
   * something numeric and non-zero.  If name is some case-insensitive variation
   * of "no" or "false", or the initial part is numeric but all zeroes, then 0
   * is placed instead.  If the string isn't something that can be easily converted to
   * a bool, then the space addressed by result is ignored and the function
   * returns -1.
   *
   * @param name the string being converted to a Boolean value.
   * @param result the address where either a 0 or 1 should be placed if the string
   *               can be easily interpreted as a boolean value.
   * @return 1 if successful, and -1 if not.  If -1 is returned, then *result
   *         is left unmodified.
   */

  int fbml_node_attr_to_bool(char *name, int *result);

  /**
   * Generates the serialization of full FBML tree addressed by tree.  This
   * particular serialization doesn't do any translation or rendering, so the
   * string you get back is logically identical to the document from which the
   * tree was built in the first place.
   *
   * @param tree the address of the root node of the entire tree being
   *        serialized.
   * @return a dynamically allocated C string storing the full serialization
   *         of the entire tree.
   */

  char *fbml_node_print(struct fbml_node *tree);

  /**
   * Similar to fbml_node_print, except this version does node-by-node rendering
   * according to the logic encoded within the fbml_node_renderer addressed by
   * 'renderer'.  That means <fb:name uid="214707"> gets published as "Jerry Cain"
   * instead of "<fb:name uid=\"214707\">".
   *
   * @param node the address of the root of the full FBML tree being rendered.
   * @param skip_schema_checking 0 if the the schema should be enforced while
   *                             rendering, and non-zero otherwise.
   * @param renderer the address of the fbml_node_rendered that controls all of
   *                 the FBML compilation/translation logic.
   * @return the dynamically allocated C string storing the full rendering of the
   *         FBML tree rooted at 'node'.
   */

  char *fbml_node_render_children(struct fbml_node *node, int skip_schema_checking,
                                  struct fbml_node_renderer *renderer);

  /**
   * Traverses the full FBML tree rooted at 'node', and returns the full serialization
   * of all of the CSS contained in the tree.  It works much like fbml_node_print,
   * except that this time only CSS gets included.
   *
   * @param the address of the root node of the FBML tree being traversed.
   * @return a dynamically allocated C string containing all of the CSS residing
   *         in the identified FBML tree.
   */

  char *fbml_node_collect_css(struct fbml_node *node);

  /**
   * Applies precaching functionality to all those nodes in the tree carrying the
   * FB_NODE_PRECACHE flag.  The precaching function that gets applied is managed
   * by the fbml_node_precacher addressed by precacher.
   *
   * @param node the root of the tree to be traversed.
   * @param precacher the address of the fbml_node_precacher record that carries
   *                  the precache functionality that should be applied to all nodes
   *                  carrying the FB_NODE_PRECACHE flag.
   */

  void fbml_node_precache(struct fbml_node *node, struct fbml_node_precacher *precacher);

  /**
   * Disposes of all of the resources held by the FBML tree rooted at 'tree'.
   *
   * @param tree the address of the root node of the tree being destroyed.
   */

  void fbml_node_free(struct fbml_node *tree);

  /**
   * Returns 1 if and only if the tag list has always been constructed,
   * and 0 otherwise.
   *
   * @return 0 if the tag list hasn't been expanded yet, and 1 otherwise.
   */

  int fbml_tag_list_expanded();

  /**
   * Configures the FBML engine to be aware of certain tags, attributes, and schemas.
   *
   * @param new_tags a NULL terminated array of C strings, where each string is
   *                 some new tag type that should be recognized as a legitimate
   *                 HTML tag type.
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
   * @param fbml_context_schema an NULL_temrinated array of all of the different schemas relevant to
   *                            parsing FBML documents.
   */

  void fbml_expand_tag_list(char **new_tags,
                            char **new_attrs,
                            struct fbml_flaggable_tags **flaggable_tags,
                            struct fbml_flaggable_attrs **flaggable_attrs,
                            struct fbml_context_schema **schemas);

  /**
   * Parses a C string of FBML, constructs an FBML parse tree, plants the
   * root of that parse tree in the space addressed by 'tree', and places
   * the accumulation of any error messages in the space addressed by 'error'.
   * Both the 'tree' and 'error' strings need to be properly disposed of
   * once the client is done with them.
   *
   * 'body_only', 'preserve_comments', and 'skip_schema_checking' are all user
   * supplied flags.  'css_sanitizer', 'js_sanitizer', 'attr_rewriter' identify
   * the pipeline of components that scrub the FBML text.
   *
   * @param fbml a C string containing some FBML text.
   * @param body_only true if the supplied FBML is a fragment representing
   *                  the <body> portion of a full FBML document.
   * @param preserve_comment true if the parse tree should retain the comments and
   *                         allow them to contribute to the FBML parse tree, and
   *                         false if they should be stripped out.
   * @param css_sanitizer the address of the fully initialized fbml_css_sanitizer
   *                      that will transform all of the CSS enough to minimize
   *                      the chances that the applications CSS will impact Facebook's
   *                      CSS.  The CSS sanitizer really dictactes what these transformations
   *                      are.
   * @param js_sanitizer the address of the fully initialized js_santizer designed to
   *                     transform any and all Javascript just enough that it won't
   *                     cause any problems when loaded and executed.  The JS sanitizer
   *                     decides what these transformations are.
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

  int fbml_parse(char *fbml, int body_only, int preserve_comment, int internal_mode,
                 struct fbml_css_sanitizer *css_sanitizer,
                 struct fbml_js_sanitizer *js_sanitizer,
                 struct fbml_attr_rewriter *attr_rewriter,
                 struct fbml_flaggable_tags **flaggable_tags,
                 struct fbml_flaggable_attrs **flaggable_attrs,
                 struct fbml_node **tree, char **error);

  extern int fbml_html_userdefined_tag;
  extern int fbml_html_style_tag;
  extern int fbml_html_comment_tag;

  /**
   * Returns the C string form of an HTML tag type.  The string being
   * returned is dynamically allocated, so it needs to be freed by the
   * caller.
   *
   * @param the constant identifying the HTML tag type of interest.
   * @return a C string spelling out the tag type identified by the
   *         incoming constant parameter, or the empty string if the
   *         incoming tag constant wasn't recognized.  In all cases,
   *         the return value is dynamically allocated and needs to be
   *         freed.
   */

  char *fbml_lookup_tag_name(unsigned short eHTMLTag);

  /**
   * Returns the constant associated with the incoming string.
   *
   * @param name the string from of some HTML tag type.
   * @return the constant associated with the same tag type.
   */

  unsigned short fbml_lookup_tag_by_name(char *name);

  /**
   * Accepts a string of CSS and places the sanitized version of it
   * in the space addressed by 'sanitized_css'.  The accumulation
   * of any error messages is planted in the space address by
   * 'error'.  Both the sanitized CSS string and the error messages string
   * are dynamically allocated and need to be freed by the client.
   *
   * @param css the CSS string to sanitize.
   * @param declaration_only a Boolean value: true if the CSS was supplied
   *                         as a style attribute value (as in style="width: 60px;'),
   *                         and false if the CSS was embedded inside style tags (as
   *                         in <style> .myStyle { width: 60px; } </style>.
   * @param line_number the line number where the CSS being sanitized resides
   *                    within the original source file.
   * @param css_sanitizer the address of the fbml_css_sanitizer record that guides the
   *                      CSS sanitization.
   * @param sanitized_css the address identifying the space where the sanitized CSS string
   *                      should be placed.
   * @param error the address where an error string should be placed.  The error string
   *              is dynamically allocated, and stores the accumulation of all of the
   *              error messages that presented themselves during the parse.
   * @return value is always 0.
   */

  int fbml_sanitize_css(char *css, int declaration_only, int line_number,
                        struct fbml_css_sanitizer *css_sanitizer,
                        char **sanitized_css, char **error);

  /**
   * Configures the fbml_js_sanitizer address by 'js_sanitizer'.
   *
   * @param js_sanitizer the fbml_js_sanitizer being configured.
   */

  void fbml_js_sanitizer_init(struct fbml_js_sanitizer *js_sanitizer);

  /**
   * Accepts a string of Javascript and places the sanitized version of it
   * in the space addressed by 'sanitized_js'.  The accumulation of any error
   * messages that come up is placed in the space address by 'error'.
   * Both the 'sanitized_js' and 'error' strings are dynamically allocated
   * and need to be freed by the caller.
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
                       struct fbml_js_sanitizer *js_sanitizer,
                       char **sanitized_js, char **error);

  /**
   * Traverses the entire FBML tree rooted at 'node' and compiles a list
   * of all those fbml_nodes that have precaching needs.  The return
   * value is a NULL_terminated, dynamically allocated array of
   * fbml_precache_bunch *s, where each one addresses a dynamically allocated
   * fbml_precache_bunch (one for each tag type.)
   *
   * @param node the root of the FBML tree to be traversed.
   * @return a dynamically allocated, NULL-terminated array of fbml_precache_bunch
   *         records, where each record maps a tag type to the set of nodes of that
   *         type.  You should only expect a tag type to be included if it was
   *         registered as a type that requires come precaching.
   */

  struct fbml_precache_bunch **fbml_node_batch_precache(struct fbml_node *node);

  /**
   * Disposes of the fbml_precache_bunch addressed by 'bunch'.
   *
   * @param bunch the address of the fbml_precache_bunch that should be
   *              fully disposed of.
   */

  void fbml_node_bunch_free(struct fbml_precache_bunch *bunch);

#ifdef __cplusplus
} /* extern "C" */
#endif


#endif /* __FBML_H__ */
