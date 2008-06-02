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
Testing fbml attribute rewriting
--SKIPIF--
<?php if (!extension_loaded("fbml")) print "skip"; ?>
--FILE--
<?php
include_once("../test_help.php");

function rewrite_attr($data, $tag, $attr, $val) {
  if ($val == 'hello'){
    return 'goodbye';
  }
  if ($val == 'blank') {
    return '';
  }
  return $val;
}

$tags = array("fb:name");
$schema = array();
$tag_flags = array ();
$attr_flags = array ('rewrite' => array('rewrite')
                    );


complex_expand_tag($tags, array('rewrite'),
                   $tag_flags, $attr_flags,
                   $schema);

$test_str = "<b rewrite='hello'></b>";
$node = fbml_parse_opaque_11($test_str,true,false, false, array(), array(),
                          array('func'=>"rewrite_attr", 'data'=> NULL) );

$s = fbml_render_children_11($node['root'], array(), "", "");

print $s;
print "\n";

$test_str = "<b rewrite='blank'></b>";
$node = fbml_parse_opaque_11($test_str,true, false, false, array(), array(),
                          array('func'=>"rewrite_attr", 'data'=> NULL) );

$s = fbml_render_children_11($node['root'], array(), "", "");
print $s;
print "\n";


?>
--EXPECT--
<b rewrite="goodbye"></b>
<b rewrite=""></b>
