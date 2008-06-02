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


/**
 * Function: remove_partial_unicodes
 * ---------------------------------
 * Function intended to cleanse the specified string (assumed to
 * be an attribute string being rendered by PHP code) of any
 * trailing partial unicodes.  Something like "\u0000" is technically
 * fine, since the unicode character is valid.  But something like
 * "\u000" is invalid, and while most browsers will recognize this,
 * some browsers don't, include the " as the fourth character in the
 * unicode literal, and we have an XSS opening.  This solution
 * here is fairly ad hoc: it just looks for the last occurrence
 * of any of the specified unicode literal openers, and if there
 * are less than four characters before the end of the string, it
 * clips the string from the unicode opener forward, and then
 * repeats the process, so as to handle strings like "\u00\u00\u00".
 *
 * @param the string being stripped of any trailing partial unicodes.
 *        the intent here is to make sure there are no trailing partial
 *        unicodes after processing.
 * @return the same string, except that trailing partial unicodes have
 *         been removed.
 */

function remove_partial_unicodes($str)
{
  $num_chars_needed = 6;

  while (true) {
    $found = strripos($str, '\x');
    if ($found === false) break;
    $length = strlen($str);
    $num_chars_after_slash = $length - $found;
    if ($num_chars_after_slash >= $num_chars_needed) break;
    $str = substr($str, 0, $found);
  }

  return $str;
}

