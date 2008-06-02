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
 * Converts an array of nouns into a list "e.g. 'boxes, shoes, and garbanzo beans'"
 */
function implode_array_into_listphrase($listarray) {
  $total = count($listarray);

  switch ($total) {
    case 0:
    case 1:
      $return = (string)reset($listarray);
      break;
    case 2:
      $return = $listarray[0] . ' and ' . $listarray[1];
      break;
    case 3:
      $return = $listarray[0] . ', ' . $listarray[1] . ' and ' . $listarray[2];
      break;
    default:
      $return = render_large_list($listarray);
      break;
  }
  return sanitize_summary_text($return);
}

function render_large_list($items) {
  if (! $items || count($items) == 0) {
    return "";
  }

  $count = count($items);

  if ($count < 4) {
    error_log("Call to render_large_list with a small list; please " .
               "code explicit cases in calling code for small numbers of " .
               "items to allow for better translations.");
  }

  // Get rid of key/value pairs so we can access the array numerically.
  $items = array_values($items);
  if ($count == 1) {
    return $items[0];
  }

  $first_count = (int)(($count - 1) / 2);
  $second_count = $count - 1 - $first_count;
  $rest_of_list = _render_list_fragment(
                              array_slice($items, 0, $first_count),
                              array_slice($items, $first_count, $second_count),
                              $is_or, $locale);
  if ($is_or) {
      return $rest_of_list . ' or ' . $items[$count - 1];
  } else {
      return $rest_of_list . ' and ' . $items[$count - 1];
  }
}

/**
 * Renders a fragment of a list. Helper function for render_large_list().
 * Splits the list in half recursively such that the depth of nesting of
 * fb:intl tags is O(log n). This is needed because we limit the depth of
 * the PHP function call stack, and the naive implementation (stringing
 * fb:intl tags together in a linear walk through the list) makes us
 * bomb out with a stack-depth fatal in real-world cases.
 *
 * All this for a function people probably ought not to be calling from a
 * UI perspective anyway!
 */
function _render_list_fragment($first_half, $second_half, $is_or, $locale) {
  $first_count = count($first_half);
  $second_count = count($second_half);

  if ($first_count == 1 && $second_count == 0) {
    return $first_half[0];
  } else if ($first_count == 0 && $second_count == 1) {
    return $second_half[0];
  } else if ($first_count == 1 && $second_count == 1) {
    $item1 = $first_half[0];
    $item2 = $second_half[0];
  } else {
    $item1 = _render_list_fragment(
                  array_slice($first_half, 0, $first_count / 2),
                  array_slice($first_half, $first_count / 2),
                  $is_or, $locale);
    $item2 = _render_list_fragment(
                  array_slice($second_half, 0, $second_count / 2),
                  array_slice($second_half, $second_count / 2),
                  $is_or, $locale);
  }

  if ($is_or) {
      return $item1 .', ' . $item2;
  } else {
      return $item1 .', ' . $item2;
  }
}


/**
 * Sanitizes a paragraph that that uses other functions below.  Currently just removes
 * excess commas
 *
 * @param string $string
 * @return string
 */
function sanitize_summary_text($string)
{
  $string = str_replace(",,", ",", $string);
  $string = str_replace(",.", ".", $string);
  return $string;
}

