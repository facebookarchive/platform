<?php

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


// Facebook Copyright 2006 - 2008

/**
 * Schema are static collections of tags that are allowed in a context.
 * They replace flavors to take care of the basic FBML permissioning.
 *
 * If, for instance, we involve the 'mock_ajax' schema, then we expressly
 * forbid '_clickrewriteurl', '_clickrewriteid', and '_clickrewriteaction'
 * from surviving in an FBML tree that respects the schema.  This is encoded
 * as:
 *
 *   'mock_ajax' => array('_clickrewriteurl',
 *                        '_clickrewriteid',
 *                        '_clickrewriteform');
 *
 * The particular encoding maps a scheme key to an array of actual tag names,
 * but some schema can map to an array of sub-schemas and tag names, and those
 * sub-schemas might themselves map to array of sub-sub-schemas and tag names,
 * and so forth.  The full blacklist of tags for any particular schema key is
 * equivalent to the full transitive closure of all those tag names that are
 * reachable from it.  So, consider the following schema set:
 *
 *   'linkbreaks' => array('block_level_elements', 'br')
 *   'block_level_elements' => array('hr', 'address', 'div', 'h1', 'h2',
 *                                   'h3', 'h4', 'h5', 'h6', 'p', 'blockquote')
 *
 * The full blacklist associated with the 'linebreaks' schema includes 'br' and
 * all of the tags listed in the array attached to 'block_level_elements' key.
 * Of course, more elaborate transitive closures are possible, provided true
 * tag names (i.e. 'br' and 'div') can't be the names of schemas.
 */

$schema_tree =
    array(
          'default' => array('linebreaks', 'interactivity',
                             'htmls' , 'fb_misc','internal', 'badhtml'),

          'htmls' => array('tables', 'bdo', 'bold', 'underline',
                           'italics', 'lists', 'phrases', 'strikethru',
                           'spans', 'css', 'forms',
                           'links', 'comments_macro', 'images', 'fb_html',
                           'cssincludes', 'font'),

          'linebreaks' => array('block_level_elements', 'br'),

          'cssincludes' => array('link', 'meta'),

          'block_level_elements' => array('hr', "address", "div", 'h1', 'h2',
                                          'h3', 'h4', 'h5', 'h6', 'p', 'blockquote'),

          'css' => array( 'style', '_style', '_imgstyle'),

          'interactivity' => array('mock_ajax', 'dialog', 'flash',
                                   'script', 'script_onload', 'interactivity_fb',
                                   '_clicktohide', '_clicktoshow'),

          'mock_ajax' => array('_clickrewriteurl',
                               '_clickrewriteid',
                               '_clickrewriteform'),

          'flash' => array('flash_autoplay', 'fb:swf', 'fb:mp3', 'fb:flv'),

          // This is the list of HTML tags that aren't permitted in FBML.
          'badhtml' => array('body', 'base', 'iframe', 'applet', 'area', 'bgsound', 'blink',
                             'char', 'colgroup', 'comment', 'dir', 'embed',
                             'frame', 'frameset', 'head', 'html', 'hx',
                             'ilayer', 'inlineinput', 'isindex', 'keygen', 'layer',
                             'listing', 'marquee', 'map', 'menu', 'multicol',
                             'nextid', 'nobr', 'noembed', 'noframes', 'nolayer', 'noscript',
                             'object', 'plaintext', 'param', 'rt', 'ruby',
                             'samp', 'sound', 'spacer', 'spell',
                             'wbr', 'xml', 'xmp'
                             ),

          'tables' => array('table', 'th', 'tr', 'td',
                            'caption', 'thead', 'tbody', 'tfoot', 'fb:user_table'),

          'italics' => array('var', 'cite', 'dfn', 'i'),

          'lists' => array('dl', 'dd', 'dt', 'li', 'ol', 'ul'),

          'underline' => array('ins', 'u'),

          'phrases' => array('cite', 'dfn', 'em','kbd',
                             'code', 'samp', 'abbr', 'acronym',
                             'strong', 'var'),

          'spans' => array('span', 'abbr', 'acronym'),

          'bold' => array('strong', 'em', 'b'),

          'strikethru' => array('del', 's'),

          'forms' => array('input', 'form', 'option', 'optgroup',
                           'select', 'textarea', 'label', 'fieldset',
                           'legend', 'fb:friend_selector',
                           'fb:multi_friend_input', 'fb:request_form',
                           'fb:submit',),

          'links' => array('a', 'fb:userlink', 'fb:grouplink', 'fb:eventlink',
                           'fb:networklink', 'fb:user_table'),

          'images' => array('img','fb:photo','fb:profile_pic', 'fb:header',
                            'fb:dashboard', 'fb:mediaheader', 'fb:user_table',
                            'fb:wallpost', 'fb:images'),

          'actor_tags' => array('fb:if_multiple_actors'),

          'comments_macro' => array(),

          'names' => array('fb:name', 'fb:user_table'),

          'iframes' => array('fb:iframe'),

          'redirects' => array('fb:redirect'),

          'interactivity_fb' => array('fb:friend_selector',
                                      'fb:multi_friend_input',
                                      'fb:request_form'),

          'fb_html' => array('fb:header','fb:dashboard', 'fb:mediaheader',
                             'fb:user_table', 'fb:tabs', 'fb:error', 'fb:success', 'fb:explanation', 'fb:editor'),

          'share_button' => array('fb:share_button'),

          // internal tags
          'internal' => array('headers'),

          'intls' => array('fb:intl' ),
          'headers' => array('fb:start_page', 'fb:close_page'),

          //not grouped (call by name)
          'fb_misc' => array('fb:message_preview', 'fb:attachment_preview',
                             'fb:wall_attachment_img','fb:comments',
                             'fb:google_analytics', 'fb:random',
                             'fb:dialog', 'fb:dialogresponse')
          );

/**
 * Compiles a list of all those HTML and FBML tags that
 * are reachable from the schemas names in the 'forbidden'
 * array but not reachable from the 'pardoned' array.
 *
 * @param forbidden an array of schema names, i.e. 'badhtml',
 *                  'default', 'fb_misc', etc.
 * @param pardoned a second array of schema names.
 * @return a dictionary of all those FBML and HTML tags that
 *         are reachable from the 'forbidden' set but not reachable
 *         from the 'pardoned' set.
 */

function schema_compute_from_tree($forbidden, $pardoned)
{
  global $schema_tree;
  $stack = $forbidden;
  $tags = array();

  while (!empty($stack)) {
    $top = array_pop($stack);
    if (isset($schema_tree[$top])) { // is it a schema name?
      if (!isset($pardoned[$top])) { // is it among the pardoned?
        $stack = array_merge($stack, $schema_tree[$top]);
      }
    } else { // not a schema name? then it's a real tag
      $tags[$top] = $top;
    }
  }

  return $tags;
}

/**
 * Simple class to manage the construction of an in-memory
 * model of the schemas as they're described above.  Fundamentally,
 * these FBMLSchema function as blacklists, explicitly storing
 * each and every tag that's *forbidden* from surviving the
 * FBML->HTML compilation process.
 */

class FBMLSchema {

  public $forbidden;
  public $pardoned;
  public $tags;

  /**
   * Constructs an FBMLSchema instance out of the 'forbidden' and 'pardoned'
   * arrays.  When the 'pardoned' array is empty, the FBMLSchema is
   * the union of the transitive closures of the schema names included
   * in the 'forbidden' array.  When 'pardoned' is not empty, then the FBMLSchema
   * is still the full transitive closure of all the schema names, except
   * that those in the full transitive closure of the 'pardoned' schema
   * names aren't included in the forbidden set of tag names.
   *
   * @param forbidden the set of schema tags that lead to the full set of
   *                  HTML and FBML tags that should be excluded from
   *                  FBML trees--that is, placed on the black list.
   * @param pardoned the set of schema tags identifying those HTML and
   *                 FBML tags that should be spared from the blacklist.
   */

  function __construct($forbidden, $pardoned)
  {
    $this->forbidden = $forbidden;
    $this->pardoned = $pardoned;
    $this->tags = schema_compute_from_tree($forbidden, $pardoned);
  }
}

/**
 * Quick and dirty function that takes the list of arguments (there
 * can be any number of them) and creates an associative array
 * where each argument maps to itself.  The arguments that *are*
 * passed in need to be strings, since they serve as keys in the
 * associative array that gets built.
 *
 * @return an associative array containing as keys all of those
 *         strings passed in as arguments.  Each key maps to itself.
 */

function schema_make_set()
{
  $args = array();
  foreach (func_get_args() as $ele) {
    $args[$ele] = $ele;
  }
  return $args;
}

/**
 * Constructs a new FBMLSchema out of the union of the specified
 * schema and the schema supplied by the additional
 * 'forbidden' and 'pardoned' parameters.  It's as if the FBMLSchema
 * that's returned is equal to the old schema, except that its blacklist
 * has been extended to include some new tags and pardon some others.
 *
 * @param old_schema the schema on which the extended schema is based.
 * @param forbidden the set of additional schema names identifying additional
 *                  tags that should be blacklisted.
 * @param pardoned the set of additional schema names identifying additional
 *                 tags that should be spared from the blacklist.
 */

function schema_extend($old_schema, $forbidden, $pardoned = array())
{
  return new FBMLSchema(array_merge($old_schema->forbidden, $forbidden),
                        $old_schema->pardoned + $pardoned);
}

/**
 * The next several functions each construct a schema
 * used for the compilation of FBML in some context.
 * One generates a schema used for compilation of FBML
 * for profile boxes, another for canvas pages, and so forth.
 * All of them return an instance of an FBMLSchema class.
 *
 * In all cases, the return value is some FBMLSchema instance
 * dictating which tags survive and which ones don't.
 */

function schema_everything_allowed()
{
  return new FBMLSchema(array('badhtml'), array());
}

function schema_nothing_allowed()
{
  return new FBMLSchema(array('default'), array());
}

function schema_facebook_internal()
{
  return schema_everything_allowed();
}

function schema_share_button()
{
  return new FBMLSchema(array(), array());
}

function schema_canvas()
{
  return schema_extend(schema_everything_allowed(),
                       array('internal'),
                       array());
}

function schema_title()
{
  return schema_extend(schema_nothing_allowed(),
                       array(),
                       schema_make_set('names','spans','visible_to'));
}

function schema_subtitle()
{
  return schema_extend(schema_title(),
                       array(),
                       schema_make_set('links'));
}

function schema_profile_action()
{
  return schema_extend(schema_nothing_allowed(),
                       array(),
                       schema_make_set('names','ifs'));
}

function schema_notification()
{
  return schema_extend(schema_nothing_allowed(),
                       array(),
                       schema_make_set('names','links', 'bold', 'italics'));
}

function schema_alerts()
{
  return schema_extend(schema_notification(),
                       array(),
                       schema_make_set('linebreaks'));
}

function schema_requests()
{
  return schema_extend(schema_profile_action(),
                       array(),
                       schema_make_set('links', 'bold', 'italics'));
}

function schema_product_directory()
{
  return schema_extend(schema_profile_action(),
                       array(),
                       schema_make_set('links', 'bold', 'italics', 'linebreaks', 'underline', 'htmls'));
}


function schema_feed_title()
{
  return schema_extend(schema_nothing_allowed(),
                       array(),
                       schema_make_set('links', 'actor_tags'));
}

function schema_feed_body()
{
  return schema_extend(schema_nothing_allowed(),
                       array(),
                       schema_make_set('links', 'italics', 'bold',
                                       'actor_tags' ));
}

function schema_profile()
{
  return schema_extend(schema_everything_allowed(),
                       array('fb:google_analytics',
                             'cssincludes',
                             'internal'),
                       schema_make_set());
}

function schema_mobile()
{
  return schema_extend(schema_nothing_allowed(),
                       array(),
                       schema_make_set('names', 'htmls', 'linebreaks', 'links',
                                       'images', 'fb:random'));
}

/**
 * Constructs a map of all the various schemas that can come up
 * during FBML compilation.
 *
 * @return an associative array of all of the various schemas ever used
 *         during the compilation of FBML.  'fb:canvas', for instance, maps
 *         to the schema used to guide FBML compilation and rendering for
 *         canvas pages, 'fb:profile' for profile pages, and so forth.
 */

function schema_get_schema()
{
  $fbml_schema =
    array(
          'fb:internal'           => schema_everything_allowed()->tags,
          'fb:canvas'             => schema_canvas()->tags,
          'fb:title'              => schema_title()->tags,
          'fb:subtitle'           => schema_subtitle()->tags,
          'fb:profile_action'     => schema_profile_action()->tags,
          'fb:profile'            => schema_profile()->tags,
          'fb:mobile'             => schema_mobile()->tags,
          'fb:request-position'   => schema_requests()->tags,
          'fb:notification-position' => schema_notification()->tags,
          'fb:feed-title-position'=> schema_feed_title()->tags,
          'fb:feed-body-position' => schema_feed_body()->tags,
          'fb:share-button'       => schema_share_button()->tags,
          'fb:product-directory'  => schema_product_directory()->tags,
          'fb:calendar'           => schema_profile()->tags,
          );
  return $fbml_schema;
}

