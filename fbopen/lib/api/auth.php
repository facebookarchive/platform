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

include_once $_SERVER['PHP_ROOT'].'/lib/api/session.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/application.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/errorcodes.php';

function init_api_auth() {
  global $API_DOMAIN;

  static $init_api_auth = false;
  if ($init_api_auth) {
    return;
  }
  $init_api_auth = true;

  $GLOBALS['NO_SESSION_METHODS'] =
    array($API_DOMAIN . '.auth.',
          'auth.',
          $API_DOMAIN . '.profile.setFBML',
          'profile.setFBML',
          $API_DOMAIN . '.profile.getFBML',
          'profile.getFBML',
          $API_DOMAIN . '.admin.setAppProperties',
          'admin.getAppProperties',
          $API_DOMAIN . '.users.getInfo',    // only for API_USER_PUBLIC_LISTING_INFO_APPS apps
          'users.getInfo',    // only for API_USER_PUBLIC_LISTING_INFO_APPS apps
          $API_DOMAIN . '.admin.getAppProperties',
          'admin.getAppProperties',
          $API_DOMAIN . '.admin.setAppProperties',
          'admin.setAppProperties',
          $API_DOMAIN , '.application.getPublicInfo',
          'application.getPublicInfo',
          );

  $GLOBALS['SESSION_BASED_SECRET_METHODS'] =
    array('batch_',
          'fql_',
          'friends_',
          'profile_',
          'users_',
          );
}


/**
 * Returns whether or not this API application has permissions to see the
 * given method.
 *
 * @param int    $app_id  The application
 * @param string $method  The method name
 * @param bool   $use_session_secret Is the API called using session based secret
 */
function api_can_call_method($app_id, $method, $use_session_secret=false) {

  if($use_session_secret) {
    global $SESSION_BASED_SECRET_METHODS;
    $is_allowed = false;
    foreach ($SESSION_BASED_SECRET_METHODS as $session_based_secret_method) {
      if (starts_with($method, $session_based_secret_method)) {
        $is_allowed = true;
        break;
      }
    }
    if(!$is_allowed) {
      return false;
    }
  }

  // All other methods are cool!
  return true;
}


// Supporting methods and values------
/**
 * Generate a signature for the API call.  Should be copied into the client
 * library and also used on the server to validate signatures.
 *
 */
function api_generate_sig($params_array, $secret) {
  $str = '';

  ksort($params_array);
  foreach ($params_array as $k=>$v) {
    if ($k != 'sig')
      $str .= "$k=$v";
  }
  $str .= $secret;

  return md5($str);
}


/**
 * Generate a random key using the methodology recommend in php.net/uniqid
 * @return a unique random hex key
 */
function api_generate_rand_key() {
  return md5(uniqid(mt_rand(), true));
}

function api_request_is_properly_signed($params_array, $secret, $signature)
{
  $good_sig = api_generate_sig($params_array, $secret);
  return ($good_sig === $signature);
}

/**
 * Takes an array of parameters and strips out the fb_sig_ params.  Validates
 * that the fb_sig_ parameters are properly signed.  Returns the signed
 * parameters (if they are properly signed) and the unrelated parameters as two
 * separate arrays.
 * Usage: list($sig_params, $other_params) = api_get_valid_fb_params($params, $app_info['secret']);
 */
function api_get_valid_fb_params($params, $secret, $namespace='fb_sig') {
  $prefix = $namespace . '_';
  $prefix_len = strlen($prefix);
  $fb_params = array();
  $other_params = array();
  foreach ($params as $name => $val) {
    if (strpos($name, $prefix) === 0) {
      $fb_params[substr($name, $prefix_len)] = $val;
    } else if ($name != $namespace) {
      $other_params[$name] = $val;
    }
  }
  if (!isset($params[$namespace]) || !api_request_is_properly_signed($fb_params, $secret, $params[$namespace])) {
    $fb_params = array();
  }
  return array($fb_params, $other_params);
}

/**
 * Validate an API request from a vendor - check that it has a valid api_key, the correct
 * signature, and that it has an active session.  Retrieve the application_id
 * and user_id associated with the request.
 *
 * @param $request The array of arguments (name=>values) passed to us (e.g. $_REQUEST).
 * To successfully validate, $message it must contain 'api_key', 'session_key', 'method', and 'sig'.
 * @param $app_id gets filled in with the appropriate application id on success.
 * @param $uid gets filled in with the user id associated with the session on success.
 * @param $config optional array of flags to disable various checks
 * @return API_EC_SUCCESS on success, or another API_EC_* if the request failed validation.
 */
function api_validate_api_request($request, &$app_id, &$uid, $throttle=true, $use_session_secret=false) {

  $api_key = isset($request['api_key']) ? $request['api_key'] : null;
  if (!$api_key || !($app_info = application_get_info_from_key($api_key))) {
    return API_EC_PARAM_API_KEY;
  }
  $app_id = $app_info['application_id'];

  // If application is disabled, their api_key is no longer valid,
  // though we may store it for future request tracking.
  if ($app_info['approved'] == -1) {
    return API_EC_PARAM_API_KEY;
  }

  // Similarly, if the app is deleted, the api_key is no good.  If
  // we've done everything else right, deleted apps shouldn't be
  // returned by the application_get_info_* functions, but better safe
  // than sorry.
  if ($app_info['deleted']) {
    return API_EC_PARAM_API_KEY;
  }

  $session_key = isset($request['session_key']) ? $request['session_key'] : null;


  if ($app_info['desktop']) {
    if ($throttle && ($ec = api_desktop_check_call_limit($app_id, $session_key)) !== API_EC_SUCCESS) {
      return $ec;
    }
  } else {
    if ($app_info['ip_list'] && !iplist_contains_ip($app_info['ip_list'], $_SERVER['REMOTE_ADDR'])) {
      return API_EC_BAD_IP;
    }

    if ($throttle && ($ec = api_server_check_call_limit($app_id)) !== API_EC_SUCCESS) {
      return $ec;
    }
  }

  //If $use_session_secret is true, then session_key must be provided
  if($use_session_secret && !$session_key) {
    return API_EC_PARAM_SESSION_KEY;
  }

  $secret = $app_info['secret']; // will sig check after checking the session, since some apps have a session secret

  $method = isset($request['method']) ? $request['method'] : null;
  if (!$method) {
    return API_EC_METHOD;
  }

  $method_requires_session = api_method_requires_session($method);

  // Some methods don't require a session key but still work with session key.
  // Even if the method doesn't require a session key and the session key is passed in, the session key
  // should be respected, it's up to the individual method to figure out the tangled mess for itself...
  if ($method_requires_session || $session_key) {

    // If the method requires a session and one isn't provided, FAIL fast...
    if ($method_requires_session && !$session_key) {
      return API_EC_PARAM_SESSION_KEY;
    }

    if ($app_info['desktop'] || $use_session_secret) {
      $session_info = api_session_get_info($session_key, $app_id);
      $secret = $session_info['session_secret'];
    }

    // If the developer provides a session key even if it's not required, fail if it's not valid...

    if ($session_key && false == ($uid = api_session_extract_uid($session_key, $app_id))) {
      return API_EC_PARAM_SESSION_KEY;
    }

    if ($session_key && (($ec = api_session_check_valid($session_key, $app_id)) !== API_EC_SUCCESS)) {
      return $ec;
    }

    /* The request has now been validated! */
    $GLOBALS['user'] = $uid; // a bunch of utility functions expect a global $user to be set
  }

  $sig = isset($request['sig']) ? $request['sig'] : null;
  if (!api_request_is_properly_signed($request, $secret, $sig)) {
    return API_EC_PARAM_SIGNATURE;
  }

  return API_EC_SUCCESS;
}

/**
 * A small number of methods can be called outside of the context of a session.
 * Whitelist those methods from some of the auth checks with this function.
 * @param string $method the name of the method
 * @return true if the method requires a valid session
 */
function api_method_requires_session($method) {
  global $NO_SESSION_METHODS;
  foreach ($NO_SESSION_METHODS as $no_session_method) {
    if (starts_with($method, $no_session_method)) {
      return false;
    }
  }
  return true;
}

/**
 * Check to see if the vendor has exceeded their limit on calls
 *
 * @param $app_id - application id
 * @return API_EC_SUCCESS on success, API_EC_TOO_MANY_CALLS if too many calls have been made
 */
function api_server_check_call_limit($app_id) {
  // FBOPEN:NOTE - You may wish to count calls in api/restserver.php from an app_id.
  // You have the chance to fail the request here.
  return API_EC_SUCCESS;
}


/**
 * Checks the call limit based on a cache value that decays exponentially over
 * time.  This is used to rate limit desktop applications on a per-session
 * basis.  Using the default values an application can safely make one request
 * per second without ever exceeding the limit, or go about 4 seconds making 2
 * requests per second, or about 5 requests in one second.
 *
 * @param float $decay_rate the rate to use to decay.
 * @param int   $max_val    the value at which to start erroring
 * @param int   $timeout    the amount of seconds of inactivity before we forget the call limit
 */
// FBOPEN:NOTE - You may wish to count calls in api/restserver.php from an app_id.
// You have the chance to fail the request here.
function api_desktop_check_call_limit($app_id, $session_key, $decay_rate=12345, $max_val=12345, $timeout=12345) {
  return API_EC_SUCCESS;
}

init_api_auth();
