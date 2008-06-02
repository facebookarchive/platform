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


include_once $_SERVER['PHP_ROOT'].'/lib/core/init.php';

// Facebook Copyright 2006 - 2008

/**
 * runs a variable through our common set of replacements
 *
 * @param  $var           the variable to be filtered
 * @param  $encode        whether or not to html encode the string (see above)
 * @return $filtered      the filtered variable
 */
function htmlize_filters($var, $encode=1) {
  // NB: we do '<3 ' to '&hearts;' (note space to no space)
  $filtered = str_replace(array('&#34;', '&nbsp;', '&AMP;', '&amp;', '<br />', '<br>', '<BR>', '<p>' , '<P>' , '<3 '),
                          array('"'    , ' '     , '&'    , '&'    , "\n"    , "\n"  , "\n"  , "\n\n", "\n\n", '&hearts;'),
                          $var);

  if ($encode) {
    $filtered = txt2html($filtered);
  }
  // make it look right in display
  $filtered = str_replace(array("\r\n",   "\n",     '&amp;hearts;'),
                          array('<br />', '<br />', '&hearts;'),
                          $filtered);

  return $filtered;
}

function txt2html($str) {
  return htmlspecialchars(utf8_sanitize($str), ENT_QUOTES, 'UTF-8');
}

// this is for cases (html_hyperlink, intern stuff that already his it right)
//    where we already using htmlize at the point of conversion
// this is convenient instead of doing htmlize(htmlize_filters($x)) and also
//    necessary in the case of html_hyperlink since that takes a function
//    and php doesn't do first-class-functions/lambdas
function htmlize($var) {
  $filtered = str_replace(array('&#34;', '&nbsp;', '&AMP;', '&amp;', '<br />', '<br>', '<BR>', '<p>' , '<P>' , '<3 '),
                          array('"'    , ' '     , '&'    , '&'    , "\n"    , "\n"  , "\n"  , "\n\n", "\n\n", '&hearts;'),
                          $var);
  $filtered = txt2html($filtered);
  return str_replace(array("\r\n",   "\n",     '&amp;hearts;'),
                     array('<br />', '<br />', '&hearts;'),
                     $filtered);
}


function print_canvas_javascript_references()
{
  global $PLATFORM_JS_FILES, $YOUR_PLATFORM_SERVER_URL;
  foreach ($PLATFORM_JS_FILES as $file) {
    print '<script type="text/javascript" src="'.$YOUR_PLATFORM_SERVER_URL.'js/'.$file.'"></script>';
    print "\n";
  }
}
