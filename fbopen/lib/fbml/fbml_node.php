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

include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/fbml_exception.php";

/**
 * PHP function that gets invoked on behalf of those HTML
 * nodes that have special rendering needs.  Most tags
 * can be rendered using a standard HTML rendering object,
 * but some HTML tags require special handling beyond the
 * default, and this function is designed to do that.
 *
 * @param impl the class implementing the collection of methods
 *             that know how to render special HTML nodes.
 * @param node the HTML node being rendered.
 */

function render_html_node($impl, $node)
{
  $html_node = new FBMLNode($node);
  $tag_name = fbml_get_tag_name_11($node);
  $open_method = 'open_' . $tag_name;
  $tag_method = 'tag_' . $tag_name;

  try {
    if (method_exists($impl, $tag_method))  {
      return $impl->$tag_method($html_node);
    } else if (method_exists($impl, $open_method)) {
      $html = $impl->$open_method($html_node);

      if ($html !== false) {

        $html .= $html_node->render_children($impl);
        $html .= $impl->get_html_renderer()->node_close($html_node);
      }
      return $html;
    } else if ($tag_name) {
      $impl->add_error('HTML tag not supported: "' . $tag_name . '"');
    }
  } catch (FBMLException $e) {
    $impl->add_error('HTML error while rendering tag "' . $tag_name . '": ' .
                     $e->get_fbml_error_text());
  }

  return ''; // default return value
}

/**
 * Callback function dedicated to compiling and rendering
 * all registered FBML tag types.  If an FBML node with
 * tag name "fb:some-tag-type" is registered as something we support,
 * then the FBML implementation (an instance of which is passed in
 * as the first argument to render_fb_node) really needs to define
 * and implement a one-argument method called fb_some_tag_type.
 *
 * @param impl an instance of some FBML implementation class that
 *             knows how to render FBML nodes to text.
 * @param node a PHP resource representing some raw C/C++ node in the
 *             parse tree.
 * @return an HTML rendering of the FBML node.
 */

function render_fb_node($impl, $node)
{
  $fbml_node = new FBMLNode($node);
  $name = fbml_get_tag_name_11($node);
  $method_name = $impl->macro_method($name);

  try {
    if (method_exists($impl, $method_name)) {
      return $impl->$method_name($fbml_node);
    } else {
      $impl->add_error('FBML tag "' . $name . '" not supported in this version. ');
    }
  } catch (FBMLException $e) {
    $impl->add_error($name . ': ' . $e->get_fbml_error_text());
  }

  return ''; // return something after registering the error
}

/**
 * Change this when you change the parser in a way that requires that all cached parse trees be dirtied
 * @return   string   Current version of FBML parser
 *
 */
function fbml_parser_version() {
  return '1';
}
/**
 * PHP class used to model all nodes in an FBML tree.
 */

class FBMLNode {

  /**
   * Constructs the freshly allocated FBMLParseNode
   * to encapsulate the FBML tree rooted at the
   * specified node.
   *
   * @param $node the root of some FBML tree.
   */

  public function __construct($node)
  {
    $this->_node = $node;
  }

  /**
   * Returns the serialization of the entire FBML tree
   * managed by the receiving FBMLNode instance.
   *
   * @return a PHP string storing the full serialization of the
   *         FBML tree managed by this receiving FBMLNode instance.
   */

  public function __toString()
  {
    return fbml_flatten_11($this->_node);
  }

  /**
   * Returns the name associated with receiving FBMLNode.
   * If, for instance, the receiving FBML exists to represent
   * <img src="http://www.facebook.com/someimage.png"/>, the
   * get_tag_name method would return "img".
   *
   * Note that the tag name is (or at least should be) constant,
   * so we only call the C library function fbml_get_tag_name
   * the very first time this get_tag_name method is called.
   * All subsequent calls to get_tag_name can just return the
   * cached value.
   *
   * @return the tag name associated with the root of the FBML
   *         tree managed by the receiving FBMLNode instance.
   */

  public function get_tag_name()
  {
    if (!isset($this->_name))
      $this->_name = fbml_get_tag_name_11($this->_node);
    return $this->_name;
  }

  /**
   * Returns a PHP associative array mapping all of the receiving
   * FBMLNode's attributes to their raw text values.  So, the FBML
   * managing the following:
   *
   *     <div id="banner" class="banner" color="white">
   *
   * would return:
   *
   *     Array
   *     (
   *        [id] => banner
   *        [class] => banner
   *        [color] => white
   *     )
   *
   * @return a PHP associative array mapping the receiving FBMLNode's
   *         attributes to their values, as illustrated above.
   */

  public function get_attributes()
  {
    if (!isset($this->_attributes))
      $this->_attributes = fbml_get_attributes_11($this->_node);
    return $this->_attributes;
  }

  /**
   * Compiles the FBML managed by the receiving FBMLNode to pure HTML,
   * where the compilation of traditional HTML tags and FBML extension
   * tags is managed by the supplied FBMLImplementation called 'fbml_impl'.
   *
   * @param fbml_impl the FBMLImplementaion that knows how to translate and serialize
   *                  all supported HTML and FBML tags to pure HTML.
   * @return the full serialization of the compilation of the FBML tree managed
   *         by the receiving FBMLNode.
   */

  public function render_children($fbml_impl)
  {
    return fbml_render_children_11($this->_node, $fbml_impl,
                                   "render_html_node", "render_fb_node", false);
  }

  /**
   * Returns the rendered HTML for this parse tree
   */
  public function render_html($fbml_impl) {
    $html = $this->render_html_without_style($fbml_impl);
    if (isset($fbml_impl->_render_state['style'])) {
      $html = '<style type="text/css">' . $fbml_impl->_render_state['style'] . '</style>' . $html;
    }

    return $html;
  }


  /**
   * Recursively compiles the FBML tree rooted at the
   * receiving FBMLNode to pure HTML, and returns the
   * HTML product of the compilation as a PHP string.
   * The manner in which each FBML node gets translated
   * is dictated almost completely by the supplied
   * FBML implementation, which absolutely must respond
   * to prerender, render_children, postrender, and
   * add_error methods.
   *
   * @param fbml_impl the FBML implementation dictating how
   *        FBML tags and special HTML tags should be rendered.
   * @return the full HTML rendering of the FML tree.
   */


  public function render_html_without_style($fbml_impl)
  {
    $html = '';
    $html .= $fbml_impl->prerender($this, false);
    $html .= $fbml_impl->render_children($this);
    try {
      return $html . $fbml_impl->postrender($this);
    } catch (FBMLException $e) {
      $fbml_impl->add_error($e->get_fbml_error_text());
      return $html;
    }
  }

  /**
   * Maps over and applies precaching functionality against
   * all of those HTML nodes marked as having precaching needs.
   * Note that fbml_batch_precache_11 returns an assoc that maps
   * tag names to array of nodes.  An 'img' key, if in the assoc,
   * would map to an array of img nodes.  An 'fb:name' key, if
   * present, would map to an array of those FBML nodes in the
   * tree that are <fb:name> nodes.
   *
   * @param fbml_impl the FBML implementation that knows how to
   *                  apply precache functionality against the
   *                  set of all those FBML nodes that have been
   *                  marked as having precache needs.
   */

  public function precache($fbml_impl)
  {
    $table = fbml_batch_precache_11($this->_node);
    foreach ($table as $tag => $nodes) {
      $new_nodes = array();
      $method_name = 'batch_precache_' .
        str_replace('-', '_', str_replace(':', '_' , $tag));
      foreach ($nodes as $n)
        $new_nodes[] = new FBMLNode($n);
      try {
        $fbml_impl->$method_name($new_nodes);
      } catch (FBMLException $ignored_on_this_pass) {}
    }
  }

  /**
   * Returns all of the receiving FBMLNode's immediate children whose
   * tag name happens to be the one supplied via the 'name' parameter.
   * It's similar to the get_children() method below, except this one
   * filters by tag name.
   *
   * @param name an arbitrary tag name.
   * @return an array of the receiving FBMLNode's immediate children
   *         with the specified tag type.  The array is an ordered
   *         array of FBMLNodes, where each FBMLNode encapsulates
   *         one of the children.
   */

  public function get_children_by_name($name)
  {
    $children = array();
    $c_children = fbml_get_children_by_name_11($this->_node, $name);
    foreach ($c_children as $c_node )
      $children[] = new FBMLNode($c_node);
    return $children;
  }

  /**
   * Returns all of the receiving FBMLNode's immediate children,
   * each expressed as an FBMLNode as well.
   *
   * @return an array of the receiving FBMLNode's immediate children,
   *         each themselves stored as FBMLNodes.
   */

  public function get_children()
  {
    $fbml_children = array();
    $children = fbml_get_children_11($this->_node);
    foreach ($children as $node )
      $fbml_children[] = new FBMLNode($node);
    return $fbml_children;
  }

  /**
   * Retrieves the raw text associated with the named attribute, and
   * then replaces all <, >, and "s with corresponding HTML entities.
   *
   * @param attribute the name of some attribute presumably
   *                  included in the attribute list of the receiving
   *                  FBMLNode.
   * @param default the value that should be returned if the attribute
   *                isn't required but isn't in the attribute list of the
   *                receiving FBMLNode.
   * @param required true if and only if the attribute is required.
   * @return the sanitized value attached to the named attribute, or
   *         whatever 'default' is if the attribute isn't in the
   *         attribute list of the receiving FBMLNode but isn't required.
   * @exception FBMLException thrown whenever the named attribute is
   *            required but isn't present.
   */

  public function attr($attribute, $default = null, $required = false)
  {
    $ret = $this->attr_raw($attribute, $default, $required);
    if ($ret === $default) return $default;
    return $this->make_html_safe($ret);
  }

  /**
   * Extracts and returns the named attribute from the
   * receiving FBMLNode.  Normally, the attribute value
   * is an integer, although we allow other values with
   * the expectation that the supplied FBMLImplementation
   * knows how to generate a legitimate id from the
   * special value.
   *
   * @param attribute the name of some FBML attribute.
   * @param impl the FMBL implementation that knows how
   *             to replace special attribute values with
   *             meaningful ids.
   * @param required true if and only if the attribute must
   *                 be in the attribute list of the receiving
   *                 FBML node.
   * @param default the value we can use if the named attribute is
   *                neither required nor present.
   * @return an id, which is either the numeric value attached to
   *         the named attribute, or the number generated from the
   *         special value associated with the named attribute.
   * @exception FBMLException thrown whenever the named attribute is
   *            required but isn't present.
   */

  public function attr_id($attribute, $impl,
                          $required = true, $default = null)
  {
    $id = $this->attr_raw($attribute, $default, $required);
    return $impl->get_substituted_attr_id($id);
  }

  /**
   * Retrieves the raw values (assumed to be a comma-separated
   * list of integer uids) attached to the specified attribute, and
   * and marshals it to an associative array where the uids are the
   * keys and all the values are 1.
   *
   * @param attribute the name of some FBML attribute.
   * @param impl the FMBL implementation that knows how
   *             to replace special attribute values with
   *             meaningful ids.
   * @param required true if and only if the attribute must
   *                 be in the attribute list of the receiving
   *                 FBML node.
   * @return an associative array containing as keys all those ids listed
   *         in the value of the named attribute (the keys all map to
   *         true, and the trues are more or less irrelevant).
   */

  public function attr_ids($attribute, $impl, $required = true)
  {
    $id_strings = $this->attr_raw($attribute, null, $required);
    if (!$id_strings) return array();
    $id_string_array = explode(',', $id_strings);

    $id_set = array();
    foreach ($id_string_array as $id_string) {
      $id = $impl->get_substituted_attr_id($id_string);
      if ($id) $id_set[$id] = true;
    }

    return $id_set;
  }

  /**
   * Retrieves the raw value (assumed to be the string form of a
   * pure integer) attached to the specified attribute.
   *
   * @param attribute the name of some attribute presumably
   *                  included in the attribute list of the receiving
   *                  FBMLNode.  The value is assumed to be the string
   *                  form of an integer, i.e. "144".
   * @param default the value that should be returned if the attribute
   *                isn't required but isn't in the attribute list of the
   *                receiving FBMLNode.
   * @param required true if and only if the attribute is required.
   * @return the integer value attached to the named attribute, or
   *         whatever 'default' is if the attribute isn't in the
   *         attribute list of the receiving FBMLNode but isn't required.
   * @exception FBMLException thrown whenever the named attribute is
   *            required but isn't present.
   */

  public function attr_int($attribute, $default = 0,
                           $required = false,
                           $min = null, $max = null)
  {
    $int = $this->attr_raw($attribute, $default, $required);
    if ($int === $default) return $default;

    if (!is_numeric($int))
      throw new FBMLException('Int attribute "'. $attr .
                              '" is not an integer: "'. $int .'"');

    if (isset($min) && $int < $min) {
      $int = $min;
    } else if (isset($max) && $int > $max) {
      $int = $max;
    }

    return (int) $int;
  }

  /**
   * Retrieves the raw value (assumed to be the string form of a
   * floating point number) attached to the specified attribute.
   *
   * @param attribute the name of some attribute presumably
   *                  included in the attribute list of the receiving
   *                  FBMLNode.  The value is assumed to be the string
   *                  form of a floating point value, i.e. "3.14159".
   * @param default the value that should be returned if the attribute
   *                isn't required but isn't in the attribute list of the
   *                receiving FBMLNode.
   * @param required true if and only if the attribute is required.
   * @return the float attached to the named attribute, or
   *         whatever 'default' is if the attribute isn't in the
   *         attribute list of the receiving FBMLNode but isn't required.
   * @exception FBMLException thrown whenever the named attribute is
   *            required but isn't present.
   */

  public function attr_float($attribute, $default=0, $required=false)
  {
    $float = $this->attr_raw($attribute, $default, $required);
    if ($float === $default)
      return $float;

    if (!is_numeric($float))
      throw new FBMLException('Float attribute ' . $attribute .
                              '" is not a number: "' . $float . '"');

    return (float) $float;
  }

  /**
   * Retrieves the raw text associated with the named attribute.
   *
   * @param attribute the name of some attribute presumably
   *                  included in the attribute list of the receiving
   *                  FBMLNode.
   * @param default the value that should be returned if the attribute
   *                isn't required but isn't in the attribute list of the
   *                receiving FBMLNode.
   * @param required true if and only if the attribute is required.
   * @return the unmodified value attached to the named attribute, or
   *         whatever 'default' is if the attribute isn't in the
   *         attribute list of the receiving FBMLNode but isn't required.
   * @exception FBMLException thrown whenever the named attribute is
   *            required but isn't present.
   */

  public function attr_raw($attribute, $default = null, $required = false)
  {
    $val = fbml_get_attribute_11($this->_node, $attribute);
    if ($val === null) {
      if ($required) {
        throw new FBMLException('Required attribute "' .
                                $attribute . '" not found in node ' .
                                $this->get_tag_name());
      }
      return $default;
    }

    return $val;
  }

  /**
   * Returns the Boolean value attached the named attribute.  Of course,
   * attribute values can be free text, but the expectation here is that
   * the value is either purely numeric, or some case-insensitive variation
   * on "true", "false", "yes", or "no".
   *
   * @param attribute the name of some attribute residing in the attribute list
   *                  of the receiving FBMLNode.
   * @default the value that should be returned unmodified if the raw text value
   *          of the named attribute is this default value.
   * @required true if the named attribute absolutely should be present, and false
   *                if not.
   * @return 0 if the named attribute has a value logically equivalent to false,
   *         1 if the named attribute has a value logically equivalent to true,
   */

  public function attr_bool($attribute, $default = null, $required = false)
  {
    $bool = $this->attr_raw($attribute, $default, $required);
    if ($bool === $default) {
      return $bool;
    } else {
      return fbml_attr_to_bool_11($bool);
    }
  }

  /**
   * Returns the hexadecimal RGB encoding of whatever color value is
   * attached to the named attribute.  These color values can take
   * the form of any legitimate six-digit hexadecimal constant, preceded
   * by a hash character, or it can be any one of the common (or even
   * not so common) color names recognized by most browsers.
   *
   * @attribute the name of the attribute presumably attached to some
   *            value easily interpreted to be a color.
   * @default the value to return if the attribute isn't required and
   *          the value attached to the named attribute just happens to
   *          equal $default (even if it isn't a legitimate color value.)
   * @required true if and only if the specified attribute needs to be
   *           present, and false otherwise.
   * @return the color value associated with the named attribute.
   * @exception FBMLException thrown if the attribute is required but
   *            isn't present.
   */

  public function attr_color($attribute, $default = null, $required = false)
  {
    $val = $this->attr_raw($attribute, $default, $required);
    if ($val === $default) {
      return $val;
    } else {
      return fbml_attr_to_color_11($val);
    }
  }

  /**
   * Removes the ", < , and > symbols in html, but leaves & so URLs aren't
   * broken.
   *
   * @param htmlstring the html to convert
   * @return the new HTML string with all instances of ", >, and < removed
   *         and replaced by the corresponding HTML entities.
   */

  private function make_html_safe($htmlstring)
  {
    $ret = $htmlstring;
    $ret = str_replace('"',"&quot;",$ret);
    $ret = str_replace('<',"&lt;",$ret);
    $ret = str_replace('>',"&gt;",$ret);
    return $ret;
  }

  public function text_not_escaped() {
    return fbml_get_text_11( $this->_node );
  }

  private $_node;
  private $_name;
  private $_attributes;
}
