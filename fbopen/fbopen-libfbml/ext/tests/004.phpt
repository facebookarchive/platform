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
Testing fbml_parse_opaque
--SKIPIF--
<?php if (!extension_loaded("fbml")) print "skip"; ?>
--FILE--
<?php
include_once("../test_help.php");
function urltr($data, $url) {
  return 'url:'.$data.'='.$url;
}


$fbml = '<fb:b style="color:red; colorr:red; background-image: url(TESTURI)" onclick="a = this.test; with(o){}">test</fb:b><!-- some comment -->';
if (fbml_tag_list_expanded_11()) print 'shouldn\'t be expanded already'."\n";

$style = array("style");
$tags = array ('fb:b');
$attrs = array ('style');
$attr_flags = array('style' => $style);
complex_expand_tag($tags, $attrs, array(), $attr_flags, array());
if (!fbml_tag_list_expanded_11()) print 'should be expanded already'."\n";


$ret = fbml_parse_opaque_11($fbml, true, false, false,
                  array('prefix' => 'app123',
                        'func' => 'urltr',
                        'data' => 'URLDATA')
);

echo $ret['error'];

$ret = fbml_parse_opaque_11($fbml, true, true, false,
                  array('prefix' => 'app123',
                        'func' => 'urltr',
                        'data' => 'URLDATA')
);

echo $ret['error'];


?>
--EXPECT--
CSS Error (line 1 char 19): PEUnknownProperty  PEDeclDropped
CSS Error (line 1 char 19): PEUnknownProperty  PEDeclDropped

