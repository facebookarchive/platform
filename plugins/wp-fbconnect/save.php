<?php

require_once('common.php');

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
    }

  } else {
    // Already have a linked wordpress account, fall through and set
    // login cookie
  }

  wp_set_auth_cookie($wpuid, true, false);
  echo "got exising user: ". $wpuid;
} else {
  header("HTTP/1.1 500 No Facebook User Session");
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

  $userinfo = fbc_api_client()->users_getInfo(array($fbuid),
                                              array('name', 'proxied_email'));
  $userinfo = $userinfo[0];

  $fbusername = 'fb' . $fbuid;
  if (username_exists($fbusername)) {
    header('HTTP/1.1 500 Username exists');
    exit;
  }

  $userdata = array(
    'user_pass' => wp_generate_password(),
    'user_login' => $fbusername,
    'display_name' => $userinfo['name'],
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
