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

// Copyright 2004-2008 Facebook. All Rights Reserved.

include_once $_SERVER['PHP_ROOT'].'/lib/core/init.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/application.php';
include_once $_SERVER['PHP_ROOT'].'/lib/networking.php';
include_once $_SERVER['PHP_ROOT'].'/lib/utils/strings.php';
include_once $_SERVER['PHP_ROOT'].'/lib/http.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/fetch.php';
include_once $_SERVER['PHP_ROOT'].'/lib/core/mysql.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/wrapper.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/implementation/css.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/mock_ajax.php';


/*
 * This proxies requests from fbjs_ajax and returns either a usable
 * FBML string, a JSON object, or a raw string.
 *
 */

// FBOPEN:SETUP - replace with your own user transmitted as you see fit.
// The normal FBJS system relies on cookies sent, but the open source
// makes no assumptions about the availability of cookies.
// Logged in users have a verified (hashed) cookie set, so
// it is useful to verify these cookies' hashes so as to prevent
// requests masquerading as an incorrect user.
$user = 1240077;


param_post(array(
  'appid'         => $PARAM_INT,
  'query'         => $PARAM_RAW,
  'type'          => $PARAM_INT,
  'url'           => $PARAM_STRING,
  'fb_mockajax_context'      => $PARAM_STRING,
  'fb_mockajax_context_hash' => $PARAM_STRING));

$app_id = $post_appid;
if (!$app_id) {
  error_log('No app_id specified in fbjs_ajax_proxy');
  exit;
}

if (is_array($post_query)) {
  // PARAM_RAW needs noslashes
  $post_query = $post_query ? noslashes_recursive($post_query) : array();
} else if ($post_query) {
  // PARAM_RAW needs noslashes
  $post_query = parse_querystring(noslashes($post_query));
} else {
  $post_query = array();
}

$FBJS_TYPES = array('RAW'  => 0,
                    'JSON' => 1,
                    'FBML' => 2);

function render_fbjs_ajax_fbml_recursive($impl, &$array) {
  foreach ($array as $key => $value) {
    if (substr($key, 0, 5) == 'fbml_') {
      $array[$key] = fbml_sample_parse($value, $impl);
    } else if (is_array($value)) {
      render_fbjs_ajax_fbml_recursive($impl, $array[$key]);
    }
  }
}

$data = null;
try {
  $impl = fbml_mock_ajax_get_impl($post_fb_mockajax_context, $post_fb_mockajax_context_hash);
  $post_vars = array('user' => $user);
  $others = array('is_ajax' => 1);
  if ($profile = $impl->get_env('profile', false, 0)) {
    $others['profile'] = $profile;
  }
  $post_vars = get_fb_validation_vars($post_vars, $app_id, $others, array(), $post_require_login);
  try {
    $response = http_post($post_url, array_merge($post_query, $post_vars));
  } catch (HTTPNoDataException $e) {
    $response = '';
  } catch (HTTPException $e) {
    // We die so that onerror will be called in JS on the user's browser
    die('');
  }
  $fbml_env = array('user' => $user,
                    'app_id' => $app_id,
                    'unfiltered_css' => false,
                    'user_triggered' => true);

  switch ($post_type) {
    case $FBJS_TYPES['RAW']:
      $data = $response;
      break;

    case $FBJS_TYPES['JSON']:
      // We need `loose' decoding which accepts unquoted keys, etc. json_decode doesn't provide this,
      // but JSON Services is buggy and doesn't work on large results... so first we try well-formed
      // json with json_decode and then defer to loose JSON Services.
      $data = json_decode($response, true);
      if ($data === null) {
        $data = get_fb_json_instance()->decode($response);
      }
      if (is_array($data)) {
        render_fbjs_ajax_fbml_recursive($impl, $data);
      }

      break;

    case $FBJS_TYPES['FBML']:
      $data = fbml_sample_parse($response, $impl);

      break;

    default:
      throw new Exception('Invalid AJAX type');
      break;
  }

  $response = array('data' => $data,
                    'type' => $post_type);

} catch (Exception $e) {
  $response = array();
  if ($e instanceof HTTPErrorException) {
    $response['error'] = $e->getCode();
    $response['error_message'] = $e->getCode();
  } else {
    error_log($e);
    $response['error'] = true;
  }
}

header('Content-Type: application/x-javascript; charset=utf-8');
echo 'for(;;);', json_encode($response);
