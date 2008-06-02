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
Testing fbml error reporting
--SKIPIF--
<?php if (!extension_loaded("fbml")) print "skip"; ?>
--FILE--
<?php
include_once("../test_help.php");
function precache($data, $node) {
  print $data;
  print fbml_get_tag_name_11($node);
  print "\n";
}

$tags = array("fb:name");
$special = array("div");
$precache = array("a");
$schema = array("fb:name"=>array("a","b"));

$tag_flags = array ('special' => $special,
                    'precache' => $precache
                    );

$attr_flags = array (
                    );


complex_expand_tag($tags, array(), $tag_flags, $attr_flags, $schema);



$test_str = "<b></b><a size='100' string='hello' bool='yes' float='1.23'></a>";
$node = fbml_parse_opaque_11($test_str, true, false);


fbml_precache_11($node['root'], "data test\n", "precache");



?>
--EXPECT--
data test
a
