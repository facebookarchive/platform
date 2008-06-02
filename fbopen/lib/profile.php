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

include_once $_SERVER['PHP_ROOT'] . '/lib/core/mysql.php';

/**
 * Library function for fetching all profile info for
 * a user id.
 *
 * @param  $id  id of user to fetch info for
 * @return associative array of data for the user, where
 *         key denotes the field name and value is the
 *         data for that field. null if user does not
 *         exist in this system.
 */
function user_get_info($id) {
  return profile_dbget_user_info($id);
}

function user_get_name($id) {
  $info = profile_dbget_user_info($id);
  return $info['name'];
}

/**
 * Library function for searching users by name. Given
 * a name, it returns a list of user ids by that name.
 *
 * @param  $name  name to search for
 * @return array of user ids that have that name
 */
function get_users_by_name($name) {
  global $user_names;
  if (isset($user_names[$name])) {
    return $user_names[$name];
  }
  return null;
}

function profile_dbget_user_info($uid) {
  global $data_conn, $user_infos;
  if (isset($user_infos[$uid])) {
    return $user_infos;
  }

  $sql = "SELECT * FROM info WHERE uid=%d";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $uid)) {
      $row = mysql_fetch_assoc($ret);

      $row['current_location'] = array ( 
        'city' => $row['current_location_city'],
        'state' => $row['current_location_state'],
        'country' => $row['current_location_country'],
        'zip' => $row['current_location_zip']
        );

      $row['hometown_location'] = array (
          'city' => $row['hometown_location_city'],
          'state' => $row['hometown_location_state'],
          'country' => $row['hometown_location_country'],
          'zip' => $row['hometown_location_zip']
      );

      $row['hs_info'] = array (
          'hs1_id' => $row['hs1_id'],
          'hs1_name' => $row['hs1_name'],
          'hs2_id' => $row['hs2_id'],
          'hs2_name' => $row['hs2_name'],
          'grad_year' => $row['grad_year']
      );

      $row['meeting_for'] = explode(',', $row['meeting_for']);
      $row['meeting_sex'] = explode(',', $row['meeting_sex']);

      $row['affiliations'] = dbget_affiliations($uid);
      $row['education_history'] = dbget_education_history($uid);
      $row['work_history'] = dbget_work_history($uid);

      $users[$uid] = $row; 
      return $row;
    }
  }

  return null; // failure
}

function dbget_education_history($uid) {
  global $data_conn;
  $results = array();
  $sql = "SELECT * FROM education WHERE uid=%d";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $uid)) {
      while ($row = mysql_fetch_assoc($ret)) {
        $results[]  = $row;
      }
    }

    return $results;
  }
  return null; //failure
}

function dbget_work_history($uid) {
  global $data_conn;
  $results = array();
  $sql = "SELECT * FROM work_history WHERE uid=%d";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $uid)) {
      while ($row = mysql_fetch_assoc($ret)) {
        $results[]  = $row;
      }
    }

    return $results;
  }
  return null; //failure
}

function dbget_affiliations($uid) {
  global $data_conn;
  $results = array();
  $sql = "SELECT * FROM affiliations WHERE uid=%d";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $uid)) {
      while ($row = mysql_fetch_assoc($ret)) {
        $results[]  = $row;
      }
    }

    return $results;
  }
  return null; //failure
}

function profile_app_get_fbml($profile_id, $app_id) {
  global $data_conn;
  $sql = "SELECT fbml FROM profile_fbml WHERE profile_id=%d AND app_id=%d";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $profile_id, $app_id)) {
      if ($row = mysql_fetch_assoc($ret)) {
        return $row['fbml'];
      }
    }
  }
  return null;
}
 
// FBOPEN:NOTE  - profile_action and mobile_profile are not part of the core API
// and are ignored. 
function profile_app_set_fbml($profile_id, $app_id, $fbml, $profile_action = null, $mobile_profile = null) {
  global $data_conn; 
  if ($data_conn) {

    $sql =    'INSERT INTO profile_fbml ' .
                      '(profile_id, app_id, fbml) ' .
                      'VALUES (%d, %d, %s)' .
                      'ON DUPLICATE KEY UPDATE fbml = %s';

    if ($ret = queryf($data_conn, $sql,
                      $profile_id, $app_id, $fbml, $fbml)) {
        return true;
    }
  }
  return false;
}

