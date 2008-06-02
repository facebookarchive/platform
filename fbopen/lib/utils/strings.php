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
 * Returns true if the first arg starts with the second arg
 * @param    string    $big_string
 * @param    string    $little_string
 * @return   true or false
 */
function starts_with($big_string, $little_string) {
  return !($len = strlen($little_string)) ||
    isset($big_string[$len - 1]) &&
    substr_compare($big_string, $little_string, 0, $len) === 0;
 }

function stripslashes_recursive($a) {
  if (!is_array($a)) {
    return stripslashes($a);
  }
  $ret = array();
  foreach ($a as $key => $val) {
    $ret[stripslashes($key)] = stripslashes_recursive($val);
  }
  return $ret;
}


/**
 * Undoes any magic quote slashing from an array, like the GET or POST
 * @param    array    $a    Probably either $_GET or $_POST or $_COOKIES
 * @return   array    The array with all of the values in it noslashed
 *
 * In many cases, this can be a drop-in replacement for stripslashes_recursive
 * since this is what we typically use stripslashes_recursive for.  This is
 * somewhat different in that if we ever turn off magic quotes, it will still
 * behave correctly and not double stripslashes.
 *
 */
function noslashes_recursive($a) {
  if (get_magic_quotes_gpc()) {
    $a = stripslashes_recursive($a);
  }
  return $a;
}

/**
 * Sanitizes a string to make sure it is valid UTF that will not break in
 * json_encode or something else dastaradly like that.
 *
 * @param string $str String with potentially invalid UTF8
 * @return string Valid utf-8 string
 */
function utf8_sanitize($str) {
  return iconv('utf-8', 'utf-8//IGNORE', $str);
}

/**
 * Escapes text to make it safe to use with Javascript
 *
 * It is usable as, e.g.:
 *  echo '<script>aiert(\'begin'.escape_js_quotes($mid_part).'end\');</script>';
 * OR
 *  echo '<tag onclick="aiert(\'begin'.escape_js_quotes($mid_part).'end\');">';
 * Notice that this function happily works in both cases; i.e. you don't need:
 *  echo '<tag onclick="aiert(\'begin'.txt2html_old(escape_js_quotes($mid_part)).'end\');">';
 * That would also work but is not necessary.
 *
 * @param  string $str    The data to escape
 * @param  bool   $quotes should wrap in quotes (isn't this kind of silly?)
 * @return string         Escaped data
 */
function escape_js_quotes($str, $quotes=false) {
  if ($str === null) {
    return;
  }
  $str = strtr($str, array('\\'=>'\\\\', "\n"=>'\\n', "\r"=>'\\r', '"'=>'\\x22', '\''=>'\\\'', '<'=>'\\x3c', '>'=>'\\x3e', '&'=>'\\x26'));
  return $quotes ? '"'. $str . '"' : $str;
}
