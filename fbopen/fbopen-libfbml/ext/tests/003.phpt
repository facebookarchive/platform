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
Testing fbml_parse
--SKIPIF--
<?php if (!extension_loaded("fbml")) print "skip"; ?>
--FILE--
<?php
function urltr($data, $url) {
  return 'url:'.$data.'='.$url;
}

function ehtr($data, $url) {
  return 'eh:'.$data.'='.$url;
}

class FBMLParseTree {}
class FBMLNode { public $_text;}
class FBMLRootNode extends FBMLNode {}
class FBMLHTMLNode extends FBMLNode {}
class FBMLMacroTagNode extends FBMLNode {}
class FBMLPlaintextNode extends FBMLNode {}
class FBMLStyleNode extends FBMLHTMLNode {}
class FBMLCommentNode extends FBMLNode {}

function dump_node($node) {
  $out = '';
  if ($node->_children) $out = "DEEPER\n";
  foreach ($node->_children as $child_node) {
    if ($child_node instanceof FBMLPlaintextNode) {
      $out .= preg_replace('/\\r\\n/', "\n", $child_node->_text);
    } else if ($child_node instanceof FBMLCommentNode) {
      $out .= 'COMMENT: ';
      $out .= preg_replace('/\\r\\n/', "\n", $child_node->_text);
    } else if ($child_node instanceof FBMLMacroTagNode) {
      $out .= 'FBTAG: '.$child_node->_tag_name;
      $out .= ' ATT: '.serialize($child_node->_attributes);
      $out .= "\n";
    } else if ($child_node instanceof FBMLStyleNode) {
      $out .= 'STYLE: '.$child_node->_tag_name;
      $out .= ' ATT: '.serialize($child_node->_attributes);
      $out .= "\n";
    } else if ($child_node instanceof FBMLHTMLNode) {
      $out .= 'HTML: '.$child_node->_tag_name;
      $out .= ' ATT: '.serialize($child_node->_attributes);
      $out .= "\n";
    } else {
      $out .= '????: '.serialize($child_node);
      $out .= "\n";
    }
    $out .= dump_node($child_node);
  }
  if ($node->_children) $out .= "SHALLOWER\n";

  return $out;
}

$fbml = '<fb:b style="color:red; colorr:red; background-image: url(TESTURI)" onclick="a = this.test; with(o){}">test</fb:b><!-- some comment -->';
if (fbml_tag_list_expanded()) print 'shouldn\'t be expanded already'."\n";
fbml_expand_tag_list(array("fb:b"));
if (!fbml_tag_list_expanded()) print 'should be expanded already'."\n";
fbml_expand_tag_list(array("fb:b")); // should be okay to expand it again

$ret = fbml_parse($fbml, new FBMLParseTree(), true, false,
                  array('prefix' => 'app123',
                        'func' => 'urltr',
                        'data' => 'URLDATA'),
                  array('prefix' => 'APPJS',
                        'func' => 'ehtr',
                        'data' => 'EHDATA',
                        'that' => 'THAT')
);


echo dump_node($ret['root']);
echo $ret['error'];
var_dump($ret);




$ret = fbml_parse($fbml, new FBMLParseTree(), true, true,
                  array('prefix' => 'app123',
                        'func' => 'urltr',
                        'data' => 'URLDATA'),
                  array('prefix' => 'APPJS',
                        'func' => 'ehtr',
                        'data' => 'EHDATA',
                        'that' => 'THAT')
);
echo dump_node($ret['root']);
echo $ret['error'];
?>
--EXPECT--
DEEPER
FBTAG: fb:b ATT: a:2:{s:5:"style";s:55:"color: red; background-image: url(url:URLDATA=TESTURI);";s:7:"onclick";s:44:"eh:EHDATA=APPJSa = THAT.test; with(APPJSo){}";}
DEEPER
testSHALLOWER
SHALLOWER
CSS Error (line 1 char 19): PEUnknownProperty  PEDeclDropped
JS Error (line 1 char 15): "with" is not supported
DEEPER
FBTAG: fb:b ATT: a:2:{s:5:"style";s:55:"color: red; background-image: url(url:URLDATA=TESTURI);";s:7:"onclick";s:44:"eh:EHDATA=APPJSa = THAT.test; with(APPJSo){}";}
DEEPER
testSHALLOWER
COMMENT: DEEPER
 some comment SHALLOWER
SHALLOWER
CSS Error (line 1 char 19): PEUnknownProperty  PEDeclDropped
JS Error (line 1 char 15): "with" is not supported
