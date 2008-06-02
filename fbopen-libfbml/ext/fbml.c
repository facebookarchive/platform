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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_fbml.h"
#include "../src/fbml.h"
#include <string.h>

#define TREE_RESOURCE_NAME "Tree Descriptor"
#define NODE_RESOURCE_NAME "Node Descriptor"
#define PHP_FBML_VERSION "1.2.0"

#define MAXTAGSIZE 512
#define FBML_COMMENT_NODE 1
#define FBML_PLAINTEXT_NODE 2
#define FBML_HTML_NODE 3
#define FBML_MACRO_NODE 4
#define FBML_STYLE_NODE 5

/**
 * PHP resource type designed to wrap around an fbml_node * so
 * we can pass fbml_node *'s back and forth across the PHP/C
 * boundary.  The root of the full tree containing the node
 * is also included.
 */

struct node_res {
  struct fbml_node *node;
  zval *tree;
};

/**
 * Integer identifiers associated with the two resource types
 * we need for our implementation.  The node_descriptor is the
 * id of the resource that wraps the above node_res type, and
 * the tree_desrciptor is the id of the resource type that
 * wraps standalone fbml_node *s.
 */

static int node_descriptor;
static int tree_descriptor;

/**
 * Each and every one of these functions needs
 * to be registered as a PHP userspace function.
 * The NULLs occupy the slot where we can optimally
 * pass in type hinting.  We don't do any of
 * that at the moment, though, so all we're announcing
 * are the names of the functions and that's all.
 */

zend_function_entry fbml_functions[] = {

  // libfbml 1.2.0 naming scheme
  PHP_FE(fbml_is_parser_configured, NULL)
  PHP_FE(fbml_configure_parser, NULL)
  PHP_FE(fbml_parse, NULL)
  PHP_FE(fbml_sanitize_css, NULL)
  PHP_FE(fbml_sanitize_js, NULL)
  PHP_FE(fbml_get_tag_name, NULL)
  PHP_FE(fbml_get_children, NULL)
  PHP_FE(fbml_get_children_count, NULL)
  PHP_FE(fbml_get_children_by_name, NULL)
  PHP_FE(fbml_get_attributes, NULL)
  PHP_FE(fbml_get_attribute, NULL)
  PHP_FE(fbml_attr_to_bool, NULL)
  PHP_FE(fbml_attr_to_color, NULL)
  PHP_FE(fbml_get_line_number, NULL)
  PHP_FE(fbml_get_text, NULL)
  PHP_FE(fbml_precache, NULL)
  PHP_FE(fbml_batch_precache, NULL)
  PHP_FE(fbml_render_children, NULL)
  PHP_FE(fbml_flatten, NULL)

  // libfbml 1.1.x naming scheme, to be deprecated
  PHP_FE(fbml_tag_list_expanded_11, NULL)
  PHP_FE(fbml_complex_expand_tag_list_11, NULL)
  PHP_FE(fbml_sanitize_css_11, NULL)
  PHP_FE(fbml_sanitize_js_11, NULL)
  PHP_FE(fbml_parse_opaque_11, NULL)

  //node getters
  PHP_FE(fbml_get_tag_name_11, NULL)
  PHP_FE(fbml_get_children_11, NULL)
  PHP_FE(fbml_get_children_count_11, NULL)
  PHP_FE(fbml_get_attributes_11, NULL)
  PHP_FE(fbml_get_children_by_name_11, NULL)
  PHP_FE(fbml_attr_to_bool_11, NULL)
  PHP_FE(fbml_attr_to_color_11, NULL)

  PHP_FE(fbml_get_attribute_11, NULL)
  PHP_FE(fbml_get_text_11, NULL)

  //traversal functions
  PHP_FE(fbml_precache_11, NULL)
  PHP_FE(fbml_batch_precache_11, NULL)
  PHP_FE(fbml_render_children_11, NULL)
  PHP_FE(fbml_flatten_11, NULL)
  {NULL, NULL, NULL} // Must be the last line in fbml_functions[]
};

/**
 * PHP boilerplate in place to identify initialization routines,
 * shutdown routines, and the set of functions exported by
 * the module.  This is standard boilerplate included in the
 * implementation of *any* php module.
 */

zend_module_entry fbml_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
  STANDARD_MODULE_HEADER,
#endif
  "fbml",
  fbml_functions,
  PHP_MINIT(fbml),
  PHP_MSHUTDOWN(fbml),
  PHP_RINIT(fbml),
  PHP_RSHUTDOWN(fbml),
  PHP_MINFO(fbml),
#if ZEND_MODULE_API_NO >= 20010901
  PHP_FBML_VERSION,
#endif
  STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(fbml);

/**
 * Disposes of the memory associated with the supplied
 * PHP resource, known to surround the address of a node_res
 * struct.  Note that the fbml_node * within the node_res
 * is left alone.  All this is doing to decrasing the reference
 * count held by the root of the over tree resource.  It's only
 * when the reference count of the root node falls to zero that
 * the tree_dtor (defined below) gets called.
 *
 * @param rsrc the resource being disposed of.
 */

static void node_dtor(zend_rsrc_list_entry *rsrc TSRMLS_DC)
{
  struct node_res *node_res = (struct node_res *) rsrc->ptr;
  zval_ptr_dtor(&node_res->tree);
  efree(node_res);
}

/**
 * Recursively disposes of the entire FBML tree hanging from the
 * supplied PHP resource, which is understood to surround the address
 * of the root fbml_node of an FBML tree.
 *
 * @param rsrc the resource being disposed of.
 */

static void tree_dtor(zend_rsrc_list_entry *rsrc TSRMLS_DC)
{
  struct fbml_node *root = (struct fbml_node *) rsrc->ptr;
  fbml_node_free(root);
}

/**
 * Registers the two PHP resource types needed to effectively
 * pass complex data structures back and forth across the
 * PHP boundary.  Note that node_dtor and tree_dtor are supplied
 * as arguments; those are the functions that get invoked to clean
 * up memory as the reference count of a PHP resource falls to 0.
 */

PHP_MINIT_FUNCTION(fbml)
{
  node_descriptor = zend_register_list_destructors_ex(node_dtor, NULL,
						      NODE_RESOURCE_NAME,
						      module_number);
  tree_descriptor = zend_register_list_destructors_ex(tree_dtor, NULL,
						      TREE_RESOURCE_NAME,
						      module_number);
  return SUCCESS;
}

/**
 * Placeholder implementation that succeeds by doing nothing at all.
 *
 * @return SUCCESS, always.
 */

PHP_MSHUTDOWN_FUNCTION(fbml)
{
  return SUCCESS;
}

/**
 * Returns SUCCESS without doing anything else.  This block of code is
 * executed with each any every request.  There's nothing special to
 * so at this level, which explains the trivial implementation.
 *
 * @return SUCCESS, always.
 */

PHP_RINIT_FUNCTION(fbml)
{
  return SUCCESS;
}

/**
 * Returns SUCCESS without doing anything else.  This block of code is
 * executes as each request closes down.  Since nothing special happens
 * when a request is made, nothing special has to happen when the request
 * has been met.
 *
 * @return SUCCESS, always.
 */

PHP_RSHUTDOWN_FUNCTION(fbml)
{
  return SUCCESS;
}

/**
 * Trivially prints information confirming that FBML support has been turned on.
 */

PHP_MINFO_FUNCTION(fbml)
{
  php_info_print_table_start();
  php_info_print_table_header(3, "fbml support", "enabled", PHP_FBML_VERSION);
  php_info_print_table_end();
}

/**
 * Dynamically allocates a new node_res resource around the
 * specified node/tree pair so that PHP knows about it, and then
 * registers it as a resource with the PHP runtime.
 */

static zval *register_new_node(struct fbml_node *node, zval *tree)
{
  struct node_res *new_node_res = emalloc(sizeof(struct node_res));

  ZVAL_ADDREF(tree);
  new_node_res->tree = tree;
  new_node_res->node = node;

  zval *node_zval;
  MAKE_STD_ZVAL(node_zval);
  ZEND_REGISTER_RESOURCE(node_zval, new_node_res, node_descriptor);

  return node_zval;
}

/**
 * Record packaging several pieces of data needed to map a PHP userspace
 * function over an FBML tree.
 *
 * + user_func_name: the name of the PHP function which should be
 *                   called.
 * + user_data: the data that should be passed to the PHP function
 *              identified by the user_func_name field.
 * + fallback: stores the result that should be returned when the
 *   the call to the PHP function in user_func_name fails.
 */

struct callback_parameter {
  char *user_func_name;
  zval *user_data;
  char *fallback;
};

/**
 * Manages the call to some two-argument function back in PHP space.  If the weren't
 * any language barriers, this function would just do this:
 *
 *   return strdup(data->user_func_name(data->user_data, input));
 *
 * Effectively, that's what happens, but most of the implementation here needs to manage the
 * communication between C and PHP.
 *
 * @param input the C string for which an equivalent PHP string should be constructed so
 *              it can be passed as the second argument to the PHP callback function.
 * @param data the address of a callback_parameter record which packages the name of the
 *             PHP callback function, the user-supplied auxilary data that should be passed
 *             as the first argument to every single invocation of the callback, and a
 *             default return value that should be used if the callback function fails or doesn't
 *             provide one.
 * @return a dynamically allocaed, C string equivalent of the PHP string that's returned by the
 *         callback function, or a C string clone of the fallback value should the callback function
 *         fail for whatever reason.
 */

static char *callback_user_function(char *input, void *data)
{
  zval *fname = NULL;
  zval *params[2];
  zval *fret = NULL;
  zval *zinput = NULL;
  char *ret = NULL;
  struct callback_parameter *param = (struct callback_parameter *) data;

  TSRMLS_FETCH();

  MAKE_STD_ZVAL(zinput);
  ZVAL_STRING(zinput, input, 1);

  MAKE_STD_ZVAL(fname);
  ZVAL_STRING(fname, param->user_func_name, 1);

  params[0] = param->user_data;
  params[1] = zinput;

  MAKE_STD_ZVAL(fret);
  if (call_user_function(EG(function_table), NULL, fname, fret,
                         2, params TSRMLS_CC) == SUCCESS && fret) {
    convert_to_string(fret);
    ret = Z_STRVAL_P(fret);
    if ((ret != NULL) && (*ret != '\0')) {
      ret = strndup(ret, Z_STRLEN_P(fret));
    }
    zval_ptr_dtor(&fret);
  }

  zval_ptr_dtor(&fname);
  zval_ptr_dtor(&zinput);

  return ((ret != NULL) && (*ret != '\0')) ? ret : strdup(param->fallback);
}

/**
 * Manages the call to some four-argument function back in PHP space.  If the weren't
 * any language barriers, this function would just do this:
 *
 *   return strdup(data->user_func_name(data->user_data, tag, attr, data));
 *
 * Effectively, that's what happens, but most of the implementation here needs to manage the
 * communication between C and PHP.
 *
 * @param tag the tag of the node for which the callback is being invoked.  Of course,
 *            the tag is a C string, so a PHP string equivalent needs to be
 *            constructed and passed as the second argument to the PHP callback
 *            function.
 * @param attr the name of the attribute for which the callback function is being invoked.
 *             As with the tag variables, 'attr' comes in as a C string, so a PHP string
 *             equivalent needs to be created so it can be passed as the third argument
 *             to the PHP callback function.
 * @param val the value associated with the specified attribute, which is also passed along
 *            as the fourth and final argument to the PHP callback function.  Again, it's
 *            a C string, so we need to create a PHP string out of it so that the PHP version
 *            can be passed to the PHP callback function.
 * @param data the address of a callback_parameter record, which bundles the name of the
 *             four-argument PHP callback function, the user-supplied client data that
 *             should be passed as the first argument to every invocation of that callback
 *             function, and a fallback value, which is the C string value that should be
 *             cloned and returned should the callback function fail for some reason.
 * @return a dynamically allocated, C string version of whatever PHP string is returned
 *         by the callback function embedded within the 'data' struct, or a C string clone
 *         of data->fallback should the callback function fail to provide one.
 */

static char *callback_attr_function(char *tag, char *attr, char *val, void *data)
{
  zval *fname;
  zval *params[4];
  zval *fret;
  zval *ztag, *zattr, *zvalue;
  char *ret;
  struct callback_parameter *param;

  ret = NULL;
  param = (struct callback_parameter *) data;

  TSRMLS_FETCH();

  MAKE_STD_ZVAL(ztag);
  ZVAL_STRING(ztag, tag, 1);

  MAKE_STD_ZVAL(zattr);
  ZVAL_STRING(zattr, attr, 1);

  MAKE_STD_ZVAL(zvalue);
  ZVAL_STRING(zvalue, val, 1);

  MAKE_STD_ZVAL(fname);
  ZVAL_STRING(fname, param->user_func_name, 1);

  params[0] = param->user_data;
  params[1] = ztag;
  params[2] = zattr;
  params[3] = zvalue;

  MAKE_STD_ZVAL(fret);
  if (call_user_function(EG(function_table), NULL, fname, fret,
                         4, params TSRMLS_CC) == SUCCESS && fret) {
    convert_to_string(fret);
    ret = Z_STRVAL_P(fret);
    if (ret && *ret) {
      ret = strndup(ret, Z_STRLEN_P(fret));
    }else {
      ret = strdup("");
    }
    zval_ptr_dtor(&fret);
  }

  zval_ptr_dtor(&fname);
  zval_ptr_dtor(&ztag);
  zval_ptr_dtor(&zattr);
  zval_ptr_dtor(&zvalue);
  return ret;
}

/**
 * Record packaging all of the information necessary to map
 * over an FBML tree and apply a PHP userspace function
 * to the nodes of that tree.
 *
 * + user_func_name the name of the PHP function acting as the callback.
 * + user_data auxilary data that should be passed verbatim as the first
 *             argument to the invocation of the function held by
 *             'user_func_name'.
 * + tree the FBML node which the function held by 'user_func_name' should
 *        be applied to.
 * + fallback the string value that should be returned if the application
 *            of the PHP function named by 'user_func_name' fails.
 */

struct node_callback_parameter {
  char *user_func_name;
  zval *user_data;
  zval *tree;
  char *fallback;
};

/**
 * Manages the call to a function defined in PHP userspace against
 * an fbml_node * and some user-supplied client data.  Fundamentally,
 * this function does this:
 *
 *    return strdup(data->user_func_name(data->user_data, node)).
 *
 * However, it's significantly complicated by the fact that we're calling
 * back into PHP space.  That's what most of the implementation is really
 * managing.
 *
 * @param node the address of the fbml_node to which some two-argument
 *             PHP function is being applied.  The name of this PHP function
 *             is stored in the struct addressed by 'data'.
 * @param data the address of a node_callback_parameter record, which contains
 *             the name of the PHP function to be invoked, the client data
 *             which should be supplied as the first argument to the callback,
 *             and the string that should be returned should the callback
 *             function fail to produce one for some reason.
 * @return the C string equivalent of the PHP string returned by the callback
 *         function, or the fallback value should the callback function fail
 *         to produce one for whatever reason.  The C string is dynamically
 *         allocated and should eventually be freed.
 */

static char *callback_node_function(struct fbml_node *node, void *data)
{
  zval *fname;
  zval *params[2];
  zval *fret;
  char *ret = NULL;
  struct node_callback_parameter *param = (struct node_callback_parameter *) data;
  zval *new_node = register_new_node(node, param->tree);

  TSRMLS_FETCH();

  MAKE_STD_ZVAL(fname);
  ZVAL_STRING(fname, param->user_func_name, 1); // PHP string with function name

  params[0] = param->user_data;
  params[1] = new_node;

  MAKE_STD_ZVAL(fret);
  if (call_user_function(EG(function_table), NULL, fname, fret,
                         2, params TSRMLS_CC) == SUCCESS && fret) {
    convert_to_string(fret);
    ret = Z_STRVAL_P(fret);
    if ((ret != NULL) && (*ret != '\0')) {
      ret = strdup(ret); //  Z_STRLEN_P(fret));
    } else {
      ret = strdup(param->fallback);
      // ret = strdup("");
    }
    zval_ptr_dtor(&fret);
  }

  zval_ptr_dtor(&fname);
  zval_ptr_dtor(&new_node);
  return ret;
}

/**
 * Convenience function that extracts information from 'param' and
 * the 'sanitizer' hash, and initializes the fbml_css_sanitizer addressed
 * by 'css_sanitizer'.
 *
 * @param sanitizer an associative array carrying information
 *                  about how the fbml_css_sanitizer should
 *                  be constructed.  The implementation looks
 *                  for four keys, all of them optional, named
 *                  "func", "data", "prefix", and "id_selector".
 * @param param additional client data that should be passed
 *              along as client data to whatever turns out to be
 *              the callback function.
 * @param css_sanitizer the address of the fbml_css_sanitizer struct
 *                      that should be populated.
 * @return NULL if the incoming sanitizer is logically NULL.  Otherwise,
 *              the value that comes in as the third argument is returned
 *              as is.
 */

static struct fbml_css_sanitizer *
get_css_sanitizer(zval *sanitizer,
                  struct callback_parameter *param,
                  struct fbml_css_sanitizer *css_sanitizer)
{
  HashTable *ht;
  void *data;

  TSRMLS_FETCH();

  if ((sanitizer == NULL) || ZVAL_IS_NULL(sanitizer)) return NULL;
  ht = HASH_OF(sanitizer);
  if (!ht) return NULL;

  if (zend_hash_find(ht, "func", 5, &data) == SUCCESS) {
    param->user_func_name = Z_STRVAL_PP((zval**)data);
  } else {
    param->user_func_name = "fbml_css_translate_url";
  }

  if (zend_hash_find(ht, "data", 5, &data) == SUCCESS) {
    param->user_data = *(zval**)data;
  } else {
    param->user_data = NULL;
  }

  if (zend_hash_find(ht, "prefix", 7, &data) == SUCCESS) {
    css_sanitizer->container_selector = Z_STRVAL_PP((zval**)data);
  } else {
    css_sanitizer->container_selector = NULL;
  }

  if (zend_hash_find(ht, "id_selector", 12, &data) == SUCCESS) {
    css_sanitizer->identifier_prefix = Z_STRVAL_PP((zval**)data);
  } else {
    css_sanitizer->identifier_prefix = NULL;
  }

  param->fallback = "\"\"";
  css_sanitizer->pfunc_url_translator = callback_user_function;
  css_sanitizer->url_translate_data = param;
  return css_sanitizer;
}

/**
 * Convenience function designed to initialize the fbml_js_sanitizer
 * addressed by 'js_sanitizer' using the information addressed by
 * 'sanitizer' and 'param'.  The value of 'param' is copied into the
 * 'eh_translate_data' field of the fbml_js_sanitizer addressed by
 * 'js_sanitizer'.  The remaining fields of the fbml_js_sanitizer are
 * filled in with default values, unless the PHP array addressed by
 * 'sanitizer' supplied other data (under keys 'func', 'data', 'prefix',
 * and/or 'that'.)
 *
 * @param sanitizer the PHP assoc array containing those values that
 *                  should displace the default values used to build
 *                  up the fbml_js_sanitizer addressed by 'js_sanitizer'.
 * @param param the value that should be planted into the 'eh_translate_data'
 *              of the fbml_js_sanitizer addressed by 'js_sanitizer'.
 * @param js_sanitizer the address of the fbml_js_sanitizer that should be
 *                     updated with defaults and information drawn from
 *                     'sanitizer' and 'param'.
 * @return NULL if the sanitizer is either missing or empty.  Otherwise,
 *              the address passed in as 'js_sanitizer' is returned.
 */

static struct fbml_js_sanitizer *
get_js_sanitizer(zval *sanitizer,
                 struct callback_parameter *param,
                 struct fbml_js_sanitizer *js_sanitizer)
{
  HashTable *ht;
  void *data;

  TSRMLS_FETCH();

  if ((sanitizer == NULL) || ZVAL_IS_NULL(sanitizer)) return NULL;
  ht = HASH_OF(sanitizer);
  if (!ht) return NULL;

  if (zend_hash_find(ht, "func", 5, &data) == SUCCESS) {
    param->user_func_name = Z_STRVAL_PP((zval**)data);
  } else {
    param->user_func_name = "fbml_js_translate_event_handler";
  }

  if (zend_hash_find(ht, "data", 5, &data) == SUCCESS) {
    param->user_data = *(zval**)data;
  } else {
    param->user_data = NULL;
  }

  if (zend_hash_find(ht, "prefix", 7, &data) == SUCCESS) {
    js_sanitizer->identifier_prefix = Z_STRVAL_PP((zval**)data);
  } else {
    js_sanitizer->identifier_prefix = NULL;
  }

  if (zend_hash_find(ht, "that", 5, &data) == SUCCESS) {
    js_sanitizer->this_replacement = Z_STRVAL_PP((zval**)data);
  } else {
    js_sanitizer->this_replacement = NULL;
  }

  param->fallback = "";
  js_sanitizer->pfunc_eh_translator = callback_user_function;
  js_sanitizer->eh_translate_data = param;
  return js_sanitizer;
}

/**
 * Populates the fbml_attr_rewriter addressed by 'attr_rewriter' using
 * information accessible via 'rewriter' and 'param'.  The value of
 * 'param' is always written to the 'rewrite_data' field of the fbml_attr_rewriter
 * addressed by 'attr_rewriter'.  The other fields of the fbml_attr_rewriter
 * addressed by 'attr_rewriter' are populated with defaults, unless the
 * PHP associative array addressed by 'rewriter' identifies values that
 * should displace some or all of those defaults.  The implementation here
 * is consistent with the implementations of get_css_sanitizer and get_js_sanitizer.
 *
 * @param rewriter PHP assoc array containing values needed to properly construct
 *                 the fbml_attr_rewriter addressed by param.
 * @param param the value that should be used to populate the 'rewrite_data'
 *              field of the fbml_attr_rewriter addressed by attr_rewriter.
 * @param attr_rewriter the address of the fbml_attr_rewriter being populated with
 *                      data supplied via 'rewriter' and 'param'
 * @return NULL if the assoc array is either NULL or empty.  Otherwise, the
 *         value passed in via 'attr_rewriter' is returned verbatim.
 */

static struct fbml_attr_rewriter *
get_attr_rewriter(zval *rewriter,
                  struct callback_parameter *param,
                  struct fbml_attr_rewriter *attr_rewriter) {
  HashTable *ht;
  void *data;

  TSRMLS_FETCH();

  if ((rewriter == NULL) || ZVAL_IS_NULL(rewriter)) return NULL;
  ht = HASH_OF(rewriter);
  if (!ht) return NULL;

  if (zend_hash_find(ht, "func", 5, &data) == SUCCESS) {
    param->user_func_name = Z_STRVAL_PP((zval**) data);
  } else {
    param->user_func_name = "fbml_rewrite_event_handler";
  }

  if (zend_hash_find(ht, "data", 5, &data) == SUCCESS) {
    param->user_data = *(zval**) data;
  } else {
    param->user_data = NULL;
  }

  param->fallback = "";
  attr_rewriter->pfunc_rewriter = callback_attr_function;
  attr_rewriter->rewrite_data = param;
  return attr_rewriter;
}

/**
 * Utility function that iterates over all of the values in the
 * supplied 'contains_hash' and makes shallow copies of the
 * C strings that back the PHP string values, storing them
 * into the 'strings' array.  The last meaningful entry in the
 * 'strings' array is followed by a NULL.
 *
 * @param contains_hash the PHP associative array whose values (presumably
 *                      expressed as PHP strings) should be copied into
 *                      the 'strings' array.
 * @param strings the array of char *s that should be populated with all of the
 *                char *s that back the PHP strings that sit as values in the
 *                associative array addressed by 'contains_hash'.  Note that
 *                the implementation here sets each entry in the 'strings' array
 *                to be a shallow copy of the C string backing the
 *                PHP strings.  That means the pointers ultimately stored in
 *                the 'strings' are not to be passed to any free/efree/pefree function.
 */

static void copy_hash_to_strings(zval *contains_hash, char **strings)
{
  int i;
  HashTable *ht;
  zval **data;

  TSRMLS_FETCH();

  ht = HASH_OF(contains_hash);
  for (zend_hash_internal_pointer_reset(ht), i = 0;
       zend_hash_get_current_data(ht, (void**)&data) == SUCCESS &&
	 i < MAXTAGSIZE - 1; // -1 to ensure room for the NULL
       zend_hash_move_forward(ht)) {
    strings[i++] = Z_STRVAL_PP(data);
  }

  strings[i] = NULL; // i guaranteed to be at most MAXTAGSIZE - 1
}

/**
 * Dynamically allocates an fbml_flaggable_tags record, dynamically
 * allocates the 'tags' array inside, and then populates the 'flag' field
 * and the 'tags' array with information drawn from the 'flag' parameter
 * and the values (but not the keys) stored inside the PHP associative
 * array addressed by 'hash'.  (Note that at most MAXTAGSIZE - 1 values
 * from the hash are shallow copied into the 'tags' array.)
 *
 * @param hash the hook to the PHP array whose values (not the keys, but the
 *             values) should be shallow-copied into fbml_flaggable_tags
 *             record we're constructing.
 * @param flag the flag used to classify certain FBML and HTML node types.
 * @return the address of the freshly allocated, newly constructed
 *             fbml_flaggable_tags record.
 */

static struct fbml_flaggable_tags *make_tag_flags(zval *hash, char flag)
{
  struct fbml_flaggable_tags *tags = emalloc(sizeof(struct fbml_flaggable_tags));
  tags->tags = ecalloc(MAXTAGSIZE, sizeof(char *));
  copy_hash_to_strings(hash, tags->tags);
  tags->flag = flag;
  return tags;
}

/**
 * Disposes of the dynamically allocated memory accessible from the
 * address held by 'tag_flags'.  Note that the record type addressed
 * by fbml_flaggable_tags holds a dynamically allocated array of
 * pointers in its 'tags' field; this is also freed.
 *
 * @param tag_flags the address of the fbml_flaggable_tags record
 *                  that should be fully donated back to the heap.
 *                  Note that both the record and the 'tags' array
 *                  within were allocated using the e*alloc family
 *                  of memory allocators.
 */

static void free_tag_flags(struct fbml_flaggable_tags *tag_flags)
{
  efree(tag_flags->tags);
  efree(tag_flags);
}

/**
 * Allocates and initializes an fbml_flaggable_attrs record, allocates
 * the 'attrs' array of character pointers, and initialized the
 * contents based on the values held by 'hash' and flag.
 *
 * @param hash a PHP assoc whose values should be shallow-copied into
 *             the fbml_flaggable_attrs struct being created.
 * @param flag the attribute flag that should be set as the key/flag
 *             of the new struct being created.
 * @return the address of a dynamically allocated fbml_flaggable_attrs
 *         struct, populated with information drawn from the 'hash' and 'flag'
 *         parameters.
 */

struct fbml_flaggable_attrs *make_attr_flags(zval *hash, char flag)
{
  struct fbml_flaggable_attrs * attrs = emalloc(sizeof(struct fbml_flaggable_attrs));
  attrs->attrs = ecalloc(MAXTAGSIZE, sizeof(char *));
  copy_hash_to_strings(hash, attrs->attrs);
  attrs->flag = flag;
  return attrs;
}

/**
 * Disposes of the dynamically allocated fbml_flaggable_attrs record
 * addressed by 'attr_flags', along with the dynamically allocated
 * 'attrs' array inside.
 *
 * @param attr_flags the address of the fbml_flaggable_attrs record
 *                   to be donated back to the heap.  Note that
 *                   both the record addressed by 'attr_flags' and
 *                   the 'attrs' arrays inside the record were allocated
 *                   using the e*alloc family of allocators.
 */

static void free_attr_flags(struct fbml_flaggable_attrs *attr_flags)
{
  efree(attr_flags->attrs);
  efree(attr_flags);
}

/**
 * Allocates the space for an fbml_schema record with room for
 * MAXTAGSIZE illegal_children pointers and another MAXTAGSIZE
 * illegal_children_attr pointers.  Uses the e*alloc family of
 * allocators.
 *
 * @return the address of a dynamically allocated, largely uninitialized
 *         fmbl_scheme record.  Note that the illegal_children and
 *         illegal_children_attr arrays are also dynamically allocated.
 */

static struct fbml_schema *make_fbml_schema()
{
  struct fbml_schema *in_schema = emalloc(sizeof(struct fbml_schema));
  in_schema->illegal_children = ecalloc(MAXTAGSIZE, sizeof(char *));
  in_schema->illegal_children_attr = ecalloc(MAXTAGSIZE, sizeof(char *));
  return in_schema;
}

/**
 * Disposes of the dynamically allocated memory at the provided
 * address, including all dynamically allocated memory inside.
 * Read through the documentation for the implementation of the
 * make_fbml_schema function to see what memory is dynamically
 * allocated (via emalloc/ecalloc).
 *
 * @param schema the address of the dynamically allocated fbml_schema
 *               record that should be destroyed and deallocated.
 */

static void free_fbml_schema(struct fbml_schema *schema)
{
  efree(schema->illegal_children);
  efree(schema->illegal_children_attr);
  efree(schema);
}

/**
 * Dynamically allocates an fbml_context_schema and initializes it using the
 * supplied arguments.  Note: the new fbml_context_schema does *not* take ownership
 * of the 'key' string, but it *does* take ownership of the fbml_context_schema
 * addressed by 'in_schema'.  All this means is that the free_fbml_context_schema
 * function below needs to dispose of the fbml_context_schema it now owns, but
 * it should not dispose of the incoming char *.
 *
 * @param key the key that should be recorded as the context_tag of the
 *            new fbml_context_schema being created.
 * @param in_schema the address of the the fbml_schema struct to which the
 *                  supplied key should be attached.
 * @return the address of the freshly allocated and initialized fbml_context_schema.
 */

static struct fbml_context_schema *
make_fbml_context_schema(char *key, struct fbml_schema *in_schema)
{
  struct fbml_context_schema *new_context = emalloc(sizeof(struct fbml_context_schema));
  new_context->context_tag = key;
  new_context->schema = ecalloc(2, sizeof(struct fbml_schema *));
  new_context->schema[0] = in_schema;
  new_context->schema[1] = NULL;
  return new_context;
}

/**
 * Disposes of the memory owned by the supplied address.  Note that
 * the 'schema' array within the addressed fbml_context_schema struct is
 * also dynamically allocated, and the one fbml_schema owned by that
 * array should be disposed of as well.
 *
 * @param context the address of the dynamically allocated fbml_context_schema
 *                that should be disposed of.
 */

static void free_fbml_context_schema(struct fbml_context_schema *context)
{
  free_fbml_schema(context->schema[0]);
  efree(context->schema);
  efree(context);
}

static void _fbml_complex_expand_tag_list(zval *tags, zval *attrs,
                                          zval *special_html, zval *precache_tags, zval *style_tags,
                                          zval *z_style_attrs, zval *z_script_attrs, zval *z_rewrite_attrs, zval *z_special_attrs,
                                          zval *schema)
{
  HashTable *ht, *ht2;
  ulong index;
  char * key;
  zval **data, **data2;
  int current_key;
  char *new_tags[MAXTAGSIZE];
  char *new_attrs[MAXTAGSIZE];

  // special, precache, style, macros
  struct fbml_flaggable_tags *flagged_tags[5], **t_iter;
  // style, script, special, rewrite
  struct fbml_flaggable_attrs *flagged_attrs[5], **a_iter;
  struct fbml_context_schema *context_schema[MAXTAGSIZE], **context_iter;
  struct fbml_schema * in_schema;
  int i, j, sizeLimit, type, k_len;

  // Extension Tags
  copy_hash_to_strings(tags, new_tags);

  // Extension Attrs
  copy_hash_to_strings(attrs, new_attrs);

  /********************/
  flagged_tags[0] = make_tag_flags(special_html, FB_FLAG_SPECIAL_HTML );
  flagged_tags[1] = make_tag_flags(precache_tags, FB_FLAG_PRECACHE);
  flagged_tags[2] = make_tag_flags(tags, FB_FLAG_FBNODE);
  flagged_tags[3] = make_tag_flags(style_tags, FB_FLAG_STYLE);
  flagged_tags[4] = NULL;
  /*********************/

  flagged_attrs[0] = make_attr_flags(z_special_attrs, FB_FLAG_ATTR_SPECIAL );
  flagged_attrs[1] = make_attr_flags(z_rewrite_attrs, FB_FLAG_ATTR_REWRITE);
  flagged_attrs[2] = make_attr_flags(z_script_attrs, FB_FLAG_ATTR_SCRIPT);
  flagged_attrs[3] = make_attr_flags(z_style_attrs, FB_FLAG_ATTR_STYLE);
  flagged_attrs[4] = NULL;
  /*************************/

  TSRMLS_FETCH();

  //schema - double loop {"context_tag" -> [tags]}
  ht = HASH_OF(schema);
  for (zend_hash_internal_pointer_reset(ht), i = 0;
       zend_hash_get_current_data(ht, (void**)&data) == SUCCESS &&
         i < MAXTAGSIZE;
       zend_hash_move_forward(ht)) {

    in_schema = make_fbml_schema();

    ht2 = HASH_OF(*data);
    int n_children =0;
    int n_attr =0;
    for (zend_hash_internal_pointer_reset(ht2), j = 0;
	 zend_hash_get_current_data(ht2, (void**)&data2) == SUCCESS &&
	   j < MAXTAGSIZE;
	 zend_hash_move_forward(ht2)) {
      char * s = Z_STRVAL_PP(data2);
      if (s[0] != '_') {
	in_schema->illegal_children[n_children++] = s;
      } else {
	in_schema->illegal_children_attr[n_attr++] = &s[1];
      }
      j++;
    }
    in_schema->illegal_children[n_children] = NULL;
    in_schema->illegal_children_attr[n_attr] = NULL;

    type = zend_hash_get_current_key(ht, &key, &index, 0);
    in_schema->ancestor_tag = key;
    context_schema[i] = make_fbml_context_schema(key, in_schema);
    i++;
  }
  context_schema[i] = NULL;

  //TODO: fix this
  fbml_expand_tag_list(new_tags, new_attrs, flagged_tags, flagged_attrs, context_schema);

  for (context_iter = context_schema; (*context_iter); context_iter++) {
    free_fbml_context_schema(*context_iter);
  }

  for (t_iter = flagged_tags; (*t_iter); t_iter++) {
    free_tag_flags(*t_iter);
  }

  for (a_iter = flagged_attrs; (*a_iter); a_iter++) {
    free_attr_flags(*a_iter);
  }
}

/**
 * Constructs an fbml_css_sanitizer out of the material held by the
 * assoc array addressed by 'sanitizer', and then passes the buck
 * to the fbml_sanitize_css routine.  Once the sanitized CSS string
 * has been constructed, the return_value addressed by 'ret' is constructed
 * to be an associative array and populated with that sanitized CSS string
 * and any error message information.
 *
 * @param ret the address of the zval that should be constructed as
 *            an array and populated with information that comes back
 *            to us after fbml_sanitize_css has returned.
 * @param css the incoming, unsanitized C string of CSS material.
 * @param decl_only a Boolean value: true if the CSS was supplied
 *                  as a style attribute value (as in style="width: 60px;'),
 *                  and false if the CSS was embedded inside style tags (as
 *                  in <style> .myStyle { width: 60px; } </style>.
 * @param line_number the line number where the CSS being sanitized resides
 *                    within the original file.
 * @param sanitizer the associative array of material used to construct the
 *                  fbml_css_sanitizer needed for the fbml_sanitize_css call.
 */

static void _fbml_sanitize_css(zval *ret, char *css, long decl_only,
                               long line_number, zval *sanitizer)
{
  struct callback_parameter param;
  struct fbml_css_sanitizer css_sanitizer;
  char *sanitized_css = NULL;
  char *error = NULL;

  fbml_sanitize_css(css, decl_only, line_number,
                    get_css_sanitizer(sanitizer, &param, &css_sanitizer),
                    &sanitized_css, &error);

  // construct return value
  array_init(ret);
  if (sanitized_css != NULL) {
    add_assoc_string(ret, "sanitized", sanitized_css, 1);
    free(sanitized_css);
  }

  if (error != NULL) {
    if (*error != '\0') {
      add_assoc_string(ret, "error", error, 1);
    }
    free(error);
  }
}

/**
 * Proxy fuction that sits in between the implementation of
 * PHP's fbml_sanitize_js and the implementation of the
 * C function fbml_sanitize_js.  fbml_sanitize_js is really
 * in place to marshal the data from PHP to C before calling
 * this function.  This function calls fbml_sanitize_js (in ../src/fbml.cpp)
 * to do the actual sanitizing, and then it combines its results into
 * an associate array that's planted at the supplied zval address by 'ret'.
 *
 * @param ret the address of the zval where all return information--the sanitized
 *            JS string and/or any error information about the JS parse.  Note
 *            that 'ret' initially addresses an allocated but uninitilized zval.
 * @param js a C string of Javascript text.
 * @param js_len the length of the Javascript text stored in 'js'.
 * @param line_number the line number where the original JavaScript resides in the
 *                    original source stream.
 * @param sanitizer a PHP array describing how the fbml_js_sanitizer should be
 *                  configured.
 */

static void _fbml_sanitize_js(zval *ret, char *js, int js_len,
                              long line_number, zval *sanitizer)
{
  struct fbml_js_sanitizer js_sanitizer;
  struct callback_parameter param;
  char *sanitized_js = NULL;
  char *error = NULL;

  fbml_sanitize_js(js, js_len, 0, line_number,
                   get_js_sanitizer(sanitizer, &param, &js_sanitizer),
                   &sanitized_js, &error);

  // construct return value: PHP array with one of two entries.
  array_init(ret);
  if (sanitized_js != NULL) {
    add_assoc_string(ret, "sanitized", sanitized_js, 1);
    free(sanitized_js);
  }

  if (error != NULL) {
    if (*error != '\0') {
      add_assoc_string(ret, "error", error, 1);
    }
    free(error);
  }
}

/**
 * Returns true if and only if fbml_complex_expand_tag_list
 * has already been called.
 *
 * @return true if fbml_complex_expand_list has already been called,
 *              and false otherwise.
 */

PHP_FUNCTION(fbml_is_parser_configured)
{
  RETURN_BOOL(fbml_tag_list_expanded());
}

// version to be deprecated, but need both names during the transition from 1.1 to 1.2
PHP_FUNCTION(fbml_tag_list_expanded_11)
{
  RETURN_BOOL(fbml_tag_list_expanded());
}

/**
 * Pulls in huge collection of PHP arrays and compiles a huge database of
 * information needed for the FBML parser to effeciently and correctly
 * parse, model, and render an FBML document.  Note that fbml_configure_parser
 * is the 1.2 name, and that fbml_complex_expand_tag_list_11 is the 1.1 version.
 * We need both version at the moment to manage the transition from 1.1 to 1.2.
 */

PHP_FUNCTION(fbml_configure_parser)
{
  zval *new_tags;
  zval *new_attrs;
  zval *special_html;
  zval *precache;
  zval *style;
  zval *style_attrs;
  zval *script_attrs;
  zval *rewrite_attrs;
  zval *special_attrs;
  zval *schema;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "aaaaaaaaaa",
			    &new_tags, &new_attrs, &special_html, &precache, &style,
			    &style_attrs, &script_attrs, &rewrite_attrs, &special_attrs, &schema) == FAILURE) {
    return;
  }

  _fbml_complex_expand_tag_list(new_tags, new_attrs, special_html, precache, style,
				style_attrs, script_attrs, rewrite_attrs, special_attrs, schema);
}

PHP_FUNCTION(fbml_complex_expand_tag_list_11)
{
  zval *new_tags;
  zval *new_attrs;
  zval *special_html;
  zval *precache;
  zval *style;
  zval *style_attrs;
  zval *script_attrs;
  zval *rewrite_attrs;
  zval *special_attrs;
  zval *schema;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "aaaaaaaaaa",
			    &new_tags, &new_attrs, &special_html, &precache, &style,
			    &style_attrs, &script_attrs, &rewrite_attrs, &special_attrs, &schema) == FAILURE) {
    return;
  }

  _fbml_complex_expand_tag_list(new_tags, new_attrs, special_html, precache, style,
				style_attrs, script_attrs, rewrite_attrs, special_attrs, schema);
}

/**
 * Parses an FBML string, builds a C parse tree, and returns a PHP resource
 * surrounding the root of that parse tree.  Most of the interesting work
 * is done by the fbml_parse routine.  But this function needs to
 * marshal the incoming data from PHP to C format, pre-allocate space for the
 * return value resource, call fbml_parse to actually parse the tree, and then
 * initialize the resource being returned out of information constructed by
 * the parse.
 *
 * @param first-arg the string of FBML to be parsed.  It's marshaled from a PHP
 *                  string to a local C string variable called 'fbml' (length
 *                  stored in local variable call 'fbml_len'.)
 * @param second-arg logical true if and only if the body of the FBML document
 *                   should contribute to the parse tree.  This Boolean flag to
 *                   bound to the local variable called 'body_only'.
 * @param third-arg logical true if and only if FBML/HTML comments should be retained and
 *                  contribute to the parse tree.
 * @param fourth-arg an optional flag (default assumed to be false) stating whether or
 *                   not the parse needs to be sensitive to internationalization issues.
 *                   Whether the user supplies a value or he/she just uses the default, the
 *                   flag is stored in the local long variable called 'internal_mode'.
 * @param fifth-arg a PHP array describing how the CSS sanitizer should be configured.
 * @param sixth-arg a PHP array describing how the JavaScript sanitizer should be configured.
 * @param seventh-arg a PHP array describing how the attribute rewriter should be configured.
 */

PHP_FUNCTION(fbml_parse)
{
  struct node_res *node_res;
  zval *tree_holder, *root;
  struct fbml_node *tree = NULL;
  struct fbml_node *root_node;
  char *fbml, *rewrite_func;
  int fbml_len, rewrite_func_len;
  long body_only;
  long preserve_comment;

  long internal_mode = 0;
  zval *css_sanitizer = NULL;
  zval *js_sanitizer = NULL;
  zval *attr_rewriter = NULL;
  char *error;

  struct callback_parameter css_param;
  struct fbml_css_sanitizer css_sanitizer_data;
  struct callback_parameter js_param;
  struct fbml_js_sanitizer js_sanitizer_data;
  struct callback_parameter attr_param;
  struct fbml_attr_rewriter attr_rewriter_data;

  // malloc (freed in node_dtor)
  node_res = emalloc(sizeof(struct node_res));
  if (!node_res) return;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sll|laaa",
			    &fbml, &fbml_len, &body_only,
			    &preserve_comment, &internal_mode,
			    &css_sanitizer, &js_sanitizer, &attr_rewriter) == FAILURE) return;

  fbml_parse(fbml, body_only, preserve_comment, internal_mode,
	     get_css_sanitizer(css_sanitizer, &css_param, &css_sanitizer_data),
	     get_js_sanitizer(js_sanitizer, &js_param, &js_sanitizer_data),
	     get_attr_rewriter(attr_rewriter, &attr_param, &attr_rewriter_data),
	     NULL, NULL, &tree, &error);

  root_node = tree;

  if (body_only && tree->children_count == 1)
    root_node = tree->children[0];

  // what follows is the construction of return value resource
  // surrounding the full tree
  array_init(return_value);
  MAKE_STD_ZVAL(tree_holder);
  ZEND_REGISTER_RESOURCE(tree_holder, tree, tree_descriptor);
  node_res->node = root_node;
  node_res->tree = tree_holder;

  MAKE_STD_ZVAL(root);
  ZEND_REGISTER_RESOURCE(root, node_res, node_descriptor);
  add_assoc_zval(return_value, "root", root);

  if (error != NULL) {
    if (*error != '\0') {
      add_assoc_string(return_value, "error", error, 1);
    }

    free(error);
  }
}

PHP_FUNCTION(fbml_parse_opaque_11)
{
  struct node_res *node_res;
  zval *tree_holder, *root;
  struct fbml_node *tree = NULL;
  struct fbml_node *root_node;
  char *fbml, *rewrite_func;
  int fbml_len, rewrite_func_len;
  long body_only;
  long preserve_comment;

  long internal_mode = 0;
  zval *css_sanitizer = NULL;
  zval *js_sanitizer = NULL;
  zval *attr_rewriter = NULL;
  char *error;

  struct callback_parameter css_param;
  struct fbml_css_sanitizer css_sanitizer_data;
  struct callback_parameter js_param;
  struct fbml_js_sanitizer js_sanitizer_data;
  struct callback_parameter attr_param;
  struct fbml_attr_rewriter attr_rewriter_data;

  // malloc (freed in node_dtor)
  node_res = emalloc(sizeof(struct node_res));
  if (!node_res) return;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sll|laaa",
			    &fbml, &fbml_len, &body_only,
			    &preserve_comment, &internal_mode,
			    &css_sanitizer, &js_sanitizer, &attr_rewriter) == FAILURE) return;

  fbml_parse(fbml, body_only, preserve_comment, internal_mode,
	     get_css_sanitizer(css_sanitizer, &css_param, &css_sanitizer_data),
	     get_js_sanitizer(js_sanitizer, &js_param, &js_sanitizer_data),
	     get_attr_rewriter(attr_rewriter, &attr_param, &attr_rewriter_data),
	     NULL, NULL, &tree, &error);

  root_node = tree;

  if (body_only && tree->children_count == 1)
    root_node = tree->children[0];

  // what follows is the construction of return value resource
  // surrounding the full tree
  array_init(return_value);
  MAKE_STD_ZVAL(tree_holder);
  ZEND_REGISTER_RESOURCE(tree_holder, tree, tree_descriptor);
  node_res->node = root_node;
  node_res->tree = tree_holder;

  MAKE_STD_ZVAL(root);
  ZEND_REGISTER_RESOURCE(root, node_res, node_descriptor);
  add_assoc_zval(return_value, "root", root);

  if (error != NULL) {
    if (*error != '\0') {
      add_assoc_string(return_value, "error", error, 1);
    }

    free(error);
  }
}

/**
 * Parses the supplied PHP string of CSS text and returns a sanitized
 * version of it, using the supplied CSS sanitizer.
 *
 * @param first-arg a PHP string of CSS that needs to be sanitized.
 *                  The PHP string is marshaled to C space and placed
 *                  in the local C string variable called 'css'.
 * @param second-arg true if the supplied CSS text comes from a style
 *                   attribute, and false if the supplied CSS text
 *                   is between open and close <style> tags.
 * @param third-arg the line number where the supplied CSS can be found
 *                  in the original source stream.
 * @param fourth-arg PHP array describing how the CSS callback function should
 *                   be constructed.  All keys are optional and only need to be
 *                   supplied if you wish to override aspects of the default
 *                   configuration.  These optional keys are:
 *
 *                   + prefix: the prefix of text that should be prepend to
 *                             all selectors.
 *                   + func: the name of the function in PHP space that knows
 *                           how to translate URLs.
 *                   + data: client data to be passed through as the first agument to
 *                           to the URL translation callback function.
 *
 * @return a PHP string holding the sanitized version of the incoming CSS text.
 */

PHP_FUNCTION(fbml_sanitize_css)
{
  char *css;
  int css_len;
  long decl_only;
  long line_number;
  zval *sanitizer;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "slla",
			    &css, &css_len, &decl_only, &line_number,
			    &sanitizer)
      == FAILURE) return;

  _fbml_sanitize_css(return_value, css, decl_only, line_number, sanitizer);
}

PHP_FUNCTION(fbml_sanitize_css_11)
{
  char *css;
  int css_len;
  long decl_only;
  long line_number;
  zval *sanitizer;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "slla",
			    &css, &css_len, &decl_only, &line_number,
			    &sanitizer)
      == FAILURE) return;

  _fbml_sanitize_css(return_value, css, decl_only, line_number, sanitizer);
}

/**
 * Parses and, as needed, sanitizes the supplied piece of JavaScript using the
 * supplied JavaScript santizer.  fbml_sanitize_js really just marshals the
 * three arguments from PHP to C, and then passes the buck to _fbml_sanitize_js, which
 * does the actual sanitizing.
 *
 * @param first-arg a fragment of JavaScript to be sanitized.
 * @param second-arg the line number where the JavaScript can be found in the original
 *                   source stream.
 * @param third-arg a PHP array containing all of the information needed to do the actual
 *                  sanitizing.  All keys are optional and need to be included only if
 *                  you wish to override default values.  These optional keys are:
 *
 *                  + func: the name of the callback function in PHP space.
 *                  + data: any client data that should be passed through as the first
 *                          argument to the PHP callback function.
 *                  + that: the PHP string that should replace all instances of the
 *                          'this' paramter used in JavaScript code.
 *                  + prefix: the PHP string that should be prepended to all function
 *                            calls and references to variables.
 *
 * @return a PHP array storing one or two pieces of information under the following
 *         keys:
 *                + "sanitized": the sanitized form of the Javascript text, stored
 *                               as a PHP string.
 *                + "error": the accumulation of all error messages posted by the
 *                           sanitizer.
 */

PHP_FUNCTION(fbml_sanitize_js)
{
  char *js;
  int js_len;
  long line_number;
  zval *sanitizer;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sla",
			    &js, &js_len, &line_number, &sanitizer) == FAILURE) return;

  _fbml_sanitize_js(return_value, js, js_len, line_number, sanitizer);
}

PHP_FUNCTION(fbml_sanitize_js_11)
{
  char *js;
  int js_len;
  long line_number;
  zval *sanitizer;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sla",
			    &js, &js_len, &line_number, &sanitizer) == FAILURE) return;

  _fbml_sanitize_js(return_value, js, js_len, line_number, sanitizer);
}

/**
 * Accepts a node_res resource from the PHP call stack, extracts the
 * tag name from within the fbml_node that's within the node_res, and then
 * returns a PHP string version of it.
 *
 * @param first-arg the node_res * surrounding the fbml_node of interest.  Note that
 *                  most of the work here involves marshaling data across the PHP->C
 *                  boundary.
 * @return a PHP string clone of the fbml_node's tag.
 */

PHP_FUNCTION(fbml_get_tag_name)
{
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)== FAILURE) return;
  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);

  struct fbml_node *node = node_res->node;
  char *tag = node->tag_name;
  RETURN_STRING(tag, 1);
}

PHP_FUNCTION(fbml_get_tag_name_11)
{
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)== FAILURE) return;
  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);

  struct fbml_node *node = node_res->node;
  char *tag = node->tag_name;
  RETURN_STRING(tag, 1);
}

/**
 * PHP function that accepts the address of a node_res and returns the line
 * number of the root of the tree held by this node_res.  The line number
 * is really the line number where the corresponding tag or text modeled
 * by the root can be found within the original text stream.  This function
 * in being introduced in the 1.2 version.
 *
 * @param first-arg a PHP resource id corresponding to some node_res *.
 * @return The line number within the original document which houses the
 *         element modeled by the root of the doc tree.
 */

PHP_FUNCTION(fbml_get_line_number)
{
  zval *r;
  struct node_res *node_res;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,
			    "r", &r) == FAILURE) return; // evaluate to nothing
  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  struct fbml_node *node = node_res->node;
  int line_num = node->line_num;
  RETURN_LONG(line_num);
}

/**
 * Userspace function which takes the root of some tree and returns
 * the serialization of the entire tree as a PHP string.  The manner in
 * which the doc tree gets serialized is dictated entirely by the fbml_node_print
 * function, which is implemented and documented in ../src/fbml.cpp.
 *
 * @param first-arg a PHP resource corresponding to some node_res *.
 * @return the serialization, expressed as a PHP string, of the entire FBML tree accessible
 *         via the first-arg.
 */

PHP_FUNCTION(fbml_flatten)
{
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  char *s = fbml_node_print(node_res->node);
  ZVAL_STRING(return_value, s, 1);
  free(s);
}

PHP_FUNCTION(fbml_flatten_11)
{
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  char *s = fbml_node_print(node_res->node);
  ZVAL_STRING(return_value, s, 1);
  free(s);
}

/**
 * Accepts a PHP resource associated with the address of the
 * root of an FBML tree, and returns the number of children that
 * root node has.
 *
 * @param first-arg a PHP resource corresponing to some node_res *, where
 *                  the node_res in question is the root of some FBML tree.
 * @return the number of children hanging from the root node of the
 *         FBML tree represented by the incoming PHP resource.
 */

PHP_FUNCTION(fbml_get_children_count)
{
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,
			    "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);

  RETURN_LONG(node_res->node->children_count);
}

PHP_FUNCTION(fbml_get_children_count_11)
{
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,
			    "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);

  RETURN_LONG(node_res->node->children_count);
}

/**
 * Accepts a PHP resource associated with the root of an FBML tree,
 * and returns a PHP array of those PHP resources representing the
 * root's children.
 *
 * @param first-arg a PHP resource corresponing to some node_res *, where
 *                  the node_res in question is the root of some FBML tree.
 * @return a PHP array of PHP resources, where each resource represents one
 *         of the incoming root node's children.
 */

PHP_FUNCTION(fbml_get_children)
{
  struct node_res *node_res;
  int i;
  zval *r;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,
			    "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);

  struct fbml_node *node = node_res->node;
  array_init(return_value);
  for (i = 0; i < node->children_count; i++) {
    zval *node_zval = register_new_node(node->children[i], node_res->tree);
    add_next_index_zval(return_value, node_zval);
  }
}

PHP_FUNCTION(fbml_get_children_11)
{
  struct node_res *node_res;
  int i;
  zval *r;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,
			    "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);

  struct fbml_node *node = node_res->node;
  array_init(return_value);
  for (i = 0; i < node->children_count; i++) {
    zval *node_zval = register_new_node(node->children[i], node_res->tree);
    add_next_index_zval(return_value, node_zval);
  }
}

/**
 * Similar to fbml_get_children, except that this version only includes
 * children with the same tag name as the one supplied as the second parameter.
 *
 * @param a PHP resource surrounding a node_res *
 * @param the tag of interest, expressed as a PHP string.
 * @return PHP array of children resources that match the supplied tag.
 */

PHP_FUNCTION(fbml_get_children_by_name)
{
  struct node_res *node_res;
  zval *r;
  char *name;
  int name_len;
  int i;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs",
			    &r, &name, &name_len) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  struct fbml_node *node = node_res->node;
  unsigned short lookup = fbml_lookup_tag_by_name(name);

  // iterate through children
  array_init(return_value);
  for (i = 0; i < node->children_count; i++) {
    struct fbml_node *cur = node->children[i];
    if (cur->eHTMLTag == lookup) {
      zval *node_zval = register_new_node(cur, node_res->tree);
      add_next_index_zval(return_value, node_zval);
    }
  }
}

PHP_FUNCTION(fbml_get_children_by_name_11)
{
  struct node_res *node_res;
  zval *r;
  char *name;
  int name_len;
  int i;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs",
			    &r, &name, &name_len) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  struct fbml_node *node = node_res->node;
  unsigned short lookup = fbml_lookup_tag_by_name(name);

  // iterate through children
  array_init(return_value);
  for (i = 0; i < node->children_count; i++) {
    struct fbml_node *cur = node->children[i];
    if (cur->eHTMLTag == lookup) {
      zval *node_zval = register_new_node(cur, node_res->tree);
      add_next_index_zval(return_value, node_zval);
    }
  }
}

/**
 * Accepts the PHP resource surroung and FBML node, extracts all of the
 * name/value attribute pairs, and returns those pairs in the form of
 * a PHP associative array.
 *
 * @param first-arg a PHP resource surrounding an FBML node.
 * @return an associative array mapping the incoming node's attribute
 *         names to their values.
 */

PHP_FUNCTION(fbml_get_attributes)
{
  struct fbml_node *node;
  struct fbml_attribute *cur;
  zval *node_zval;
  int i;

  zval *r;
  struct node_res * node_res;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)== FAILURE) {
    return;
  }

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r,-1,NODE_RESOURCE_NAME,node_descriptor);
  node =node_res->node;

  array_init(return_value);
  for (i = 0; i < node->attribute_count; i++) {
    cur = node->attributes[i];
    add_assoc_string(return_value, cur->name,cur->value,1);
  }
}

PHP_FUNCTION(fbml_get_attributes_11)
{
  struct fbml_node *node;
  struct fbml_attribute *cur;
  zval *node_zval;
  int i;

  zval *r;
  struct node_res * node_res;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)== FAILURE) {
    return;
  }

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r,-1,NODE_RESOURCE_NAME,node_descriptor);
  node =node_res->node;

  array_init(return_value);
  for (i = 0; i < node->attribute_count; i++) {
    cur = node->attributes[i];
    add_assoc_string(return_value, cur->name,cur->value,1);
  }
}

/**
 * Returns the tag type of the identified node.  The tag type
 * returned is expressed as an integer instead of a string.
 *
 * @param a PHP resource surrounding the address of some FBML node.
 */

PHP_FUNCTION(fbml_get_type)
{
  struct fbml_node *node;
  char *name;

  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)== FAILURE) {
    return;
  }

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r,-1,NODE_RESOURCE_NAME,node_descriptor);
  node = node_res->node;

  if (node->eHTMLTag > fbml_html_userdefined_tag) {
    RETURN_LONG(FBML_MACRO_NODE);
  } else if (node->eHTMLTag == fbml_html_style_tag) {
    RETURN_LONG(FBML_STYLE_NODE);
  } else if (node->eHTMLTag == fbml_html_comment_tag) {
    RETURN_LONG(FBML_COMMENT_NODE);
  } else {
    RETURN_LONG(FBML_HTML_NODE);
  }
}

PHP_FUNCTION(fbml_get_type_11)
{
  struct fbml_node *node;
  char *name;

  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)== FAILURE) {
    return;
  }

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r,-1,NODE_RESOURCE_NAME,node_descriptor);
  node = node_res->node;

  if (node->eHTMLTag > fbml_html_userdefined_tag) {
    RETURN_LONG(FBML_MACRO_NODE);
  } else if (node->eHTMLTag == fbml_html_style_tag) {
    RETURN_LONG(FBML_STYLE_NODE);
  } else if (node->eHTMLTag == fbml_html_comment_tag) {
    RETURN_LONG(FBML_COMMENT_NODE);
  } else {
    RETURN_LONG(FBML_HTML_NODE);
  }
}

/**
 * Extracts the named attribute's value from the supplied FBML node.
 * The address of the node is supplied as the first argument, and the
 * name of the attribute is supplised as the second.  This function is
 * more about marshaling data from PHP form to C form, calling the corresponding
 * C function, and then marshaling its result from C back to PHP.
 *
 * @param first-arg a PHP resournce surrounding the address of some FBML node.
 * @param second-arg a PHP string naming some attribute within the FBML node's
 *                   list of attributes.
 */

PHP_FUNCTION(fbml_get_attribute)
{
  struct fbml_node *node;
  char *name;
  char *result;
  int state;
  int name_len;
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &r, &name, &name_len)== FAILURE) {
    return;
  }

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r,-1,NODE_RESOURCE_NAME,node_descriptor);
  node = node_res->node;

  //don't need to free result
  result = fbml_node_get_attribute(node, name);

  if (result == NULL) {
    RETURN_NULL();
  } else {
    RETURN_STRING(result, 1);
  }
}

PHP_FUNCTION(fbml_get_attribute_11)
{
  struct fbml_node *node;
  char *name;
  char *result;
  int state;
  int name_len;
  zval *r;
  struct node_res *node_res;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &r, &name, &name_len)== FAILURE) {
    return;
  }

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r,-1,NODE_RESOURCE_NAME,node_descriptor);
  node = node_res->node;

  //don't need to free result
  result = fbml_node_get_attribute(node, name);

  if (result == NULL) {
    RETURN_NULL();
  } else {
    RETURN_STRING(result, 1);
  }
}

/**
 * Accepts a PHP string, assumed to be some value attached to a
 * color attribute, and returns another PHP string of the form
 * "#cccccc;".  The incoming string can be anything of the form
 * "#[0-9a-fA-F]{6}", or it can be one of the string constants
 * recognized by most browsers as legitimate colors, such as
 * "red", "dark
 *
 * @param first-arg a string describing some color, as in "red" or
 *                  "#886699".
 * @return a normalized form of the incoming string.
 */

PHP_FUNCTION(fbml_attr_to_color)
{
  char * name;
  char * result;
  int state;
  int name_len;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s",
			    &name, &name_len)== FAILURE) return;

  state = fbml_node_attr_to_color(name, &result);
  if (state == 1) {
    ZVAL_STRING(return_value, result, 1);
    free(result);
  } else {
    RETURN_NULL();
  }
}

PHP_FUNCTION(fbml_attr_to_color_11)
{
  char * name;
  char * result;
  int state;
  int name_len;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s",
			    &name, &name_len)== FAILURE) return;

  state = fbml_node_attr_to_color(name, &result);
  if (state == 1) {
    ZVAL_STRING(return_value, result, 1);
    free(result);
  } else {
    RETURN_NULL();
  }
}

/**
 * Trivial utility function that converts a PHP string into a PHP Boolean
 * value.  Check the documentation for the fbml_node_attr_to_bool function
 * in ../src/fbml.cpp for all of the details.  In a nutshell, strings like
 * "yes", "true", "TRUE", "tRUe", and "1" prompt fbml_attr_to_bool_11 to
 * return PHP's version of true.  Strings like "no", "NO", "false", "fAlsE",
 * and "0" are interpreted prompt fbml_node_attr_to_bool_11 to return PHP's
 * version of false.  Note that the evaulation of the PHP string is case
 * insensitive.
 *
 * @param first-arg PHP string to be interpreted as a Boolean value.
 * @return 1 if the incoming PHP string can be interpreted as logical TRUE,
 *         0 if the incoming PHP string can be interpreted as logical FALSE,
 *         NULL if the PHP string isn't numeric and isn't some case-insensitive
 *         variation on "yes", "no", "true", or "false".
 */

PHP_FUNCTION(fbml_attr_to_bool)
{
  char *name;
  int name_len;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s",
			    &name, &name_len)== FAILURE) return;

  int result;
  int state = fbml_node_attr_to_bool(name, &result);
  if (state == 1) {
    RETURN_BOOL(result);
  } else {
    RETURN_NULL();
  }
}

PHP_FUNCTION(fbml_attr_to_bool_11)
{
  char *name;
  int name_len;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s",
			    &name, &name_len)== FAILURE) return;

  int result;
  int state = fbml_node_attr_to_bool(name, &result);
  if (state == 1) {
    RETURN_BOOL(result);
  } else {
    RETURN_NULL();
  }
}

/**
 * Accepts the root of the FBML tree, some arbitrary client data, and
 * the name of the PHP userspace function that should be applied to
 * all FBML nodes carrying the FB_FLAG_PRECACHE flag.  fbml_precache
 * marshals the PHP arguments into their C equivalents, then passes the
 * buck to the internal fbml_node_precache function, which actually does the
 * mapping.
 *
 * Highlights:
 *  + The mapping function being applied is a PHP function taking two arguments.
 *    The first argument is the client data passed as the second argument to this
 *    function.  The second argument is the address of an FBML node that's been marked
 *    with the FB_FLAG_PRECACHE bit.
 *  + Note that the map function is only applied to those FBML nodes that're carrying
 *    the FB_FLAG_PRECACHE.  The marking is done during FBML tree construction--that
 *    is, during the call to fbml_parse.
 *
 * @param first-arg the address of a node_res structure storing the root of a
 *                  a full FBML tree.  The address of the root is ultimately
 *                  placed in the local variable 'node'.
 * @param second-arg a piece of client data that should be passed verbatim as
 *                   the first argument to the precache mapping routine.
 * @param third-arg the name of the function in PHP userspace that should be
 *                  applied to each node carrying the FB_FLAG_PRECACHE flag.
 */

PHP_FUNCTION(fbml_precache)
{
  zval *r;
  zval *data;
  char *f_name;
  int f_len;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rzs",
			    &r, &data, &f_name, &f_len)== FAILURE) return;

  struct node_res *node_res;
  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  struct fbml_node *node = node_res->node;

  struct node_callback_parameter p;
  p.tree = node_res->tree;
  p.user_func_name = f_name;
  p.user_data = data;
  p.fallback = "";

  struct fbml_node_precacher node_pre;
  node_pre.precache_node_data = &p;
  node_pre.pfunc_precacher = callback_node_function;
  fbml_node_precache(node, &node_pre);
}

PHP_FUNCTION(fbml_precache_11)
{
  zval *r;
  zval *data;
  char *f_name;
  int f_len;
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rzs",
			    &r, &data, &f_name, &f_len)== FAILURE) return;

  struct node_res *node_res;
  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  struct fbml_node *node = node_res->node;

  struct node_callback_parameter p;
  p.tree = node_res->tree;
  p.user_func_name = f_name;
  p.user_data = data;
  p.fallback = "";

  struct fbml_node_precacher node_pre;
  node_pre.precache_node_data = &p;
  node_pre.pfunc_precacher = callback_node_function;
  fbml_node_precache(node, &node_pre);
}

/**
 * Effectively the same as the C function we've defined in ../src/fbml.cpp called
 * fbml_node_batch_precache.  This function marshalls the one argument (which is a
 * PHP resource wrapping a node_res *) over to C space, passes the buck to the
 * fbml_node_batch_precache function, and then marshall its return value into
 * a PHP array mapping tags to the arrays of node with that tag type.
 *
 * @param first-arg a PHP resource tracking the root of the FBML tree of
 *                  interest.  The PHP resource actually wraps a node_res *,
 *                  which is bound to the local variable called 'node_res'.
 *
 */

PHP_FUNCTION(fbml_batch_precache)
{
  zval *r;
  struct node_res *node_res;
  struct fbml_node **node_iter;
  struct fbml_precache_bunch **iter;

  zval *nodes;
  zval *node_z;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  struct fbml *node = node_res->node;
  zval *tree = node_res->tree;

  struct fbml_precache_bunch **node_bunch = fbml_node_batch_precache(node);

  array_init(return_value);
  for (iter = node_bunch; *iter; iter++) {
    MAKE_STD_ZVAL(nodes);
    array_init(nodes);
    for (node_iter = (*iter)->nodes; *node_iter ; node_iter++) {
      node_z = register_new_node(*node_iter, tree);
      add_next_index_zval(nodes, node_z);
    }
    add_assoc_zval(return_value, (*iter)->tag, nodes);
    fbml_node_bunch_free(*iter);
  }

  free(node_bunch);
  return;
}

PHP_FUNCTION(fbml_batch_precache_11)
{
  zval *r;
  struct node_res *node_res;
  struct fbml_node **node_iter;
  struct fbml_precache_bunch **iter;

  zval *nodes;
  zval *node_z;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r) == FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *,
		      &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  struct fbml *node = node_res->node;
  zval *tree = node_res->tree;

  struct fbml_precache_bunch **node_bunch = fbml_node_batch_precache(node);

  array_init(return_value);
  for (iter = node_bunch; *iter; iter++) {
    MAKE_STD_ZVAL(nodes);
    array_init(nodes);
    for (node_iter = (*iter)->nodes; *node_iter ; node_iter++) {
      node_z = register_new_node(*node_iter, tree);
      add_next_index_zval(nodes, node_z);
    }
    add_assoc_zval(return_value, (*iter)->tag, nodes);
    fbml_node_bunch_free(*iter);
  }

  free(node_bunch);
  return;
}

/**
 * Generates the full rendering (compilation + serialization) of an entire
 * FBML tree.  The root of this FBML tree is supplised as the first argument
 * and ultimately bound to a local variable called 'node_res'.
 *
 * The third and fourth arguments to fbml_render_children are
 * the names of PHP userspace functions that know how to render FB nodes and
 * traditional HTML nodes that we've marked as special (meaning they require
 * special treatment.
 *
 * @param first-arg the PHP resource node tracking the root of the FBML tree
 *                  of interest.  It ultimately becomes bound to the local variable
 *                  called 'node'.
 * @param second-arg auxilary data that should be passed as the second argument to the
 *                   callback functions.  This argument is bound to the local variable
 *                   called 'data'.
 * @param third-arg the name of the PHP function that should be called on behalf of every
 *                  HTML node requiring special treatment.  The name of this function
 *                  is placed in the local char * variable called 'html_f_name'.
 * @param fourth-arg the name of the PHP function that should be called on behalf of every
 *                   FBNode node (i.e. those nodes corresponding to <fb:name>, <fb:profile-pic>,
 *                   and so forth.)
 * @return the full rendering of the tree as constructed by the inorder mapping and application
 *         of callback to construct sub-renderings that are all merged together to form one
 *         big rendering.
 */

PHP_FUNCTION(fbml_render_children)
{
  zval *r;
  zval *data;
  char *html_f_name, *fb_f_name;;
  int html_f_len, fb_f_len;
  long internal_mode = 0 ;
  struct node_res *node_res;
  struct fbml_node *node;

  char * result;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rzss|l",
			    &r, &data,
			    &html_f_name, &html_f_len,
			    &fb_f_name, &fb_f_len,
			    &internal_mode)== FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  node = node_res->node;

  struct node_callback_parameter html;
  html.tree = node_res->tree;
  html.user_func_name = html_f_name;
  html.user_data = data;
  html.fallback = "";

  struct node_callback_parameter fb;
  fb.tree = node_res->tree;
  fb.user_func_name = fb_f_name;
  fb.user_data = data;
  fb.fallback = "";

  struct fbml_node_renderer node_rend;
  node_rend.fb_node_data = &fb;
  node_rend.html_node_data = &html;
  node_rend.pfunc_renderer = callback_node_function;
  node_rend.node = node;

  result = fbml_node_render_children(node, internal_mode, &node_rend);
  ZVAL_STRING(return_value, result, 1);
  free(result);
}

PHP_FUNCTION(fbml_render_children_11)
{
  zval *r;
  zval *data;
  char *html_f_name, *fb_f_name;;
  int html_f_len, fb_f_len;
  long internal_mode = 0 ;
  struct node_res *node_res;
  struct fbml_node *node;

  char * result;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rzss|l",
			    &r, &data,
			    &html_f_name, &html_f_len,
			    &fb_f_name, &fb_f_len,
			    &internal_mode)== FAILURE) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  node = node_res->node;

  struct node_callback_parameter html;
  html.tree = node_res->tree;
  html.user_func_name = html_f_name;
  html.user_data = data;
  html.fallback = "";

  struct node_callback_parameter fb;
  fb.tree = node_res->tree;
  fb.user_func_name = fb_f_name;
  fb.user_data = data;
  fb.fallback = "";

  struct fbml_node_renderer node_rend;
  node_rend.fb_node_data = &fb;
  node_rend.html_node_data = &html;
  node_rend.pfunc_renderer = callback_node_function;
  node_rend.node = node;

  result = fbml_node_render_children(node, internal_mode, &node_rend);
  ZVAL_STRING(return_value, result, 1);
  free(result);
}

/**
 * Pulls the text out of the first argument, which is assumed to be a text node
 * and returns it.  If the node isn't a text node, then NULL is returned instead.
 *
 * @param first-arg a PHP resource which is really a node_res * in disguise.  The addressed
 *                  node_res contains a fbml_node *, and that's the fmbl_node * of interest.
 * @return the text contained by the root node of the supplied FBML tree, or NULL if the
 *         root isn't text node.
 */

PHP_FUNCTION(fbml_get_text)
{
  zval *r;
  struct node_res *node_res;
  struct fbml_node *node;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  node = node_res->node;

  if (node->text) {
    ZVAL_STRING(return_value, node->text, 1);
  } else {
    RETURN_NULL();
  }
}

PHP_FUNCTION(fbml_get_text_11)
{
  zval *r;
  struct node_res *node_res;
  struct fbml_node *node;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &r)) return;

  ZEND_FETCH_RESOURCE(node_res, struct node_res *, &r, -1, NODE_RESOURCE_NAME, node_descriptor);
  node = node_res->node;

  if (node->text) {
    ZVAL_STRING(return_value, node->text, 1);
  } else {
    RETURN_NULL();
  }
}
