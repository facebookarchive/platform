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
 * Backend implementation of the API functions, indepenedent of the frontend,
 * i.e. this is the code that is ALWAYS shared between REST, SOAP, Thrift, etc.
 */

include_once $GLOBALS['THRIFT_ROOT'].'/packages/api_10/FacebookApi10.php';

include_once $_SERVER['PHP_ROOT'].'/lib/platform_install.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/includes.php';
include_once $_SERVER['PHP_ROOT'].'/lib/friend.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/feed.php';
include_once $_SERVER['PHP_ROOT'].'/lib/friend_list.php';
include_once $_SERVER['PHP_ROOT'].'/lib/utils/strings.php';
include_once $_SERVER['PHP_ROOT'].'/lib/common.php';

/**
 * Class that implements the API. One instance of this should be created per
 * API web script run (i.e. in restserver.php).
 */
class FacebookApi10Implementation implements FacebookApi10If {

  // Application id
  protected $app_id;

  // User id
  protected $user_id;

  // Session key
  protected $session_key;

  // Is API validated using session secret instead of app secret?
  // session secret is less secure than app secret because it's visible on client's browser
  protected $using_session_secret;

  /**
   * Constructor. Takes the app id, user id, and session key, all of which are
   * specific to this request context and should never change in this handler.
   *
   * @param int    $app_id       Application id (object id)
   * @param int    $user_id      User id (Normal facebook uid)
   * @param string $session_key  Session key (implies the other two)
   * @param string $format       Response format
   */
  public function __construct($app_id, $user_id, $session_key, $format='xml', $using_session_secret = false) {
    $this->app_id = $app_id;
    $this->user_id = $user_id;
    $this->session_key = $session_key;
    $this->format = $format;
    $this->parser = new FqlParser($user_id, $app_id);
    $this->using_session_secret = $using_session_secret;
  }

  /**
   * Helper function used internally to throw an exception with an error code
   *
   * @param int $code API error code
   */
  protected function throw_code($code) {
    $msg = $GLOBALS['api_error_descriptions'][$code];
    throw new api10_FacebookApiException(array('error_code' => $code,
      'error_msg' => $msg));
  }

  protected function throw_exception($code, $e) {
    $msg = $GLOBALS['api_error_descriptions'][$code];
    throw new api10_FacebookApiException
      (array('error_code' => $code,
             'error_msg' => $msg.': '.$e->getMessage()));
  }

  /**
   * A bunch of places we get ids as a comma separated list, and need to map
   * it to an array of user ids
   *
   * @param str $ids
   * @return array array of integers
   */
  protected function get_ids_from_list($ids) {
    $ids = preg_replace('/[^,\d]/', '', $ids);
    if (!$ids) {
      return array();
    }
    $id_arr = explode(',', $ids);
    $pos_arr = array();
    foreach ($id_arr as $id) {
      if (($cast = (int)$id) > 0) {
        $pos_arr []= $cast;
      }
    }
    return $pos_arr;
  }

  public function fql_query($query) {
    try {
      switch ($this->format) {
      case 'json':
        return $this->parser->query_thrift($query);
      case 'xml':
      default:
        return $this->parser->query_xml($query);
      }
    } catch (DatabaseException $e) {
      $this->throw_exception(API_EC_DATA_DATABASE_ERROR, $e);
    } catch (InvalidArgumentException $e) {
      $this->throw_exception(API_EC_PARAM, $e);
    } catch (PermissionException $e) {
      $this->throw_exception(API_EC_PERMISSION, $e);
    } catch (DataQuotaException $e) {
      $this->throw_exception(API_EC_DATA_QUOTA_EXCEEDED, $e);
    } catch (InvalidOperationException $e) {
      $this->throw_exception(API_EC_DATA_INVALID_OPERATION, $e);
    }
  }

  // FBOPEN:NOTE - usually this token is bound to a session key by your login flow.
  // Here we just use a static session key.
  public function auth_createToken() {
    global $DEMO_SESSION_KEY;
    $token = api_authtoken_create($this->app_id);
    if (!$token) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_UNKNOWN);
    }

    // FBOPEN:NOTE - DEMO ONLY!  We'll bind the auth token and demo session key
    // here as if a user had logged in with that token and we had generated that
    // session key (through whatever means you choose)

    api_authtoken_bind($this->app_id, $token, $DEMO_SESSION_KEY);
    return $token;
  }

  public function auth_getSession($auth_token) {
    if (!$auth_token) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
    }

    $info = api_authtoken_get_info($this->app_id, $auth_token);
    if (!$info || !$info['session_key']) {
      // if the auth_token is invalid or hasn't been bound to a session key
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
    }

    $session_info = api_session_get_info($info['session_key'], $this->app_id);
    if (!$session_info) {
      // There might be multiple valid auth_token <-> session_key
      // mappings, but only one of the session_key values is actually
      // valid.
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
    }

    $session = new api10_session_info();
    $session->session_key = $info['session_key'];

    $session->uid = api_session_extract_uid($info['session_key'], $this->app_id);
    if ($session_info['session_timeout'] == 0) {
      $session->expires = 0;
    } else {
      $session->expires = $session_info['key_create_time'] +
        $session_info['session_timeout'];
    }

    $app_info = application_get_info($this->app_id);
    if ($app_info['desktop']) {
      $session->secret = $session_info['session_secret'];
    }
    return $session;
  }


  public function feed_publishStoryToUser($title, $body,
      $image_1, $image_1_link,
      $image_2, $image_2_link,
      $image_3, $image_3_link,
      $image_4, $image_4_link) {

    if (!is_platform_app_installed_to_feed($this->app_id, $this->user_id)) {
      return array(false);
    }

    $error = '';
    $feed_story = application_create_feed_story($this->app_id, $this->user_id, false,
        $title, $body,
        $image_1, $image_1_link,
        $image_2, $image_2_link,
        $image_3, $image_3_link,
        $image_4, $image_4_link,
        $error);
    if (!$feed_story) {
      switch ($error) {
        case 'error_title_link':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_LINK);
          break;
        case 'error_title_length':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_LENGTH);
          break;
        case 'error_title_name':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_NAME);
          break;
        case 'error_title_blank':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_BLANK);
          break;
        case 'error_body_length':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_BODY_LENGTH);
          break;
        case 'error_photo_src':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_PHOTO_SRC);
          break;
        case 'error_photo_link':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_PHOTO_LINK);
          break;
        case 'error_illegal_content':
          throw new api10_FacebookApiException
            (array('error_code' => api10_FacebookApiErrorCode::API_EC_PARAM,
                   'error_msg' => 'Your feed story contains illegal or malformed content.'));
        default:
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_MARKUP);
          break;
        }
      }

      $app_info = application_get_info($this->app_id);
      if (application_is_owner($this->app_id, $this->user_id)) {
        // Bypass checks if the developer is publishing to his own profile
        $publish = true;
      } else {
        // Get the number of available feed points and check call limits
        $available_feed_points = application_get_available_feed_points($this->app_id, $this->user_id);
        if ($available_feed_points == 0) {
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TOO_MANY_USER_CALLS);
        }

        // Figure out how many feed points to use, and probabilistically determine whether or not to publish
        $publish = application_use_feed_points($this->app_id, $this->user_id, 1);
      }

      if ($publish) {
        application_publish_story_to_user($this->app_id, $this->user_id, $feed_story);
      }

      return array(true);
    }

  public function feed_publishActionOfUser($title, $body,
                                           $image_1, $image_1_link,
                                           $image_2, $image_2_link,
                                           $image_3, $image_3_link,
                                           $image_4, $image_4_link) {
      if (!is_platform_app_installed_to_minifeed($this->app_id, $this->user_id)) {
        return array(false);
      }

      $error = '';
      $feed_story = application_create_feed_story($this->app_id, $this->user_id, true,
                                                  $title, $body,
                                                  $image_1, $image_1_link,
                                                  $image_2, $image_2_link,
                                                  $image_3, $image_3_link,
                                                  $image_4, $image_4_link,
                                                  $error);

      if (!$feed_story) {
        switch ($error) {
        case 'error_title_link':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_LINK);
          break;
        case 'error_title_length':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_LENGTH);
          break;
        case 'error_title_name':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_NAME);
          break;
        case 'error_title_blank':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TITLE_BLANK);
          break;
        case 'error_body_length':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_BODY_LENGTH);
          break;
        case 'error_photo_src':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_PHOTO_SRC);
          break;
        case 'error_photo_link':
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_PHOTO_LINK);
          break;
        case 'error_illegal_content':
          throw new api10_FacebookApiException
            (array('error_code' => api10_FacebookApiErrorCode::API_EC_PARAM,
               'error_msg' => 'Your feed story contains illegal or malformed content.'));
        default:
          $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_MARKUP);
          break;
        }
      }

      // Check call limits
      if (application_add_publish_action_call($this->app_id, $this->user_id) === false) {
        $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_FEED_TOO_MANY_USER_ACTION_CALLS);
      }

      $result = application_publish_action_of_user($this->app_id, $this->user_id, $feed_story);

      return array($result);
    }

  public function friends_get($uid, $flid) {
    if ($flid) {
      return $this->friends_getListMembers($flid);
    }
    $friends_uid = $this->user_id;

    return user_get_all_friends($friends_uid);
  }

  public function friends_getAppUsers() {
    $friend_list = user_get_all_friends($this->user_id);

    $result = array();
    foreach ($friend_list as $friend_id) {
      if (platform_app_has_full_permission($this->app_id, $friend_id)) {
        $result []= $friend_id;
      }
    }
    return $result;
  }

  public function friends_areFriends($ids1, $ids2) {
    $ids1 = $this->get_ids_from_list($ids1);
    $ids2 = $this->get_ids_from_list($ids2);
    $count = count($ids1);
    if (count($ids2) != $count) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
    }

    $result = array();
    for ($i = 0; $i < $count; $i++) {
      $id1 = $ids1[$i];
      $id2 = $ids2[$i];
      if (!is_numeric($id1) || !is_numeric($id2)) {
        $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
      }
      $result[$i] = new api10_friend_info(array('uid1' => $id1,
        'uid2' => $id2,
        'are_friends' => are_friends($id1, $id2)
        ));
    }
    return $result;
  }

  public function friends_getLists() {
    $array_lists = friend_list_get_lists($this->user_id);
    $object_lists = array();
    foreach ($array_lists as $flid => $list) {
      $object_lists []= new api10_friendlist(array('flid' => $flid,
                                                   'name' => $list['name']));
    }
    return $object_lists;
  }

  protected function friends_getListMembers($flid) {
    if (!$flid || !friend_list_is_owner($flid, $this->user_id)) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
    }
    $members = friend_list_get_members($flid);
    if (!is_array($members)) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_UNKNOWN);
    }
    return $members;
  }

  public function profile_setFBML($markup, $uid, $profile, $profile_action, $mobile_profile) {
    $app_info = application_get_info($this->app_id);

    // no logged in user
    if (!$this->user_id) {
      if ($app_info['desktop']) {
        // Desktop app cannot call profile.setFBML without a session key since they can't set it on anyone
        // other than the logged in user, and since there's no logged in user...
        $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM_SESSION_KEY);
      }

      if (!$uid) {
        // They have to specify who they are setting it on...
        $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
      }
    }

    $id = $uid ? $uid : $this->user_id;

    if ($id != $this->user_id) {
      if ($app_info['desktop'] || $this->using_session_secret) {
        // setting markup on others' profiles is not allowed for
        // desktop apps or API that's validated using session secret
        $this->throw_code(api10_FacebookApiErrorCode::API_EC_PERMISSION_MARKUP_OTHER_USER);
      }
    }

    // $markup is a deprecated parameter that serves the same purpose as $profile
    $profile = $profile ? $profile : $markup;

    // FBOPEN:NOTE - here, you may wish to use the fbml (as in canvas.php)
    // to verify the FBML set.

    if (!profile_app_set_fbml($id, $this->app_id, $profile)) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_EDIT_MARKUP);
    }

    return true;
  }

  public function profile_getFBML($uid) {

    if (!$this->user_id && !$uid) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
    }

    $id = $uid ? $uid : $this->user_id;

    if ($id != $this->user_id && $this->using_session_secret) {
      // getting markup on others' profiles is not allowed for
      // apps using session secret
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PERMISSION_MARKUP_OTHER_USER);
    }

    if ($fbml = profile_app_get_fbml($id, $this->app_id)) {
      return $fbml;
    } else {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_UNKNOWN);
    }
  }

  public function users_getInfo($users, $fields) {
     return $this->getInfo($users, $fields, 'user', 'uid');
  }

  protected function getInfo($ids, $fields, $table, $id_column) {
    // $ids and $fields are given to us as comma-separated lists, so we can just use them as-is
    $query = sprintf('SELECT ' . $id_column . ', ' . $fields . ' FROM ' . $table . ' WHERE ' . $id_column . ' IN (' .
                     $ids . ')');

    // need to use query_xml here because we want to preserve order and you can
    // have multiple fields with the same name, etc.
    switch ($this->format) {
    case 'json':
      return $this->parser->query_thrift($query);
    case 'xml':
    default:
      return $this->parser->query_xml($query);
    }
  }

  public function users_isAppAdded() {
    if (!$id) {
      $id = $this->user_id;
    }

    $result = is_platform_app_installed($this->app_id, $id);
    // We check for null because that means there was a data retrieval error
    if ($result === null) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_SERVICE);
    }

    return $result;
  }

  public function users_getLoggedInUser() {
    return $this->user_id;
  }

  public function admin_getAppProperties($properties) {
    $app_info = application_get_info($this->app_id);
    $ret = array();
    $app_fields = get_editable_app_fields();
    foreach($properties as $prop) {
      if (isset($app_fields[$prop])) {
        $ret[$prop] = $app_info[$prop];
      } else {
        $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM);
      }
    }
    return json_encode($ret);
  }

  public function admin_setAppProperties($properties) {
    $props = json_decode($properties, true);
    if (!is_array($props)) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_PARAM_BAD_JSON);
    }
    $app_profile = array();
    $app_fields = get_editable_app_fields();
    foreach ($props as $key => $val) {
      if (!isset($app_fields[$key])) {
        throw new api10_FacebookApiException(array('error_code' => api10_FacebookApiErrorCode::API_EC_PARAM,
                                                   'error_msg' => $key . ' is not a valid application property.'));
      }
      $app_profile[$key] = $val;
    }
    return application_update($this->app_id, $app_profile, $this->user_id);
  }

  public function application_getPublicInfo($application_id,
                                            $application_api_key,
                                            $application_canvas_name) {
    $param_ec = api10_FacebookApiErrorCode::API_EC_PARAM;
    $param_msg = 'Exactly one of application_id, api_key, or canvas_name is required.';

    // parameter checking
    if ($application_api_key) {
      if ($application_id || $application_canvas_name) {
        throw new api10_FacebookApiException(array('error_code' => $param_ec,
                                                   'error_msg' => $param_msg));
      }
      $app_id = application_get_id_from_key($application_api_key);

    } else if ($application_canvas_name) {
      if ($application_id) {
        throw new api10_FacebookApiException(array('error_code' => $param_ec,
                                                   'error_msg' => $param_msg));
      }
      $app_id = application_get_fbframe_id($application_canvas_name);

    } else if ($application_id) {
      $app_id = $application_id;

    } else {
      throw new api10_FacebookApiException(array('error_code' => $param_ec,
                                                 'error_msg' => $param_msg));
    }

    if (!$app_id) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_NO_SUCH_APP);
    }

    // get the info first, as it allows us to do the rest of this efficiently
    $app_info = application_get_info($app_id);
    if (!$app_info) {
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_NO_SUCH_APP);
    }

    $result = new api10_app_info();
    $result->app_id = $app_info['application_id'];
    $result->api_key = $app_info['apikey'];
    $result->canvas_name = application_get_fbframe_name($app_id);
    $result->display_name = application_get_name($app_id, $app_info);
    $result->icon_url = application_get_icon_url($app_id, $app_info);
    $result->logo_url = application_get_logo_url($app_id, $app_info);
    $result->description = $app_info['description'];
    $result->developers = array();
    $result->company_name = '';

    // figure out whether to return the developers or company name
    $company_name = $app_info['company_name'];
    $result->company_name = $company_name;

      $dev_ids = application_get_owners($app_id);
      foreach ($dev_ids as $dev_id) {
        $developer_info = new api10_developer_info();
        $developer_info->uid = $dev_id;
        $developer_info->name = user_get_name($dev_id);
        $result->developers[] = $developer_info;

      }

    return $result;
  }

  /*
   * Execute a series of methods and return all the results together
   */
  public function batch_run($method_feed, $serial_only) {
    $server_url = 'http://' . $API_HOST . '/restserver.php';

    $results = array();

    $method_count = count($method_feed);
    if ($method_count == 0) {
      return null;
    } elseif ($method_count > 20) {
      // Out of concerns about possible DOS attacks, we are limiting total number of
      // methods in a feed to 20 for now
      $this->throw_code(api10_FacebookApiErrorCode::API_EC_BATCH_TOO_MANY_ITEMS);
    }

    if ($serial_only) {
      $count_curl = 0;
    } else {
      // To reduce overall server load while still have fast response, we will execute up to 5
      // methods in the current PHP process and dispath the rest
      // as different HTTP Requests
      $count_curl = max(0, $method_count - 5);
    }

    if ($count_curl > 0) {
      // create the multiple cURL handle
      $mh = curl_multi_init();
      $chs = array();

      for ( $i = 0 ; $i < $count_curl ; $i++ ) {
        $post_fields = $method_feed[$i];
        $ch = self::create_single_method_curl_resource($server_url, $post_fields);
        curl_multi_add_handle($mh, $ch);
        $chs[$i] = $ch;
      }

      // start performing the request, but don't wait for completion until later
      do {
        $mrc = curl_multi_exec($mh, $running);
      } while ( $mrc == CURLM_CALL_MULTI_PERFORM );

      // First, I have to fill empty value for the curl results because PHP takes
      // the order of insertion over the index value.
      for ( $i = 0 ; $i < $count_curl ; $i++ ) {
        $results[$i] = null;
      }
    }

    // Now execute the some methods in the current process
    for ( $i = $count_curl ; $i < $method_count ; $i++ ) {
      $post_fields = $method_feed[$i];
      $request = self::read_querystring($post_fields);
      $results[$i] = $this->process_request($request);
    }

    if ($count_curl > 0) {
      //Wait for curl to complete
      while ( $running > 0 ) {
        curl_multi_exec($mh, $running);
      }

      for ( $i = 0 ; $i < $count_curl ; $i++ ) {
        $ch = $chs[$i];
        $results[$i] = curl_multi_getcontent($ch);
        // close the handle
        curl_multi_remove_handle($mh, $ch);
      }

      curl_multi_close($mh);
    }

    return $results;
  }

  // Create a curl resource given a request
  private static function create_single_method_curl_resource($server_url, $post_fields) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $server_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook API PHP5 Batch Sub Request (curl) ');
    return $ch;
  }

  private function check_throttle($method_underscore, $request) {
    $app_info = application_get_info($this->app_id);

    if ($app_info['desktop']) {
      if ($throttle && ($ec = api_desktop_check_call_limit($this->app_id, $this->session_key)) !== API_EC_SUCCESS) {
        return $ec;
      }
    } else {
      if ($app_info['ip_list'] && !iplist_contains_ip($app_info['ip_list'], $_SERVER['REMOTE_ADDR'])) {
        return API_EC_BAD_IP;
      }

      // FBOPEN: NOTE - you may wish to throttle only certain methods here.
      if (($ec = api_server_check_call_limit($this->app_id)) !== API_EC_SUCCESS) {
        return $ec;
      }
    }

    return API_EC_SUCCESS;
  }

  // Given a querystring, return a array of name/value pair where value has been url decoded
  private static function read_querystring($qs) {

    $parts = qs_vars($qs);
    foreach ( $parts as $key => $val ) {
      $parts[$key] = urldecode($val);
    }

    return $parts;
  }

  private function process_request($request) {
    global $API_DOMAIN, $API_DOMAIN_DOT_SUFFIX;

    $app_id = $this->app_id;

    $method = $request['method'];
    $callback = false;

    $serialized_result = '';

    // Initialize result
    $result = array();

    // Fix method name
    if (starts_with($method, $API_DOMAIN.'.')) {
      $method = substr($method, 9);
    }

    // Replace periods with underscores in method name
    $method_underscore = str_replace('.', '_', $method);

    $ec = $this->check_throttle($method_underscore, $request);

    if ($ec !== API_EC_SUCCESS) {
      $msg = $api_error_descriptions[$ec];
      if ($ec === API_EC_BAD_IP) {
        $msg .= ' (ip was: ' . $_SERVER['REMOTE_ADDR'] . ')';
      }
      throw new api10_FacebookApiException(array('error_code' => $ec, 'error_msg' => $msg));
    }

    $impl = new FacebookApi10Implementation($app_id, $this->user_id, $this->session_key, $this->format);
    $api = new FacebookApi10Rest($impl);

    // Check that the method is valid
    if (!method_exists($api, $method_underscore) || !method_exists($impl, $method_underscore) || !api_can_call_method($app_id, $method_underscore)) {
      $ec = api10_FacebookApiErrorCode::API_EC_METHOD;
      throw new api10_FacebookApiException(array('error_code' => $ec, 'error_msg' => $GLOBALS['api_error_descriptions'][$ec]));
    } else {
      // Call the method and catch any exceptions
      $result = $api->$method_underscore($request);
    }


    switch ( $this->format) {
      case 'manual' :
        print api_xml_render_manual_error($ec, $msg, $request);
      break;

      case 'xml' :
        // Prepare the XML response
        $xml_memory = xmlwriter_open_memory();
        xmlwriter_set_indent($xml_memory, true);
        xmlwriter_set_indent_string($xml_memory, '  ');
        xmlwriter_start_document($xml_memory, API_VERSION_10, 'UTF-8');

        if ($result instanceof Exception) {
          $name = 'error_response';
        } else {
          $name = $method_underscore . '_response';
        }
        $attrs = array();

        // FBOPEN:NOTE here, if you are not publishing your own .xsd, to use 'facebook.com' instead
        // of $API_DOMAIN_DOT_SUFFIX
        $attrs['xmlns'] = 'http://api.'.$API_DOMAIN_DOT_SUFFIX.'/' . API_VERSION_10 . '/';
        $attrs['xmlns:xsi'] = 'http://www.w3.org/2001/XMLSchema-instance';
        if ($method_underscore != 'fql_query') {
          $attrs['xsi:schemaLocation'] = 'http://api.'.$API_DOMAIN_DOT_SUFFIX.'/' . API_VERSION_10 . '/ http://api.'.$API_DOMAIN_DOT_SUFFIX.'/' . API_VERSION_10 . '/facebook.xsd';
        }
        if (is_array($result) && isset($result[0]) && $result[0] instanceof xml_element) {
          $attrs['list'] = 'true';
          api_xml3_render_object($xml_memory, new xml_element($name, $result, $attrs));
        } else {
          api_xml2_render_object($xml_memory, $name, $result, $attrs);
        }

        xmlwriter_end_document($xml_memory);

        // Write XML response
        $xml = xmlwriter_output_memory($xml_memory, true);
        if ($callback) {
          $xml = addslashes($xml);
          $xml = str_replace("\n", '\\n', $xml);
          $serialized_result = $callback . '(\'' . $xml . '\');';
        } else {
          $serialized_result = $xml;
        }
      break;

      case 'json' :
        $json = api_json2_render_object($result);
        if ($callback) {
          $serialized_result = $callback . '(' . $json . ');';
        } else {
          $serialized_result = $json;
        }
      break;
    }

    return $serialized_result;
  }
}

