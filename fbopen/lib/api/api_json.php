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
 * Takes care of JSON rendering for the REST-based API. We do this ourselves
 * so that we can properly handle our special quirks like invisibility.
 *
 * @param  $object object to be serialized by json encoding
 * @return string representing serialized form of $object
 */
function api_json2_render_object($object) {
  $json = '';

  if ($object instanceof FQLCantSee) {
    $json .= 'null';
  } else if (is_array($object)) {
    $list = (key($object) === 0);
    $json .= $list ? '[' : '{';
    if (!empty($object)) {
      $values = array();
      foreach ($object as $k => $v) {
        $val = '';
        if (!$list) {
          $val .= json_encode($k).':';
        }
        $val .= api_json2_render_object($v);
        $values []= $val;
      }
      $json .= implode(',', $values);
    }
    $json .= $list ? ']' : '}';
  } else if (is_object($object)) {
    $json .= '{';
    $values = array();
    foreach ($object as $k => $v) {
      if (isset($v)) {
        $values []= json_encode($k).':'.api_json2_render_object($v);
      }
    }
    $json .= implode(',', $values);
    $json .= '}';
  } else {
    $json .= json_encode($object);
  }
  return $json;
}

