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

include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/fbml_node.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/flavors/canvas_page_flavor.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/html_renderer.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/fbml_utils.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/implementation/fbml_implementation.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/implementation/mini_facebook_implementation.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/implementation/fbjs_facebook_implementation.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/fbml_schema.php";

function fbml_sample_parse($fbml_from_callback, $fbml_impl)
{

  // Preconfigure the FBML engine so it knows which FBML tags
  // to look for, which HTML tags require special rendering, which
  // tags require precaching, and so forth.

  $fbml_tags = $fbml_impl->get_all_fb_tag_names();
  $html_special = $fbml_impl->get_special_html_tags();
  $precache_tags = $fbml_impl->get_precache_tags();
  $style_tags = array('style');
  $style_attrs = array('style', 'imgstyle');

  $rewrite_attrs = array( 'name', 'for', 'href', 'src', 'background', 'url', 'dynsrc', 'lowsrc',
       'clickrewriteurl', 'onclick', 'onmouseover', 'onabort', 'onblur', 'ondblclick',
       'onerror', 'onfocus', 'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onmousedown',
       'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmousewheel', 'onmouseup',
       'onreset', 'onresize', 'onselect', 'onsubmit', 'onunload', 'onchange');

  $special_attrs = array('id');
  $fbml_attrs = array_merge($style_attrs, $rewrite_attrs, $special_attrs);
  $fbml_schema = schema_get_schema();

  fbml_complex_expand_tag_list_11($fbml_tags, $fbml_attrs,
      $html_special, $precache_tags, $style_tags,
      $style_attrs, array(), $rewrite_attrs, $special_attrs,
      $fbml_schema);


  $data = array('impl'        => $fbml_impl );

  $rewriter = array('func'  => 'fbml_rewrite_attr',
      'data'  =>$fbml_impl);

  // FBOPEN:NOTE - no css sanitizer incorporated in this version.
  $sanitizer = array();

  $parse_tree = fbml_parse_opaque_11($fbml_from_callback, true /*$this->_body_only*/, false, false, $sanitizer, array(), $rewriter);
  $fbml_tree = new FBMLNode($parse_tree['root']);


  $fbml_tree->precache($fbml_impl);

  $html =  $fbml_tree->render_html($fbml_impl);


  return $html;
}

function fbml_rewrite_attr($impl, $tag, $attr, $val) {
  $html_r =  $impl->_html_rewriter;
  if ($html_r->attr_is_js($attr)) {
    $fbjs = '';
    try {
      $fbjs = $impl->_fbjs_impl()->render_event($attr, $val);
      if ($fbjs === false) {
        $impl->add_error('Unknown Javascript action attribute: '.$attr);
        return '';
      }
    } catch (Exception $e) {
      $impl->add_error($e->getMessage());
    }
    return $fbjs;
  } else if ($html_r->attr_is_url($attr)) {
    try {
      if ($tag == 'a' || $tag == 'label' || $attr == 'href') {
        return $html_r->validate_url($val, true, $impl->_flavor->allows('relative_urls'), true);
      }
      else {
        return $html_r->validate_url($val, false, true, false);
      }
    } catch (FBMLUrlException $e) {
      $impl->add_error("URLException" . $e->getMessage());
      return '';
    }
  } else if ($attr == 'name') {
    if (strlen($val)>=2 && $val[0]=='f' && $val[1] == 'b') {
      $impl->add_error('Names beginning with "fb" are reserved by Facebook (' . $val . ')');
      return '';
    }
  } else if ($attr == 'for') {
    return $impl->_html_rewriter->prefix_id($val);
  }
  return $val;
}

