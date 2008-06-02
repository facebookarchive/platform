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

include_once $_SERVER['PHP_ROOT'].'/lib/api/auth.php';

function get_editable_app_fields() {
  return array('application_name' => 1,
               'callback_url' => 1,
               'post_install_url' => 1,
               'edit_url' => 1,
               'dashboard_url' => 1,
               'uninstall_url' => 1,
               'ip_list' => 1,
               'email' => 1,
               'description' => 1,
               'use_iframe' => 1,
               'desktop' => 1,
               'is_mobile' => 1,
               'default_fbml' => 1,
               'default_action_fbml' => 1,
               'default_column' => 1,
               'message_url' => 1,
               'message_action' => 1,
               'about_url' => 1,
               'private_install' => 1,
               'installable' => 1,
               'privacy_url' => 1,
               'help_url' => 1,
               'see_all_url' => 1,
               'tos_url' => 1,
               'dev_mode' => 1,
               'preload_fql' => 1,
               'contact_email' => 1);
}


/**
 * Fetches the user ids of the owners of an application
 *
 * @param  int $app_id   The application id
 * @return array         The ids of the owners
 */
function application_get_owners($app_id) {
  $info = application_dbget_info($app_id);
  $owners = array();
  if ($info['user_id']) {
    $owners[] = $info['user_id'];
  }
  return $owners;
}

function application_is_owner($app_id, $user_id, $pending=false, &$pending_arr=null) {
  $owners = application_get_owners($app_id);
  return in_array($user_id, $owners);
}


function application_get_info_from_key($api_key) {
  global $data_conn;
  $sql = "SELECT * FROM application WHERE apikey=%s";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $api_key)) {
      if ($row = mysql_fetch_assoc($ret)) {
        return $row;
      }
    }
  }
  return null; //failure
}


function application_get_id_from_key($api_key) {
  global $data_conn;
  $sql = "SELECT application_id FROM application WHERE apikey=%s";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $api_key)) {
      if ($row = mysql_fetch_assoc($ret)) {
        return $row;
      }
    }
  }
  return null; //failure
}


/**
 * Returns a string representing the given application's name.  If the
 * application doesn't have a name, returns something like "Eric's
 * Unnamed App."
 *
 * @param app_id
 * @param app_info - if you already have the app_info, pass that in so
 *                   we don't have to make an extra application_get_short_info call.
 */
function application_get_name($app_id, $app_info=null) {
  if (!$info) {
    $info = application_dbget_info($app_id);
  }
  return $info['application_name'];
}

function application_update($app_id, $app_profile, $user_id) {
  global $data_conn;

  if (!application_is_owner($app_id, $user_id)) {
    return false;
  }

  // Ensure application_id does not drift away from original id
  if (isset($app_profile['application_id']) && ($app_id != $app_profile['application_id'])) {
    error_log("Error: Cannot change value of application_id in application_update.");
    return false;
  }

  $sql_set_terms = array();
  $editable_fields = get_editable_app_fields();
  foreach ($app_profile as $name => $val) {
   
    if (isset($editable_fields[$name])) {
      $sql_set_terms[] = "`$name`=" . (is_int($val) ? "%d" : "%s");
      $params[] = $val;
    } 
  }

  $params[] = $app_id;

  if (!empty($sql_set_terms)) {
    $sql = 'UPDATE application SET ' .
      implode(",", $sql_set_terms) .
      ' WHERE application_id=%d';
    if (!vqueryf($data_conn, $sql, $params)) {
      error_log('PLATFORM: unable to update application profile on db');
      return false;
    }
  }

  return true;

}

function application_get_logo_url($app_id, $info = null) {
  if (!$info) {
    $info = application_dbget_info($app_id);
  }
  $info = application_dbget_info($app_id);
  return $info['logo_url'];
}

function application_get_icon_url($app_id, $info=null) {
  if (!$info) {
    $info = application_dbget_info($app_id);
  }
  return $info['icon_url'];
}

/**
 * @param $ip_list a comma separated string of ip addresses
 * Note 1: There is no whitespace allowed (stripped from user input and stored in db)
 * IP list is of form "10.1.0.245,10.1.0.246"
 * Note 2: If we regex check input from the API pages, we could remove the explode
 * and strcmp combo in favor of a strstr
 * Note 3: Currently we allow '*', to change when we get QOOP's IP addrs.
 */
function iplist_contains_ip($ip_list, $caller_ip) {
  $ip_list = explode(",", $ip_list);
  foreach ($ip_list as $ip) {
    // Note: ip_list contains ip strings without whitespace, so no trim required.

    // BEGIN REMOVE
    $len = strlen($ip);
    if ($ip{$len - 1} == '*') {
      if ((strncmp($ip, $caller_ip, $len-1) == 0) && is_numeric($caller_ip{$len-1}))
        return true;
    } else {
      // END REMOVE
      if (strcmp($ip, $caller_ip) == 0)
        return true;
    }
  }
  return false;
}


function application_get_fbframe_id($app_name) {
  global $data_conn;
  $sql = "SELECT fbframe_id FROM application WHERE application_name=%s";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $app_name)) {
      if ($row = mysql_fetch_assoc($ret)) {
        return $row['fbframe_id'];
      }
    }
  }
  return null;
}

function application_get_fbframe_name($app_id) {
  $info = application_dbget_info($app_id);
  return $info['fbframe_name'];
}

function application_get_short_info($app_id) {
  return application_dbget_info($app_id);
}

function application_get_info($app_id) {
  return application_dbget_info($app_id);
}

function application_dbget_info($app_id) {
  global $data_conn;
  $sql = "SELECT * FROM application WHERE application_id=%d";
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $app_id)) {
      if ($row = mysql_fetch_assoc($ret)) {
        return $row;
      }
    }
  }
  return null; //failure
}

