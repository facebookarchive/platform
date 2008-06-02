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
 * Validates the url md5 hash.  This is used to block attempts at modifying addresses directly
 * <p>
 * Example.  Validate hash value:
 * <p>
 *     <code>if (url_checkmd5()) { header('http://$map.facebook.com/home.php'); my_exit(); }</code>
 *
 * @param  string $key      Private key to hash on (defaults to global $url_md5key)
 * @param  string $argname  Name of the GET variable that stores the hash (defaults to 'h')
 * @param  boolean $relative Hash over the relative path, not full http[s]://[hostname]
 * @return bool   true if the hash matches the url, false if it does not.
 */
function url_checkmd5($key='', $argname='h', $relative=false) {
  global $url_md5key;
  $url = '';

  if ($key == '') {
    $key = $url_md5key;
  }

  if (!$relative) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';

    // Build URL
    $url = $protocol.urldecode($_SERVER['HTTP_HOST']);
  }
  $url .= urldecode($_SERVER['REQUEST_URI']);

  // Make sure the hash variable is in the array
  if (!isset($_GET[$argname])) {
    return false;
  }

  // Remove hash value from GET string
  $url = str_replace('&'.$argname.'='.$_GET[$argname], '', $url);

  // Validate hash
  if (md5($url.$key) != $_GET[$argname]) {
    return 0;
  }

  return 1;   // Success
}

