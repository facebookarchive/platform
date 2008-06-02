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
 * Adds a period to the text if it doesn't already end with ., ?, !, ', or "
 *
 * @param  string $text User supplied text
 * @return string User text ending with a valid sentence-ender
 */
function render_with_period($text) {
  if ($text) {
    // this prevents us from adding an extra period at the end unnecessarily
    $text = rtrim($text);

    $len = strlen($text);
    $quot = array(6 => array('&#039;' => 1,
                             '&quot;' => 1),
                  4 => array('</a>' => 1),
                  1 => array('\'' => 1,
                             '"' => 1,
                             ')' => 0));
    $punc = array('.' => 1, '?' => 1, '!' => 1, ':' => 1);

    // Put a period inside closing quotes if not there already
    foreach ($quot as $l => $quotes) {
      $last = substr($text, $len-$l);
      if (isset($quotes[$last])) {
        // already has a period inside it
        if (isset($punc[$text{$len-$l-1}])) {
          return $text;
        }
        // needs a period added inside it
        if ($quotes[$last]) {
          return substr($text, 0, $len-$l).'.'.substr($text, $len-$l);
        }
        // should add a period after it, skip to below
        break;
      }
    }

    // Append a period if text doesn't already end in one of . ? !
    if (!isset($punc[$text{$len-1}])) {
      $text .= '.';
    }
  }
  return $text;
}

// FBOPEN:NOTE - FBJS ONLY
function render_js_inline($javascript) {

  return '<script type="text/javascript">'."\n".
         $javascript.
         "\n</script>\n";
}


