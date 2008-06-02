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

$GLOBALS['SECURE_MAPS'] = array('api',
                                'secure',
                                'www',
                                'login',
                                'register');

/**
 * URL Generation with parameterization
 *
 * @param string $path        relative path
 * @param string $map         optional map (school) to use in domain part of the constructed URL
 */
function redirect_str($path, $map=null, $ssl=0, $force_prod=false, $force_protocol=false) {
  return redirect($path, $map, 0, $ssl, $force_prod, $permanent_redirect=false, $force_protocol);
}

/**
 * Get the current redirect map (i.e. www/upload/etc)
 *
 * @return string Current redirect map
 */
function redirect_map() {
  global $API_DOMAIN;
  if (isset($GLOBALS['CACHE']['redirect:map'])) { // cache default map in globals
    return $GLOBALS['CACHE']['redirect:map'];
  }
  // use current domain
  $map = explode('.', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
  $map = isset($map[0]) ? $map[0] : null;
  if ($map == $API_DOMAIN || !$map) {
    $map = 'www';
  }
  return ($GLOBALS['CACHE']['redirect:map'] = $map);
}

/**
 * Get a secure map
 */
function get_secure_map($map='') {
  // FBOPEN:NOTE - developers may wish to check against a list of
  // secure maps here.
  return $map;
}

function redirect($path, $map=null, $redirect=1, $ssl=0, $force_prod=false,
                  $permanent_redirect=false, $force_protocol=false) {
  global $API_DOMAIN_DOT_SUFFIX;


  if ((isset($path[6]) && $path[4] == ':' && $path[5] == '/' && $path[6] == '/') ||             // support full local url but it will be mangled
      (isset($path[7]) && $path[5] == ':' && $path[6] == '/' && $path[7] == '/' && $ssl = 1)) { // the "=" here is NOT a typo, it is an assignment
    $url_parts = parse_url($path);
    if (strpos($url_parts['host'], $API_DOMAIN_DOT_SUFFIX) !== false) {
      $path = $url_parts['path'].((!empty($url_parts['query'])) ? '?'.$url_parts['query'] : '');
    }
  }

  if ($map === null || !$map) {
    $map = redirect_map();
  }

  if (isset($GLOBALS['CACHE']['redirect:domain'])) { // cache default domain in globals
    $domain = $GLOBALS['CACHE']['redirect:domain'];
  } else {
    $http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'facebook.com';
    $numdots = substr_count($http_host, '.');

    if ($numdots==1) {
      $domain = $http_host;
    } else {
      $domain = substr($http_host, (strpos($http_host, '.')+1));
    }
    $GLOBALS['CACHE']['redirect:domain'] = $domain;
  }
  if ($force_prod) {
    $domain = $API_DOMAIN_DOT_SUFFIX;
  }

  switch ($ssl){
  case 0 : // forces non-secure
    $protocol = 'http:'; break;
  case 1 : // force secure
    $protocol = 'https:';
    $map      = get_secure_map($map);
    break;
  default:
    break;
  }

  if ($path) {
    $pathchar = ($path{0}!='/') ? '/' : '';
    $path = $pathchar.$path;
  }

  $url = $protocol.'//'.$map.'.'.$domain.$path;

  if ($redirect) {
    if ($permanent_redirect) {
      header('HTTP/1.x 301 Moved Permanently');
    }
    header("Location: $url");
    exit();
  }

  return($url);
}

/**
 * Parse a query string into an array
 *
 * @param  string $qa  optional query string to parse
 * @return array  An array of query items
 */
function parse_querystring($qs, $decode=false) {
  $query_items = array();

  if ($qs) {
    $tokens = explode('&', $qs);

    foreach ($tokens as $tok) {
      $pos = strpos($tok, '=');
      if ($pos !== false) {
        $val = substr($tok, $pos+1);
        if ($decode) {
          $val = urldecode($val);
        }
        $query_items[substr($tok, 0, $pos)] = $val;
      } else {
        $query_items[$tok] = '';
      }
    }
  }

  return($query_items);
}
