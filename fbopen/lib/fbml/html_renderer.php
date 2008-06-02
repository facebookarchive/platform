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
 * Straightforward class to manage the serialization of
 * attribute pairs and HTML nodes (actually, FBML nodes
 * that are assumed to represent traditional HTML nodes).
 */

class HTMLRenderer {

  /**
   * Simple utility method designed to return
   * whatever's passed in, except that &, ', ", <, and >
   * are replaced by the corresponding HTML entities.
   *
   * @param $attr_val the attribute value that should be
   *                  encoded.
   * @return the encoded version of the incoming attribute.
   */

  public function attr_encode($attr_val)
  {
    return htmlspecialchars($attr_val, ENT_QUOTES, 'UTF-8');
  }

  /**
   * Constructs and returns the serialization of the
   * supplied attribute/value pair.  Note that the
   * attribute is cleared of its &, >, <, ', and ", which
   * are replaced by the equivalent HTML entities.
   *
   * @param attr the name of the attribute.
   * @param val the value of the attribute being serialized.
   * @return the serialization of name/value pair, where the
   *         serialization takes the traditional form of:
   *
   *           <name>="<value>"
   */

  public function render_attr($attr, $val)
  {
    return $attr.'="'.$this->attr_encode($val).'"';
  }

  /**
   * Constructs the string representation of an HTML tag with
   * the specified name and list of attributes.
   *
   * @param tag_name the node's tag name, i.e. "a", "img", "div",
   *                 or "span".
   * @param attributes an associative array of attribute name/attribute
   *                   value pairs.
   * @return the full string represention of the open HTML tag having
   *         the specified tag and attribute list.
   */

  function render_html_open_tag($tag_name, $attributes)
  {
    $html = '<' . $tag_name;
    foreach ($attributes as $attr => $val) {
      $val = substr($this->attr_encode(' '.$val.' '), 1, -1);
      $html .= ' '.$attr.'="'.$val.'"';
    }
    $html .= '>';
    return $html;
  }

  /**
   * Self-explanatory method designed to balance the
   * corresponding open tag.
   *
   * @param tag_name the relevant tag type, i.e. "a", "img", "div", or "span".
   * @return a string of the form '</[tag-name]>', which serves to end the run
   *         of some other open tag.
   */

  function render_html_close_tag($tag_name)
  {
    return '</' . $tag_name . '>';
  }

  /**
   * Returns the serialization of a singleton HTML
   * node, which is the condensed form of an open and close tag in one
   * that can be used when a node doesn't have any children.
   *
   * @param tag_name the tag type of the node being serialized.
   * @param attributes the list of attributes that should be published
   *                   as part of the serialization.
   * @return the full serialization of the singleton tag, which can be
   *         expected to look like '<[tag-name] name1="value1" name2="value2" />'
   */

  function render_html_singleton_tag($tag_name, $attributes)
  {
    $html = '<' . $tag_name;
    foreach ($attributes as $attr => $val) {
      $val = substr($this->attr_encode(' '.$val.' '), 1, -1);
      $html .= ' '.$attr.'="'.$val.'"';
    }
    $html .= ' />';
    return $html;
  }

  /**
   * Returns the serialization of the open tag for the specified node.
   *
   * @param node the HTML node whose tag name and attribute list should
   *             be used to construct an open tag.
   * @return the full serialization of the open tag constructed out of
   *         information residing in the specified node.
   */

  public function node_open($node)
  {
    return $this->render_html_open_tag($node->get_tag_name(),
                                       $this->node_get_attributes($node));
  }

  /**
   * Returns the serialization of the close tag for the specified node.
   *
   * @param node the HTML node whose tag name get used to construct the
   *        close tag being returned.
   * @return the close tag of interest.
   */

  public function node_close($node)
  {
    if ($node->get_tag_name() !== null)
      return $this->render_html_close_tag($node->get_tag_name());

    throw FBMLException("Node doesn't have a tag name.");
  }

  /**
   * Returns the full serialization of the incoming HTML node (which is
   * presumably one carrying no children at all.)
   *
   * @param node the HTML node being serialized.
   * @return the full serialization of the HTML node passed in as the
   *         only parameter.  It's assumed that the specified node doesn't
   *         have any children (or at least no children worth rendering),
   *         so that the singleton node format is appropriate.
   */

  public function node_singleton($node)
  {
    return $this->render_html_singleton_tag($node->get_tag_name(),
                                            $this->node_get_attributes($node));
  }
}

?>
