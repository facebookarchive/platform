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

include_once $_SERVER['PHP_ROOT'].'/lib/privacy/platform.php';

/**
 * Get all the installed apps (of all types) for a user or page
 *
 * @param int $fbid id of user or page
 * @return array -- broken down by type of install
 *
 */
function id_get_installed_apps_all_types($uid) {
  global $data_conn, $installed_apps_types;

  if (isset($installed_apps_types[$uid])) {
    return $installed_apps_types[$uid];
  }


  if ($data_conn) {
    $sql = "SELECT * FROM app_perms WHERE user_id=%d";
    $app_arr = array();
    if ($ret = queryf($data_conn, $sql, $uid)) {
      while ($row = mysql_fetch_assoc($ret)) {
        $app_arr[$row['application_id']] = $row;
      }
    }

  $profile_apps = array();
  $installed_apps = array();
  $authorized_apps = array();

  foreach  ($app_arr as $app_id => $app_data) {
    if ($app_data['installed']) {
      $installed[$app_id] = 1;
    }
    if ($app_data['authorized']) {
      $authorized[$app_id] = 1;
    }
    if ($app_data['installed_to_profile']) {
      $installed_to_profile[$app_id] = 1;
    }
  }

  // FBOPEN:NOTE  - Feel free to add columns, e.g. 
  // installed_to_minideed, installed_to_wap, etc.
  // Here for simplicity we will assume installed to profile and installed have some
  // other install applications.
  $installed_to_minifeed = $installed_to_feed = $installed;
  $installed_profile_actions = $installed_to_profile;
  
  return array (
    'authorized' => $authorized,
    'installed' => $installed,
    'profile_apps' => $installed_to_profile,
    'feed_apps' => $installed_to_feed,
    'minifeed_apps' => $installed_to_minifeed,
    'profile_actions_apps' => $installed_profile_actions
  );
  
  }
  return null;
}

/**
 * Determine if an app is installed to a user or page's minifeed
 *
 * @param int $app_id
 * @param int $fbid id of user or page
 * @return bool
 *
 */
function is_platform_app_installed_to_minifeed($app_id, $fbid) {
  if ($permissions = id_get_installed_apps_all_types($fbid)) {
    return isset($permissions['minifeed_apps'][$app_id]);
  } else {
    return false;
  }
}

/**
 * Determine if an app is installed to a user or page's feed
 *
 * @param int $app_id
 * @param int $fbid   id of user or page
 * @return bool
 *
 */
function is_platform_app_installed_to_feed($app_id, $fbid) {
  if ($permissions = id_get_installed_apps_all_types($fbid)) {
    return isset($permissions['feed_apps'][$app_id]);
  } else {
    return false;
  }
}

/**
 * Determine if an app is installed to a user or page's profile actions list
 *
 * @param int $app_id
 * @param int $fbid
 * @return bool
 *
 */
function is_platform_app_installed_to_profile_actions_list($app_id, $fbid) {
  if ($permissions = id_get_installed_apps_all_types($fbid)) {
    return isset($permissions['profile_actions_apps'][$app_id]);
  } else {
    return false;
  }
}

/**
 * Determine if an app is authorized (but not necessarily added)
 *
 * @param int $app_id
 * @param int $user
 * @return bool
 *
 */
function is_platform_app_authorized($app_id, $fbid) {
  if ($permissions = id_get_installed_apps_all_types($fbid)) {
    return isset($permissions['authorized'][$app_id]);
  } else {
    return null;
  }
}

function is_platform_app_installed($app_id, $fbid) {
  if ($permissions = id_get_installed_apps_all_types($fbid)) {
    return isset($permissions['installed'][$app_id]);
  } else {
    return null;
  }
}


