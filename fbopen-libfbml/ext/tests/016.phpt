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
Testing fbml batch precaching
--SKIPIF--
<?php if (!extension_loaded("fbml")) print "skip"; ?>
--FILE--
<?php
include_once("../test_help.php");

$tags = array("fb:name");
$precache = array("a", "b");

$tag_flags = array ('precache' => $precache);

$attr_flags = array (
                    );


complex_expand_tag($tags, array(), $tag_flags, $attr_flags, array());



$test_str = "<b></b><a></a><a size='100' string='hello' bool='yes' float='1.23'></a>";
$node = fbml_parse_opaque_11($test_str, true, false);


$precachable = fbml_batch_precache_11($node['root']);

foreach ($precachable as $tag=>$nodes) {
  print $tag . "\n";
  foreach ($nodes as $node) {
    print "node\n";
  }
}


?>
--EXPECT--
a
node
node
b
node
