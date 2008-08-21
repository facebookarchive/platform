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


/*
 * Get the facebook client object for easy access.
 */
function fbc_facebook_client() {
  static $facebook = null;
  if ($facebook === null) {
    $facebook = new Facebook(get_option(FBC_APP_KEY_OPTION),
                             get_option(FBC_APP_SECRET_OPTION),
                             false,
                             'connect.facebook.com');

    if (!$facebook) {
      error_log('Could not create facebook client.');
    }

  }
  return $facebook;
}

function fbc_api_client() {
  return fbc_facebook_client()->api_client;
}


function fbc_make_public_url($userinfo) {
  $fbuid = $userinfo['uid'];
  $name = $userinfo['name'];
  $under_name = str_replace(" ", "_", $name);

  $clean_name = preg_replace('/[^A-Za-z0-9_\-]+/', '', $under_name);

  $url = 'http://www.facebook.com/people/' . $clean_name . '/' . $fbuid;

  return $url;
}


function render_fb_profile_pic($user) {
  return <<<EOF
    <div class="avatar avatar-32">
    <fb:profile-pic uid="$user"  size="square"></fb:profile-pic>
    </div>
EOF;
}


function render_fbconnect_button($onsuccess="null", $size='small') {
  if (!in_array($size, array('small', 'medium', 'large'))) {
    error_log("invalid size: $size");
    return;
  }

  return <<<EOF
<a href="#" onclick="fbconnect.ensure_session($onsuccess); return false;" class="fbconnect">
<img src="http://static.ak.fbcdn.net/images/fbconnect/fbconnect_$size.gif" class="fbconnect_logo" />
</a>
<div id="facebook_button_loading" style="visibility:hidden;">
<img src="http://static.ak.fbcdn.net/images/upload_progress.gif" />
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



?>
