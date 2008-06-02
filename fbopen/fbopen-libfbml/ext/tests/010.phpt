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

?>
--TEST--
Testing fbml render
--SKIPIF--
<?php if (!extension_loaded("fbml")) print "skip"; ?>
--FILE--
<?php
include_once("../test_help.php");

/**
 * Contived function that gets called to render
 * any tags we deem to be special.
 */

function html_fun($data, $node)
{
  return '';
}

/**
 * Function that gets invoked on behalf of those tags in the
 * tags array (which in the case of this test is just "fb:name").
 * Note that the implementation elects to continue with a full
 * render through subtrees hanging from the <fb:name> node.
 *
 * @param $data auxiliary data supplied as the second argument to the
 *              call to fbml_render_children_11 that ultimately triggered
 *              the call to fb_fun.
 */

function fb_fun($data, $node)
{
  $ret = "<bah>";
  $ret .= fbml_render_children_11($node, $data, "html_fun", "fb_fun");
  $ret .= "</bah>";
  return $ret;
}

$tags = array("fb:name");
$special = array("b");
$precache = array("a");
$schema = array();

$tag_flags = array('special' => $special, 'precache' => $precache);
$attr_flags = array();
complex_expand_tag($tags, array(), $tag_flags, $attr_flags,  $schema);

$test_str = "<div><fb:name><a>Hello</a></fb:name></div><div><b/></div><div><div></div></div><div blah='hello'></div>";
$node = fbml_parse_opaque_11($test_str,true,false);

$s = fbml_render_children_11($node['root'], array(), "html_fun", "fb_fun");
print $s;

?>

--EXPECT--
<div><bah><a>Hello</a></bah></div><div></div><div><div></div></div><div blah="hello"></div>

