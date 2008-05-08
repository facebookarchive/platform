<?php
//
// +---------------------------------------------------------------------------+
// | Facebook Platform PHP4 client                                 |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007 Facebook, Inc.                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | 1. Redistributions of source code must retain the above copyright         |
// |    notice, this list of conditions and the following disclaimer.          |
// | 2. Redistributions in binary form must reproduce the above copyright      |
// |    notice, this list of conditions and the following disclaimer in the    |
// |    documentation and/or other materials provided with the distribution.   |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR      |
// | IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES |
// | OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.   |
// | IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT  |
// | NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF  |
// | THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.         |
// +---------------------------------------------------------------------------+
// | For help with this library, contact developers-help@facebook.com          |
// +---------------------------------------------------------------------------+
//

include_once 'simplexml44-0_4_4/class/IsterXmlSimpleXMLImpl.php';

class FacebookRestClient {
  var $secret;
  var $session_key;
  var $api_key;
  var $facebook;
  var $error_code;
  var $friends_list; // to save making the friends.get api call, this will get prepopulated on canvas pages
  var $added;        // to save making the users.isAppAdded api call, this will get prepopulated on canvas pages
  var $call_as_apikey;

  /**
   * Create the client.
   * @param string $session_key if you haven't gotten a session key yet, leave
   *                            this as null and then set it later by just
   *                            directly accessing the $session_key member
   *                            variable.
   */
  function FacebookRestClient($api_key, $secret, &$facebook, $session_key=null) {
    $this->secret       = $secret;
    $this->session_key  = $session_key;
    $this->api_key      = $api_key;
    $this->last_call_id = 0;
    $this->facebook     = &$facebook;
    $this->error_code   = 0;
    $this->call_as_apikey = '';
    $this->server_addr  = $this->facebook->get_facebook_url('api') . '/restserver.php';
    if (!empty($GLOBALS['facebook_config']['debug'])) {
      $this->cur_id = 0;
      ?>
<script type="text/javascript">
var types = ['params', 'xml', 'php', 'sxml'];
function getStyle(elem, style) {
  if (elem.getStyle) {
    return elem.getStyle(style);
  } else {
    return elem.style[style];
  }
}
function setStyle(elem, style, value) {
  if (elem.setStyle) {
    elem.setStyle(style, value);
  } else {
    elem.style[style] = value;
  }
}
function toggleDisplay(id, type) {
  for (var i = 0; i < types.length; i++) {
    var t = types[i];
    var pre = document.getElementById(t + id);
    if (pre) {
      if (t != type || getStyle(pre, 'display') == 'block') {
        setStyle(pre, 'display', 'none');
      } else {
        setStyle(pre, 'display', 'block');
      }
    }
  }
  return false;
}
</script>
<?php
    }
  }

  function begin_permissions_mode($permissions_apikey) {
    $this->call_as_apikey = $permissions_apikey;
  }

  function end_permissions_mode() {
    $this->call_as_apikey = '';
  }

  /**
   * Returns the session information available after current user logs in.
   * @param string $auth_token the token returned by auth_createToken or
   *  passed back to your callback_url.
   * @param bool   $generate_session_secret  whether the session returned should include a session secret
   * @return assoc array containing session_key, uid
   */
  function auth_getSession($auth_token, $generate_session_secret=false) {
    $result = $this->call_method('facebook.auth.getSession',
        array('auth_token'=>$auth_token, 'generate_session_secret' => $generate_session_secret));
    $this->session_key = $result['session_key'];
    if (!empty($result['secret']) && !$generate_session_secret) {
      // desktop apps have a special secret
      $this->secret = $result['secret'];
    }
    return $result;
  }

  /**
   * Generates a session specific secret. This is for integration with client-side API calls, such as the
   * JS library.
   * @error API_EC_PARAM_SESSION_KEY
   *        API_EC_PARAM_UNKNOWN
   * @return session secret for the current promoted session
   */
  function auth_promoteSession() {
    return $this->call_method('facebook.auth.promoteSession', array());
  }

  /**
   * Expires the session that is currently being used.  If this call is successful, no further calls to the
   * API (which require a session) can be made until a valid session is created.
   *
   * @return bool  true if session expiration was successful, false otherwise
   */
  function auth_expireSession() {
    return $this->call_method('facebook.auth.expireSession', array());
  }


  /**
   * Returns events according to the filters specified.
   * @param int $uid Optional: User associated with events.
   *   A null parameter will default to the session user.
   * @param array $eids Optional: Filter by these event ids.
   *   A null parameter will get all events for the user.
   * @param int $start_time Optional: Filter with this UTC as lower bound.
   *   A null or zero parameter indicates no lower bound.
   * @param int $end_time Optional: Filter with this UTC as upper bound.
   *   A null or zero parameter indicates no upper bound.
   * @param string $rsvp_status Optional: Only show events where the given uid
   *   has this rsvp status.  This only works if you have specified a value for
   *   $uid.  Values are as in events.getMembers.  Null indicates to ignore
   *   rsvp status when filtering.
   * @return array of events
   */
  function events_get($uid, $eids, $start_time, $end_time, $rsvp_status) {
    return $this->call_method('facebook.events.get',
        array(
        'uid' => $uid,
        'eids' => $eids,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'rsvp_status' => $rsvp_status));
  }

  /**
   * Returns membership list data associated with an event
   * @param int $eid : event id
   * @return assoc array of four membership lists, with keys 'attending',
   *  'unsure', 'declined', and 'not_replied'
   */
  function events_getMembers($eid) {
    return $this->call_method('facebook.events.getMembers',
      array('eid' => $eid));
  }

  /**
   * Makes an FQL query.  This is a generalized way of accessing all the data
   * in the API, as an alternative to most of the other method calls.  More
   * info at http://developers.facebook.com/documentation.php?v=1.0&doc=fql
   * @param string $query the query to evaluate
   * @return generalized array representing the results
   */
  function fql_query($query) {
    return $this->call_method('facebook.fql.query',
      array('query' => $query));
  }

  function feed_publishStoryToUser($title, $body,
                                   $image_1=null, $image_1_link=null,
                                   $image_2=null, $image_2_link=null,
                                   $image_3=null, $image_3_link=null,
                                   $image_4=null, $image_4_link=null) {
    return $this->call_method('facebook.feed.publishStoryToUser',
      array('title' => $title,
            'body' => $body,
            'image_1' => $image_1,
            'image_1_link' => $image_1_link,
            'image_2' => $image_2,
            'image_2_link' => $image_2_link,
            'image_3' => $image_3,
            'image_3_link' => $image_3_link,
            'image_4' => $image_4,
            'image_4_link' => $image_4_link));
  }

  function feed_publishActionOfUser($title, $body,
                                    $image_1=null, $image_1_link=null,
                                    $image_2=null, $image_2_link=null,
                                    $image_3=null, $image_3_link=null,
                                    $image_4=null, $image_4_link=null) {
    return $this->call_method('facebook.feed.publishActionOfUser',
      array('title' => $title,
            'body' => $body,
            'image_1' => $image_1,
            'image_1_link' => $image_1_link,
            'image_2' => $image_2,
            'image_2_link' => $image_2_link,
            'image_3' => $image_3,
            'image_3_link' => $image_3_link,
            'image_4' => $image_4,
            'image_4_link' => $image_4_link));
  }

  function feed_publishTemplatizedAction($title_template, $title_data,
                                         $body_template, $body_data, $body_general,
                                         $image_1=null, $image_1_link=null,
                                         $image_2=null, $image_2_link=null,
                                         $image_3=null, $image_3_link=null,
                                         $image_4=null, $image_4_link=null,
                                         $target_ids='', $page_actor_id=null) {
    return $this->call_method('facebook.feed.publishTemplatizedAction',
      array('title_template' => $title_template,
            'title_data' => $title_data,
            'body_template' => $body_template,
            'body_data' => $body_data,
            'body_general' => $body_general,
            'image_1' => $image_1,
            'image_1_link' => $image_1_link,
            'image_2' => $image_2,
            'image_2_link' => $image_2_link,
            'image_3' => $image_3,
            'image_3_link' => $image_3_link,
            'image_4' => $image_4,
            'image_4_link' => $image_4_link,
            'target_ids' => $target_ids,
            'page_actor_id' => $page_actor_id));
  }

  /**
   * Returns whether or not pairs of users are friends.
   * Note that the Facebook friend relationship is symmetric.
   * @param array $uids1: array of ids (id_1, id_2,...) of some length X
   * @param array $uids2: array of ids (id_A, id_B,...) of SAME length X
   * @return array of uid pairs with bool, true if pair are friends, e.g.
   *   array( 0 => array('uid1' => id_1, 'uid2' => id_A, 'are_friends' => 1),
   *          1 => array('uid1' => id_2, 'uid2' => id_B, 'are_friends' => 0)
   *         ...)
   */
  function friends_areFriends($uids1, $uids2) {
    return $this->call_method('facebook.friends.areFriends',
        array('uids1'=>$uids1, 'uids2'=>$uids2));
  }

  /**
   * Returns the friends of the current session user.
   * @return array of friends
   */
  function friends_get() {
    if (isset($this->friends_list)) {
      return $this->friends_list;
    }
    return $this->call_method('facebook.friends.get', array());
  }

  /**
   * Returns the friends of the session user, who are also users
   * of the calling application.
   * @return array of friends
   */
  function friends_getAppUsers() {
    return $this->call_method('facebook.friends.getAppUsers', array());
  }

  /**
   * Returns groups according to the filters specified.
   * @param int $uid Optional: User associated with groups.
   *  A null parameter will default to the session user.
   * @param array $gids Optional: group ids to query.
   *   A null parameter will get all groups for the user.
   * @return array of groups
   */
  function groups_get($uid, $gids) {
    return $this->call_method('facebook.groups.get',
        array(
        'uid' => $uid,
        'gids' => $gids));
  }

  /**
   * Returns the membership list of a group
   * @param int $gid : Group id
   * @return assoc array of four membership lists, with keys
   *  'members', 'admins', 'officers', and 'not_replied'
   */
  function groups_getMembers($gid) {
    return $this->call_method('facebook.groups.getMembers',
      array('gid' => $gid));
  }

  /**
   * Returns cookies according to the filters specified.
   * @param int $uid Required: User for which the cookies are needed.
   * @param string $name Optional:
   *   A null parameter will get all cookies for the user.
   * @return array of cookies
   */
  function data_getCookies($uid, $name) {
    return $this->call_method('facebook.data.getCookies',
        array(
        'uid' => $uid,
        'name' => $name));
  }

  /**
   * Sets cookies according to the params specified.
   * @param int $uid Required: User for which the cookies are needed.
   * @param string $name Required: name of the cookie
   * @param string $value Optional if expires specified and is in the past
   * @param int$expires Optional
   * @param string $path Optional
   *
   * @return bool
   */
  function data_setCookie($uid, $name, $value, $expires, $path) {
    return $this->call_method('facebook.data.setCookie',
        array(
        'uid' => $uid,
        'name' => $name,
        'value' => $value,
        'expires' => $expires,
        'path' => $path));
  }

  /**
   * Permissions API
   */

  /**
   * Checks API-access granted by self to the specified application
   * @param string $permissions_apikey: Required
   *
   * @return array: API methods/namespaces which are allowed access
   */
  function permissions_checkGrantedApiAccess($permissions_apikey) {
    return $this->call_method('facebook.permissions.checkGrantedApiAccess',
        array(
        'permissions_apikey' => $permissions_apikey));
  }

  /**
   * Checks API-access granted to self by the specified application
   * @param string $permissions_apikey: Required
   *
   * @return array: API methods/namespaces which are allowed access
   */
  function permissions_checkAvailableApiAccess($permissions_apikey) {
    return $this->call_method('facebook.permissions.checkAvailableApiAccess',
        array(
        'permissions_apikey' => $permissions_apikey));
  }

  /**
   * Grant API-access to the specified methods/namespaces to the specified application
   * @param string $permissions_apikey: Required
   * @param array(string) : Optional: API methods/namespaces to be allowed
   *
   * @return array: API methods/namespaces which are allowed access
   */
  function permissions_grantApiAccess($permissions_apikey, $method_arr) {
    return $this->call_method('facebook.permissions.grantApiAccess',
        array(
        'permissions_apikey' => $permissions_apikey,
        'method_arr' => $method_arr));
  }

  /**
   * Revoke API-access granted to the specified application
   * @param string $permissions_apikey: Required
   *
   * @return bool
   */
  function permissions_revokeApiAccess($permissions_apikey) {
    return $this->call_method('facebook.permissions.revokeApiAccess',
        array(
        'permissions_apikey' => $permissions_apikey));
  }


  /**
   * Returns the outstanding notifications for the session user.
   * @return assoc array of
   *  notification count objects for 'messages', 'pokes' and 'shares',
   *  a uid list of 'friend_requests', a gid list of 'group_invites',
   *  and an eid list of 'event_invites'
   */
  function notifications_get() {
    return $this->call_method('facebook.notifications.get', array());
  }

  /**
   * Sends a notification to the specified users.
   * @return (nothing)
   */
  function notifications_send($to_ids, $notification) {
    return $this->call_method('facebook.notifications.send',
                              array('to_ids' => $to_ids, 'notification' => $notification));
  }

  /**
   * Sends an email to the specified user of the application.
   * @param array $recipients : ids of the recipients
   * @param string $subject : subject of the email
   * @param string $text : (plain text) body of the email
   * @param string $fbml : fbml markup if you want an html version of the email
   * @return comma separated list of successful recipients
   */
  function notifications_sendEmail($recipients, $subject, $text, $fbml) {
    return $this->call_method('facebook.notifications.sendEmail',
                              array('recipients' => $recipients,
                                    'subject' => $subject,
                                    'text' => $text,
                                    'fbml' => $fbml));
  }

  /**
   * Returns the requested info fields for the requested set of pages
   * @param array $page_ids an array of page ids
   * @param array $fields an array of strings describing the info fields desired
   * @param int $uid   Optionally, limit results to pages of which this user is a fan.
   * @param string type  limits results to a particular type of page.
   * @return array of pages
   */
  function pages_getInfo($page_ids, $fields, $uid, $type) {
    return $this->call_method('facebook.pages.getInfo', array('page_ids' => $page_ids, 'fields' => $fields, 'uid' => $uid, 'type' => $type));
  }

  /**
   * Returns true if logged in user is an admin for the passed page
   * @param int $page_id target page id
   * @return boolean
   */
  function pages_isAdmin($page_id) {
    return $this->call_method('facebook.pages.isAdmin', array('page_id' => $page_id));
  }

  /**
   * Returns whether or not the page corresponding to the current session object has the app installed
   * @return boolean
   */
  function pages_isAppAdded() {
    if (isset($this->added)) {
      return $this->added;
    }
    return $this->call_method('facebook.pages.isAppAdded', array());
  }

  /**
   * Returns true if logged in user is a fan for the passed page
   * @param int $page_id target page id
   * @param int $uid user to compare.  If empty, the logged in user.
   * @return bool
   */
  function pages_isFan($page_id, $uid) {
    return $this->call_method('facebook.pages.isFan', array('page_id' => $page_id, 'uid' => $uid));
  }

  /**
   * Returns photos according to the filters specified.
   * @param int $subj_id Optional: Filter by uid of user tagged in the photos.
   * @param int $aid Optional: Filter by an album, as returned by
   *  photos_getAlbums.
   * @param array $pids Optional: Restrict to a list of pids
   * Note that at least one of these parameters needs to be specified, or an
   * error is returned.
   * @return array of photo objects.
   */
  function photos_get($subj_id, $aid, $pids) {
    return $this->call_method('facebook.photos.get',
      array('subj_id' => $subj_id, 'aid' => $aid, 'pids' => $pids));
  }

  /**
   * Returns the albums created by the given user.
   * @param int $uid Optional: the uid of the user whose albums you want.
   *   A null value will return the albums of the session user.
   * @param array $aids Optional: a list of aids to restrict the query.
   * Note that at least one of the (uid, aids) parameters must be specified.
   * @returns an array of album objects.
   */
  function photos_getAlbums($uid, $aids) {
    return $this->call_method('facebook.photos.getAlbums',
      array('uid' => $uid,
            'aids' => $aids));
  }

  /**
   * Returns the tags on all photos specified.
   * @param string $pids : a list of pids to query
   * @return array of photo tag objects, with include pid, subject uid,
   *  and two floating-point numbers (xcoord, ycoord) for tag pixel location
   */
  function photos_getTags($pids) {
    return $this->call_method('facebook.photos.getTags',
      array('pids' => $pids));
  }

  /**
   * Returns the requested info fields for the requested set of users
   * @param array $uids an array of user ids
   * @param array $fields an array of strings describing the info fields desired
   * @return array of users
   */
  function users_getInfo($uids, $fields) {
    return $this->call_method('facebook.users.getInfo', array('uids' => $uids, 'fields' => $fields));
  }

  /**
   * Returns the user corresponding to the current session object.
   * @return integer uid
   */
  function users_getLoggedInUser() {
    return $this->call_method('facebook.users.getLoggedInUser', array());
  }


  /**
   * Returns whether or not the user corresponding to the current session object has the app installed
   * @return boolean
   */
  function users_isAppAdded($uid=null) {
    if (isset($this->added)) {
      return $this->added;
    }
    return $this->call_method('facebook.users.isAppAdded', array('uid' => $uid));
  }

  /**
   * Sets the FBML for the profile of the user attached to this session
   * @param   string   $markup     The FBML that describes the profile presence of this app for the user
   * @param   int      $uid              The user
   * @param   string   $profile          Profile FBML
   * @param   string   $profile_action   Profile action FBML
   * @param   string   $mobile_profile   Mobile profile FBML
   * @return  array    A list of strings describing any compile errors for the submitted FBML
   */
  function profile_setFBML($markup, $uid = null, $profile='', $profile_action='', $mobile_profile='') {
    return $this->call_method('facebook.profile.setFBML', array('markup' => $markup,
                                                                'uid' => $uid,
                                                                'profile' => $profile,
                                                                'profile_action' => $profile_action,
                                                                'mobile_profile' => $mobile_profile));
  }

  function profile_getFBML($uid) {
    return $this->call_method('facebook.profile.getFBML', array('uid' => $uid));
  }

  function fbml_refreshImgSrc($url) {
    return $this->call_method('facebook.fbml.refreshImgSrc', array('url' => $url));
  }

  function fbml_refreshRefUrl($url) {
    return $this->call_method('facebook.fbml.refreshRefUrl', array('url' => $url));
  }

  function fbml_setRefHandle($handle, $fbml) {
    return $this->call_method('facebook.fbml.setRefHandle', array('handle' => $handle, 'fbml' => $fbml));
  }

  /**
   * Get all the marketplace categories
   *
   * @return array  A list of category names
   */
  function marketplace_getCategories() {
    return $this->call_method('facebook.marketplace.getCategories', array());
  }

  /**
   * Get all the marketplace subcategories for a particular category
   *
   * @param  category  The category for which we are pulling subcategories
   * @return array     A list of subcategory names
   */
  function marketplace_getSubCategories($category) {
    return $this->call_method('facebook.marketplace.getSubCategories', array('category' => $category));
  }

  /**
   * Get listings by either listing_id or user
   *
   * @param listing_ids   An array of listing_ids (optional)
   * @param uids          An array of user ids (optional)
   * @return array        The data for matched listings
   */
  function marketplace_getListings($listing_ids, $uids) {
    return $this->call_method('facebook.marketplace.getListings', array('listing_ids' => $listing_ids, 'uids' => $uids));
  }

  /**
   * Search for Marketplace listings.  All arguments are optional, though at least
   * one must be filled out to retrieve results.
   *
   * @param category     The category in which to search (optional)
   * @param subcategory  The subcategory in which to search (optional)
   * @param query        A query string (optional)
   * @return array       The data for matched listings
   */
  function marketplace_search($category, $subcategory, $query) {
    return $this->call_method('facebook.marketplace.search', array('category' => $category, 'subcategory' => $subcategory, 'query' => $query));
  }

  /**
   * Remove a listing from Marketplace
   *
   * @param listing_id  The id of the listing to be removed
   * @param status      'SUCCESS', 'NOT_SUCCESS', or 'DEFAULT'
   * @return bool       True on success
   */
  function marketplace_removeListing($listing_id, $status='DEFAULT', $uid=null) {
    return $this->call_method('facebook.marketplace.removeListing',
                              array('listing_id'=>$listing_id,
                                    'status'=>$status,
                                    'uid' => $uid));
  }

  /**
   * Create/modify a Marketplace listing for the loggedinuser
   *
   * @param int              listing_id   The id of a listing to be modified, 0 for a new listing.
   * @param show_on_profile  bool         Should we show this listing on the user's profile
   * @param attrs            array        An array of the listing data
   * @return                 int          The listing_id (unchanged if modifying an existing listing)
   */
  function marketplace_createListing($listing_id, $show_on_profile, $attrs, $uid=null) {
    return $this->call_method('facebook.marketplace.createListing',
                              array('listing_id'=>$listing_id,
                                    'show_on_profile'=>$show_on_profile,
                                    'listing_attrs'=>json_encode($attrs),
                                    'uid' => $uid));
  }


  /////////////////////////////////////////////////////////////////////////////
  // Data Store API

  /**
   * Set a user preference.
   *
   * @param  pref_id    preference identifier (0-200)
   * @param  value      preferece's value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_setUserPreference($pref_id, $value) {
    return $this->call_method
      ('facebook.data.setUserPreference',
       array('pref_id' => $pref_id,
             'value' => $value));
  }

  /**
   * Set a user's all preferences for this application.
   *
   * @param  values     preferece values in an associative arrays
   * @param  replace    whether to replace all existing preferences or
   *                    merge into them.
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_setUserPreferences($values, $replace = false) {
    return $this->call_method
      ('facebook.data.setUserPreferences',
       array('values' => json_encode($values),
             'replace' => $replace));
  }

  /**
   * Get a user preference.
   *
   * @param  pref_id    preference identifier (0-200)
   * @return            preference's value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getUserPreference($pref_id) {
    return $this->call_method
      ('facebook.data.getUserPreference',
       array('pref_id' => $pref_id));
  }

  /**
   * Get a user preference.
   *
   * @return           preference values
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getUserPreferences() {
    return $this->call_method
      ('facebook.data.getUserPreferences',
       array());
  }

  /**
   * Create a new object type.
   *
   * @param  name       object type's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_createObjectType($name) {
    return $this->call_method
      ('facebook.data.createObjectType',
       array('name' => $name));
  }

  /**
   * Delete an object type.
   *
   * @param  obj_type       object type's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_dropObjectType($obj_type) {
    return $this->call_method
      ('facebook.data.dropObjectType',
       array('obj_type' => $obj_type));
  }

  /**
   * Rename an object type.
   *
   * @param  obj_type       object type's name
   * @param  new_name       new object type's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_renameObjectType($obj_type, $new_name) {
    return $this->call_method
      ('facebook.data.renameObjectType',
       array('obj_type' => $obj_type,
             'new_name' => $new_name));
  }

  /**
   * Add a new property to an object type.
   *
   * @param  obj_type       object type's name
   * @param  prop_name      name of the property to add
   * @param  prop_type      1: integer; 2: string; 3: text blob
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_defineObjectProperty($obj_type, $prop_name, $prop_type) {
    return $this->call_method
      ('facebook.data.defineObjectProperty',
       array('obj_type' => $obj_type,
             'prop_name' => $prop_name,
             'prop_type' => $prop_type));
  }

  /**
   * Remove a previously defined property from an object type.
   *
   * @param  obj_type      object type's name
   * @param  prop_name     name of the property to remove
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_undefineObjectProperty($obj_type, $prop_name) {
    return $this->call_method
      ('facebook.data.undefineObjectProperty',
       array('obj_type' => $obj_type,
             'prop_name' => $prop_name));
  }

  /**
   * Rename a previously defined property of an object type.
   *
   * @param  obj_type      object type's name
   * @param  prop_name     name of the property to rename
   * @param  new_name      new name to use
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_renameObjectProperty($obj_type, $prop_name, $new_name) {
    return $this->call_method
      ('facebook.data.renameObjectProperty',
       array('obj_type' => $obj_type,
             'prop_name' => $prop_name,
             'new_name' => $new_name));
  }

  /**
   * Retrieve a list of all object types that have defined for the application.
   *
   * @return               a list of object type names
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getObjectTypes() {
    return $this->call_method
      ('facebook.data.getObjectTypes',
       array());
  }

  /**
   * Get definitions of all properties of an object type.
   *
   * @param obj_type       object type's name
   * @return               pairs of property name and property types
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getObjectType($obj_type) {
    return $this->call_method
      ('facebook.data.getObjectType',
       array('obj_type' => $obj_type));
  }

  /**
   * Create a new object.
   *
   * @param  obj_type      object type's name
   * @param  properties    (optional) properties to set initially
   * @return               newly created object's id
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_createObject($obj_type, $properties = null) {
    return $this->call_method
      ('facebook.data.createObject',
       array('obj_type' => $obj_type,
             'properties' => json_encode($properties)));
  }

  /**
   * Update an existing object.
   *
   * @param  obj_id        object's id
   * @param  properties    new properties
   * @param  replace       true for replacing existing properties; false for merging
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_updateObject($obj_id, $properties, $replace = false) {
    return $this->call_method
      ('facebook.data.updateObject',
       array('obj_id' => $obj_id,
             'properties' => json_encode($properties),
             'replace' => $replace));
  }

  /**
   * Delete an existing object.
   *
   * @param  obj_id        object's id
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_deleteObject($obj_id) {
    return $this->call_method
      ('facebook.data.deleteObject',
       array('obj_id' => $obj_id));
  }

  /**
   * Delete a list of objects.
   *
   * @param  obj_ids       objects to delete
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_deleteObjects($obj_ids) {
    return $this->call_method
      ('facebook.data.deleteObjects',
       array('obj_ids' => json_encode($obj_ids)));
  }

  /**
   * Get a single property value of an object.
   *
   * @param  obj_id        object's id
   * @param  prop_name     individual property's name
   * @return               individual property's value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getObjectProperty($obj_id, $prop_name) {
    return $this->call_method
      ('facebook.data.getObjectProperty',
       array('obj_id' => $obj_id,
             'prop_name' => $prop_name));
  }

  /**
   * Get properties of an object.
   *
   * @param  obj_id      object's id
   * @param  prop_names  (optional) properties to return; null for all.
   * @return             specified properties of an object
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getObject($obj_id, $prop_names = null) {
    return $this->call_method
      ('facebook.data.getObject',
       array('obj_id' => $obj_id,
             'prop_names' => json_encode($prop_names)));
  }

  /**
   * Get properties of a list of objects.
   *
   * @param  obj_ids     object ids
   * @param  prop_names  (optional) properties to return; null for all.
   * @return             specified properties of an object
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getObjects($obj_ids, $prop_names = null) {
    return $this->call_method
      ('facebook.data.getObjects',
       array('obj_ids' => json_encode($obj_ids),
             'prop_names' => json_encode($prop_names)));
  }

  /**
   * Set a single property value of an object.
   *
   * @param  obj_id        object's id
   * @param  prop_name     individual property's name
   * @param  prop_value    new value to set
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_setObjectProperty($obj_id, $prop_name, $prop_value) {
    return $this->call_method
      ('facebook.data.setObjectProperty',
       array('obj_id' => $obj_id,
             'prop_name' => $prop_name,
             'prop_value' => $prop_value));
  }

  /**
   * Read hash value by key.
   *
   * @param  obj_type      object type's name
   * @param  key           hash key
   * @param  prop_name     (optional) individual property's name
   * @return               hash value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getHashValue($obj_type, $key, $prop_name = null) {
    return $this->call_method
      ('facebook.data.getHashValue',
       array('obj_type' => $obj_type,
             'key' => $key,
             'prop_name' => $prop_name));
  }

  /**
   * Write hash value by key.
   *
   * @param  obj_type      object type's name
   * @param  key           hash key
   * @param  value         hash value
   * @param  prop_name     (optional) individual property's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_setHashValue($obj_type, $key, $value, $prop_name = null) {
    return $this->call_method
      ('facebook.data.setHashValue',
       array('obj_type' => $obj_type,
             'key' => $key,
             'value' => $value,
             'prop_name' => $prop_name));
  }

  /**
   * Increase a hash value by specified increment atomically.
   *
   * @param  obj_type      object type's name
   * @param  key           hash key
   * @param  prop_name     individual property's name
   * @param  increment     (optional) default is 1
   * @return               incremented hash value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_incHashValue($obj_type, $key, $prop_name, $increment = 1) {
    return $this->call_method
      ('facebook.data.incHashValue',
       array('obj_type' => $obj_type,
             'key' => $key,
             'prop_name' => $prop_name,
             'increment' => $increment));
  }

  /**
   * Remove a hash key and its values.
   *
   * @param  obj_type    object type's name
   * @param  key         hash key
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_removeHashKey($obj_type, $key) {
    return $this->call_method
      ('facebook.data.removeHashKey',
       array('obj_type' => $obj_type,
             'key' => $key));
  }

  /**
   * Remove hash keys and their values.
   *
   * @param  obj_type    object type's name
   * @param  keys        hash keys
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_removeHashKeys($obj_type, $keys) {
    return $this->call_method
      ('facebook.data.removeHashKeys',
       array('obj_type' => $obj_type,
             'keys' => json_encode($keys)));
  }


  /**
   * Define an object association.
   *
   * @param  name        name of this association
   * @param  assoc_type  1: one-way 2: two-way symmetric 3: two-way asymmetric
   * @param  assoc_info1 needed info about first object type
   * @param  assoc_info2 needed info about second object type
   * @param  inverse     (optional) name of reverse association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_defineAssociation($name, $assoc_type, $assoc_info1,
                                  $assoc_info2, $inverse = null) {
    return $this->call_method
      ('facebook.data.defineAssociation',
       array('name' => $name,
             'assoc_type' => $assoc_type,
             'assoc_info1' => json_encode($assoc_info1),
             'assoc_info2' => json_encode($assoc_info2),
             'inverse' => $inverse));
  }

  /**
   * Undefine an object association.
   *
   * @param  name        name of this association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_undefineAssociation($name) {
    return $this->call_method
      ('facebook.data.undefineAssociation',
       array('name' => $name));
  }

  /**
   * Rename an object association or aliases.
   *
   * @param  name        name of this association
   * @param  new_name    (optional) new name of this association
   * @param  new_alias1  (optional) new alias for object type 1
   * @param  new_alias2  (optional) new alias for object type 2
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_renameAssociation($name, $new_name, $new_alias1 = null,
                                  $new_alias2 = null) {
    return $this->call_method
      ('facebook.data.renameAssociation',
       array('name' => $name,
             'new_name' => $new_name,
             'new_alias1' => $new_alias1,
             'new_alias2' => $new_alias2));
  }

  /**
   * Get definition of an object association.
   *
   * @param  name        name of this association
   * @return             specified association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getAssociationDefinition($name) {
    return $this->call_method
      ('facebook.data.getAssociationDefinition',
       array('name' => $name));
  }

  /**
   * Get definition of all associations.
   *
   * @return             all defined associations
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getAssociationDefinitions() {
    return $this->call_method
      ('facebook.data.getAssociationDefinitions',
       array());
  }

  /**
   * Create or modify an association between two objects.
   *
   * @param  name        name of association
   * @param  obj_id1     id of first object
   * @param  obj_id2     id of second object
   * @param  data        (optional) extra string data to store
   * @param  assoc_time  (optional) extra time data; default to creation time
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_setAssociation($name, $obj_id1, $obj_id2, $data = null,
                               $assoc_time = null) {
    return $this->call_method
      ('facebook.data.setAssociation',
       array('name' => $name,
             'obj_id1' => $obj_id1,
             'obj_id2' => $obj_id2,
             'data' => $data,
             'assoc_time' => $assoc_time));
  }

  /**
   * Create or modify associations between objects.
   *
   * @param  assocs      associations to set
   * @param  name        (optional) name of association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_setAssociations($assocs, $name = null) {
    return $this->call_method
      ('facebook.data.setAssociations',
       array('assocs' => json_encode($assocs),
             'name' => $name));
  }

  /**
   * Remove an association between two objects.
   *
   * @param  name        name of association
   * @param  obj_id1     id of first object
   * @param  obj_id2     id of second object
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_removeAssociation($name, $obj_id1, $obj_id2) {
    return $this->call_method
      ('facebook.data.removeAssociation',
       array('name' => $name,
             'obj_id1' => $obj_id1,
             'obj_id2' => $obj_id2));
  }

  /**
   * Remove associations between objects by specifying pairs of object ids.
   *
   * @param  assocs      associations to remove
   * @param  name        (optional) name of association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_removeAssociations($assocs, $name = null) {
    return $this->call_method
      ('facebook.data.removeAssociations',
       array('assocs' => json_encode($assocs),
             'name' => $name));
  }

  /**
   * Remove associations between objects by specifying one object id.
   *
   * @param  name        name of association
   * @param  obj_id      who's association to remove
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_removeAssociatedObjects($name, $obj_id) {
    return $this->call_method
      ('facebook.data.removeAssociatedObjects',
       array('name' => $name,
             'obj_id' => $obj_id));
  }

  /**
   * Retrieve a list of associated objects.
   *
   * @param  name        name of association
   * @param  obj_id      who's association to retrieve
   * @param  no_data     only return object ids
   * @return             associated objects
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getAssociatedObjects($name, $obj_id, $no_data = true) {
    return $this->call_method
      ('facebook.data.getAssociatedObjects',
       array('name' => $name,
             'obj_id' => $obj_id,
             'no_data' => $no_data));
  }

  /**
   * Count associated objects.
   *
   * @param  name        name of association
   * @param  obj_id      who's association to retrieve
   * @return             associated object's count
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getAssociatedObjectCount($name, $obj_id) {
    return $this->call_method
      ('facebook.data.getAssociatedObjectCount',
       array('name' => $name,
             'obj_id' => $obj_id));
  }

  /**
   * Get a list of associated object counts.
   *
   * @param  name        name of association
   * @param  obj_ids     whose association to retrieve
   * @return             associated object counts
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getAssociatedObjectCounts($name, $obj_ids) {
    return $this->call_method
      ('facebook.data.getAssociatedObjectCounts',
       array('name' => $name,
             'obj_ids' => json_encode($obj_ids)));
  }

  /**
   * Find all associations between two objects.
   *
   * @param  obj_id1     id of first object
   * @param  obj_id2     id of second object
   * @param  no_data     only return association names without data
   * @return             all associations between objects
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  function data_getAssociations($obj_id1, $obj_id2, $no_data = true) {
    return $this->call_method
      ('facebook.data.getAssociations',
       array('obj_id1' => $obj_id1,
             'obj_id2' => $obj_id2,
             'no_data' => $no_data));
  }

  /* UTILITY FUNCTIONS */

  function call_method($method, $params) {
    $this->error_code = 0;

    if ($this->call_as_apikey) {
      $params['call_as_apikey'] = $this->call_as_apikey;
    }

    $xml = $this->post_request($method, $params);

    $impl = new IsterXmlSimpleXMLImpl();
    $sxml = $impl->load_string($xml);
    $result = array();
    $children = $sxml->children();
    $result = $this->convert_simplexml_to_array($children[0]);

    if (!empty($GLOBALS['facebook_config']['debug'])) {
      // output the raw xml and its corresponding php object, for debugging:
      print '<div style="margin: 10px 30px; padding: 5px; border: 2px solid black; background: gray; color: white; font-size: 12px; font-weight: bold;">';
      $this->cur_id++;
      print $this->cur_id . ': Called ' . $method . ', show ' .
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'params\');">Params</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'xml\');">XML</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'php\');">PHP</a>';
      print '<pre id="params'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($params, true).'</pre>';
      print '<pre id="xml'.$this->cur_id.'" style="display: none; overflow: auto;">'.htmlspecialchars($xml).'</pre>';
      print '<pre id="php'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($result, true).'</pre>';
      print '</div>';
    }
    if (is_array($result) && isset($result['error_code'])) {
      $this->error_code = $result['error_code'];
      return null;
    }
    return $result;
  }

  function post_request($method, $params) {
    $params['method'] = $method;
    $params['session_key'] = $this->session_key;
    $params['api_key'] = $this->api_key;
    $params['call_id'] = fb_microtime_float(true);
    if ($params['call_id'] <= $this->last_call_id) {
      $params['call_id'] = $this->last_call_id + 0.001;
    }
    $this->last_call_id = $params['call_id'];
    if (!isset($params['v'])) {
      $params['v'] = '1.0';
    }
    $post_params = array();
    foreach ($params as $key => $val) {
      if (is_array($val)) $params[$key] = implode(',', $val);
      $post_params[] = $key.'='.urlencode($params[$key]);
    }
    $secret = $this->secret;
    $post_params[] = 'sig='.$this->facebook->generate_sig($params, $secret);
    $post_string = implode('&', $post_params);

    if (function_exists('curl_init')) {
      // Use CURL if installed...
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->server_addr);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook API PHP4 Client 1.1 (curl) ' . phpversion());
      $result = curl_exec($ch);
      curl_close($ch);
    } else {
      // Non-CURL based version...
      $context =
        array('http' =>
              array('method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
                                'User-Agent: Facebook API PHP4 Client 1.1 (non-curl) '.phpversion()."\r\n".
                                'Content-length: ' . strlen($post_string),
                    'content' => $post_string));
      $contextid=stream_context_create($context);
      $sock=fopen($this->server_addr, 'r', false, $contextid);
      if ($sock) {
        $result='';
        while (!feof($sock))
          $result.=fgets($sock, 4096);

        fclose($sock);
      }
    }
    return $result;
  }

  function convert_simplexml_to_array($sxml) {
    if ($sxml) {
      $arr = array();
      $attrs = $sxml->attributes();
      foreach ($sxml->children() as $child) {
        if (!empty($attrs['list'])) {
          $arr[] = $this->convert_simplexml_to_array($child);
        } else {
          $arr[$child->___n] = $this->convert_simplexml_to_array($child);
        }
      }
      if (sizeof($arr) > 0) {
        return $arr;
      } else {
        return (string)$sxml->CDATA();
      }
    } else {
      return '';
    }
  }
}

// Supporting methods and values------

/**
 * Error codes and descriptions for the Facebook API.
 */

class FacebookAPIErrorCodes {

  var $API_EC_SUCCESS = 0;

  /*
   * GENERAL ERRORS
   */
  var $API_EC_UNKNOWN = 1;
  var $API_EC_SERVICE = 2;
  var $API_EC_METHOD = 3;
  var $API_EC_TOO_MANY_CALLS = 4;
  var $API_EC_BAD_IP = 5;

  /*
   * PARAMETER ERRORS
   */
  var $API_EC_PARAM = 100;
  var $API_EC_PARAM_API_KEY = 101;
  var $API_EC_PARAM_SESSION_KEY = 102;
  var $API_EC_PARAM_CALL_ID = 103;
  var $API_EC_PARAM_SIGNATURE = 104;
  var $API_EC_PARAM_USER_ID = 110;
  var $API_EC_PARAM_USER_FIELD = 111;
  var $API_EC_PARAM_SOCIAL_FIELD = 112;
  var $API_EC_PARAM_ALBUM_ID = 120;

  /*
   * USER PERMISSIONS ERRORS
   */
  var $API_EC_PERMISSION = 200;
  var $API_EC_PERMISSION_USER = 210;
  var $API_EC_PERMISSION_ALBUM = 220;
  var $API_EC_PERMISSION_PHOTO = 221;

  var $FQL_EC_PARSER = 601;
  var $FQL_EC_UNKNOWN_FIELD = 602;
  var $FQL_EC_UNKNOWN_TABLE = 603;
  var $FQL_EC_NOT_INDEXABLE = 604;
  var $FQL_EC_UNKNOWN_FUNCTION = 605;
  var $FQL_EC_INVALID_PARAM = 606;

  /**
   * DATA STORE API ERRORS
   */
  var $API_EC_DATA_UNKNOWN_ERROR = 800;
  var $API_EC_DATA_INVALID_OPERATION = 801;
  var $API_EC_DATA_QUOTA_EXCEEDED = 802;
  var $API_EC_DATA_OBJECT_NOT_FOUND = 803;
  var $API_EC_DATA_OBJECT_ALREADY_EXISTS = 804;
  var $API_EC_DATA_DATABASE_ERROR = 805;

  var $api_error_descriptions = array();

  function FacebookApiErrorCodes() {
    $this->api_error_descriptions = array(
      $this->API_EC_SUCCESS            => 'Success',
      $this->API_EC_UNKNOWN            => 'An unknown error occurred',
      $this->API_EC_SERVICE            => 'Service temporarily unavailable',
      $this->API_EC_METHOD             => 'Unknown method',
      $this->API_EC_TOO_MANY_CALLS     => 'Application request limit reached',
      $this->API_EC_BAD_IP             => 'Unauthorized source IP address',
      $this->API_EC_PARAM              => 'Invalid parameter',
      $this->API_EC_PARAM_API_KEY      => 'Invalid API key',
      $this->API_EC_PARAM_SESSION_KEY  => 'Session key invalid or no longer valid',
      $this->API_EC_PARAM_CALL_ID      => 'Call_id must be greater than previous',
      $this->API_EC_PARAM_SIGNATURE    => 'Incorrect signature',
      $this->API_EC_PARAM_USER_ID      => 'Invalid user id',
      $this->API_EC_PARAM_USER_FIELD   => 'Invalid user info field',
      $this->API_EC_PARAM_SOCIAL_FIELD => 'Invalid user field',
      $this->API_EC_PARAM_ALBUM_ID     => 'Invalid album id',
      $this->API_EC_PERMISSION         => 'Permissions error',
      $this->API_EC_PERMISSION_USER    => 'User not visible',
      $this->API_EC_PERMISSION_ALBUM   => 'Album not visible',
      $this->API_EC_PERMISSION_PHOTO   => 'Photo not visible',
      $this->FQL_EC_PARSER             => 'FQL: Parser Error',
      $this->FQL_EC_UNKNOWN_FIELD      => 'FQL: Unknown Field',
      $this->FQL_EC_UNKNOWN_TABLE      => 'FQL: Unknown Table',
      $this->FQL_EC_NOT_INDEXABLE      => 'FQL: Statement not indexable',
      $this->FQL_EC_UNKNOWN_FUNCTION   => 'FQL: Attempted to call unknown function',
      $this->FQL_EC_INVALID_PARAM      => 'FQL: Invalid parameter passed in',
      $this->API_EC_DATA_UNKNOWN_ERROR => 'Unknown data store API error',
      $this->API_EC_DATA_INVALID_OPERATION => 'Invalid operation',
      $this->API_EC_DATA_QUOTA_EXCEEDED => 'Data store allowable quota was exceeded',
      $this->API_EC_DATA_OBJECT_NOT_FOUND => 'Specified object cannot be found',
      $this->API_EC_DATA_OBJECT_ALREADY_EXISTS => 'Specified object already exists',
      $this->API_EC_DATA_DATABASE_ERROR => 'A database error occurred. Please try again',
    );
  }
}

$profile_field_array = array(
    "about_me",
    "activities",
    "affiliations",
    "birthday",
    "books",
    "current_location",
    "education_history",
    "first_name",
    "hometown_location",
    "hs_info",
    "interests",
    "is_app_user",
    "last_name",
    "meeting_for",
    "meeting_sex",
    "movies",
    "music",
    "name",
    "notes_count",
    "pic",
    "pic_big",
    "pic_small",
    "political",
    "profile_update_time",
    "quotes",
    "relationship_status",
    "religion",
    "sex",
    "significant_other_id",
    "status",
    "timezone",
    "tv",
    "wall_count",
    "work_history");

function fb_microtime_float() {
  list($usec, $sec) = explode(' ', microtime());
  return ((float)$usec + (float)$sec);
}
