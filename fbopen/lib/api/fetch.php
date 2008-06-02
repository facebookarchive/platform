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

include_once $_SERVER['PHP_ROOT'] . '/lib/friend.php';
include_once $_SERVER['PHP_ROOT'] . '/lib/privacy/platform.php';
include_once $_SERVER['PHP_ROOT'] . '/lib/platform_install.php';

/**
 * Simple parameters that are passed to a canvas or canvas-like page
 */
function api_canvas_parameters() {
  return array(
    'in_canvas' => 1,
    'request_method' => $_SERVER['REQUEST_METHOD'],
  );
}

function api_canvas_parameters_other($app_id, $user)  {
  // Add the list of friends and the friends who've added the app
  // to the POST we make
  $app_info = application_get_info($app_id);

  $api_friends = user_get_all_friends($user);
  foreach ($api_friends as $k => $friend) {
    if (!platform_can_see_app($app_id, $friend, $app_info)) {
      unset($api_friends[$k]);
    }
  }
  $csv_api_friends = implode(',', $api_friends);
  return array('friends' => $csv_api_friends);
}


function get_fb_validation_vars($user, $app_id, $others=array(), $logged_in_others=array(), $require_login=null) {
  global $DEMO_SESSION_KEY;
  $app_info = application_get_short_info($app_id);
  $secret   = $app_info['secret'];

  $others['time'] = (string)microtime(true);

  if (is_array($user)) {
    $user = $user['user'];
  }
  if ($user) {
    $others['added'] = (int)is_platform_app_installed($app_id, $user);

    $session_key = $DEMO_SESSION_KEY; // FBOPEN:NOTE - stub: assume user session exists

    if ($session_key) {
      $others['user'] = $user;
      $others['session_key'] = $session_key;
      $session_info = api_session_get_info($session_key, $app_id);
      if ($app_info['desktop']) {
        // use the session secret instead of the normal one
        $secret = $session_info['session_secret'];
      }
      if ($session_info['session_timeout'] == 0) {
        $others['expires'] = 0;
      } else {
        $others['expires'] = $session_info['key_create_time'] + $session_info['session_timeout'];
      }
      $others += $logged_in_others;
    } elseif ($require_login) {
      $others['user'] = $user;
    }
  }

  $others['api_key'] = $app_info['apikey'];
  $vars = array();
  foreach ($others as $n=>$v) {
    $vars['fb_sig_' . $n] = $v;
  }
  $vars['fb_sig'] = api_generate_sig($others, $secret);
  return $vars;
}

