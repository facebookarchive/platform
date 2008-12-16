<?php

// via
// http://planetozh.com/blog/2008/07/what-plugin-coders-must-know-about-wordpress-26/
$root = dirname(dirname(dirname(dirname(__FILE__))));
if (file_exists($root.'/wp-load.php')) {
  // WP 2.6
  require_once($root.'/wp-load.php');
} else {
  // Before 2.6
  require_once($root.'/wp-config.php');
}

require_once($root . '/wp-includes/registration.php');
require_once('facebook-client/facebook.php');


function _fbc_make_client() {
  return new Facebook(get_option(FBC_APP_KEY_OPTION),
                      get_option(FBC_APP_SECRET_OPTION),
                      false,
                      'connect.facebook.com');
}

/*
 * Get the facebook client object for easy access.
 */
function fbc_facebook_client() {
  static $facebook = null;
  if ($facebook === null) {
    $facebook = _fbc_make_client();
  }
  return $facebook;
}


function fbc_api_client() {
  return fbc_facebook_client()->api_client;
}


/**
  provides an api client without a user session.
*/
function fbc_anon_api_client() {
  $client = _fbc_make_client();
  $client->user = 0;
  $client->api_client->session_key = null;
  return $client->api_client;
}

function fbc_get_displayname($userinfo) {
  if (empty($userinfo['name'])) {
    // i18n-able
    return _(FBC_ANONYMOUS_DISPLAYNAME);
  } else {
    return $userinfo['name'];
  }
}

function fbc_make_public_url($userinfo) {
  if (empty($userinfo['name'])) {
    // This user is hidden from search, so they dont get a url either
    return null;
  }

  $fbuid = $userinfo['uid'];
  $name = $userinfo['name'];
  $under_name = str_replace(" ", "-", $name);

  $clean_name = preg_replace('/[^A-Za-z0-9_\-]+/', '', $under_name);

  $url = 'http://www.facebook.com/people/' . $clean_name . '/' . $fbuid;

  return $url;
}


function render_fb_profile_pic($user) {
  return <<<EOF
    <div class="avatar avatar-32">
    <fb:profile-pic uid="$user" facebook-logo="true" size="square"></fb:profile-pic>
    </div>
EOF;
}


function render_fbconnect_button($onlogin=null) {
  if ($onlogin !== null) {
    $onlogin_str = ' onlogin="'. $onlogin .'" ';
  } else {
    $onlogin_str = '';
  }
  return <<<EOF
<div class="dark">
  <fb:login-button size="large" background="white" length="short" $onlogin_str>
  </fb:login-button>
</div>
EOF;

}

function get_wpuid_by_fbuid($fbuid) {
  global $wpdb;
  $sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'fbuid' AND meta_value = %s";
  $res = $wpdb->get_results($wpdb->prepare($sql, $fbuid), ARRAY_A);
  if ($res) {
    return $res['user_id'];
  } else {
    return 0;
  }
}

define('FBC_ERROR_NO_FB_SESSION', -2);
define('FBC_ERROR_USERNAME_EXISTS', -1);

function fbc_login_if_necessary() {
  $fbuid = fbc_facebook_client()->get_loggedin_user();
  if ($fbuid) {
    $wpuid = fbc_fbuser_to_wpuser($fbuid);
    if (!$wpuid) {
      // There is no wp user associated w/ this fbuid

      $user = wp_get_current_user();
      $wpuid = $user->ID;
      if ($wpuid) {
        // User already has a wordpress account, link to this facebook account
        update_usermeta($wpuid, 'fbuid', "$fbuid");
      } else {
        // Create a new wordpress account
        $wpuid = fbc_insert_user($fbuid);
        if ($wpuid === FBC_ERROR_USERNAME_EXISTS) {
          return FBC_ERROR_USERNAME_EXISTS;
        }
      }

    } else {
      // Already have a linked wordpress account, fall through and set
      // login cookie
    }

    wp_set_auth_cookie($wpuid, true, false);

    return $fbuid;
  } else {
    return FBC_ERROR_NO_FB_SESSION;
  }
}

function get_user_by_meta($meta_key, $meta_value) {
  global $wpdb;
  $sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = '%s'";
  return $wpdb->get_var($wpdb->prepare($sql, $meta_key, $meta_value));
}

function fbc_fbuser_to_wpuser($fbuid) {
  return get_user_by_meta('fbuid', $fbuid);
}


function fbc_insert_user($fbuid) {

  $userinfo = fbc_anon_api_client()->users_getInfo(array($fbuid),
                                              array('name',
                                              'proxied_email',
                                              'profile_url'));

  $userinfo = $userinfo[0];

  $fbusername = 'fb' . $fbuid;
  if (username_exists($fbusername)) {
    return FBC_ERROR_USERNAME_EXISTS;
  }

  $userdata = array(
    'user_pass' => wp_generate_password(),
    'user_login' => $fbusername,
    'display_name' => fbc_get_displayname($userinfo),
    'user_url' => fbc_make_public_url($userinfo),
    'user_email' => $userinfo['proxied_email']
  );

  $wpuid = wp_insert_user($userdata);
  if($wpuid) {
    update_usermeta($wpuid, 'fbuid', "$fbuid");
  }

  return $wpuid;
}


?>
