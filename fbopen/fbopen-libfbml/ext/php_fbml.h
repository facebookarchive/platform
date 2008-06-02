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

#ifndef PHP_FBML_H
#define PHP_FBML_H

extern zend_module_entry fbml_module_entry;
#define phpext_fbml_ptr &fbml_module_entry

#define PHP_FBML_API

#ifdef ZTS
#include "TSRM.h"
#endif

/**
 * Defines the suite of functions that get called when
 * the libfbml module is imported by Apache.
 */

PHP_MINIT_FUNCTION(fbml);
PHP_MSHUTDOWN_FUNCTION(fbml);
PHP_RINIT_FUNCTION(fbml);
PHP_RSHUTDOWN_FUNCTION(fbml);
PHP_MINFO_FUNCTION(fbml);

/**
 * The following list defines the set of PHP functions
 * exported so that client code can parse, sanitize, precache,
 * and render FBML.  This list defines the updated names
 * being introduced with the 1.2.0 release.
 */

PHP_FUNCTION(fbml_is_parser_configured);
PHP_FUNCTION(fbml_configure_parser);
PHP_FUNCTION(fbml_parse);

PHP_FUNCTION(fbml_sanitize_css);
PHP_FUNCTION(fbml_sanitize_js);
PHP_FUNCTION(fbml_get_tag_name);
PHP_FUNCTION(fbml_to_string);
PHP_FUNCTION(fbml_get_children);
PHP_FUNCTION(fbml_get_children_count);
PHP_FUNCTION(fbml_get_children_by_name);
PHP_FUNCTION(fbml_get_attributes);
PHP_FUNCTION(fbml_get_attribute);

PHP_FUNCTION(fbml_attr_to_bool);
PHP_FUNCTION(fbml_attr_to_color);
PHP_FUNCTION(fbml_get_text);
PHP_FUNCTION(fbml_precache);
PHP_FUNCTION(fbml_batch_precache);
PHP_FUNCTION(fbml_render_children);
PHP_FUNCTION(fbml_flatten);

/**
 * PHP functions analogous to all those listed above.  These
 * are all the names used by the 1.1.x releases, and we're
 * keeping them during the 1.2.0 release to help ease the transition
 * from 1.1 to 1.2.  The function named are being updated
 * to exclude versioning (so no more _11), and also because some
 * of the 1.1 function names are a little confusing.
 */

PHP_FUNCTION(fbml_tag_list_expanded_11);
PHP_FUNCTION(fbml_complex_expand_tag_list_11);
PHP_FUNCTION(fbml_parse_opaque_11);

PHP_FUNCTION(fbml_sanitize_css_11);
PHP_FUNCTION(fbml_sanitize_js_11);
PHP_FUNCTION(fbml_get_tag_name_11);
PHP_FUNCTION(fbml_get_children_11);
PHP_FUNCTION(fbml_get_children_count_11);
PHP_FUNCTION(fbml_get_children_by_name_11);
PHP_FUNCTION(fbml_get_attributes_11);
PHP_FUNCTION(fbml_get_attribute_11);
PHP_FUNCTION(fbml_get_line_number);

PHP_FUNCTION(fbml_attr_to_bool_11);
PHP_FUNCTION(fbml_attr_to_color_11);
PHP_FUNCTION(fbml_get_text_11);
PHP_FUNCTION(fbml_precache_11);
PHP_FUNCTION(fbml_batch_precache_11);
PHP_FUNCTION(fbml_render_children_11);
PHP_FUNCTION(fbml_flatten_11);

/**
 * In every utility function you add that needs to use variables
 * in php_fbml_globals, call TSRMLS_FETCH(); after declaring other
 * variables used by that function, or better yet, pass in TSRMLS_CC
 * after the last function argument and declare your utility function
 * with TSRMLS_DC after the last declared argument.  Always refer to
 * the globals in your function as FBML_G(variable).  You are
 * encouraged to rename these macros something shorter, see
 * examples in any other php module directory.
 */

#ifdef ZTS
#define FBML_G(v) TSRMG(fbml_globals_id, zend_fbml_globals *, v)
#else
#define FBML_G(v) (fbml_globals.v)
#endif

#endif	/* PHP_FBML_H */

