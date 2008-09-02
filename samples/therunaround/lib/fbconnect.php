<?php

/*
 * Facebook-specific functions.
 *
 */

include_once 'facebook-client/facebook.php';

/*
 * Renders the JS necessary for any Facebook interaction to work.
 */
function render_fbconnect_init_js() {
  $html = sprintf(
    '<script src="%s/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>
     <script  type="text/javascript">window.api_key="'.get_api_key().'";</script>
     <script src="fbconnect.js" type="text/javascript"></script>',
    get_static_root());

  //  $already_logged_in = facebook_client()->get_loggedin_user() ? "true" : "false";
  onloadRegister(sprintf("facebook_onload(%s);",
                         (bool) facebook_client()->get_loggedin_user()));

  return $html;
}

/*
 * Render a custom button to log in via Facebook.
 * When the button is clicked, the facebook_button_onclick JS function is fired. That triggers the Facebook JS library to
 * authenticate the user, and sets up a handler for when the authentication is complete.
 *
 * @param $link_to_current_user The button can be used to log in, or to associate an existing account. If an existing account, pass true.
 *
 */
function render_fbconnect_button($link_to_current_user='', $size='medium') {
  return '<fb:login-button size="'.$size.'" onclick="facebook_button_onclick('.$link_to_current_user.');"></fb:login-button>';
}

/*
 * Display the feed form when the page is loaded. This should be called only if the user has already
 * been warned that the feed form is coming (via a checkbox, button, etc), or else it can be a pretty
 * jarring experience.
 *
 * @param $run    the run to publish in feed
 */
function register_feed_form_js($run) {
  $template_data = array('running-picture' => '<img src="http://www.midwinter.com/~jrosenstein/runaround_feed.jpg" />',
                         'location' => $run->route,
                         'distance' => $run->miles . ' mile');

  onloadRegister(sprintf("facebook_publish_feed_story(%d, %s); ",
                         get_feed_bundle_id(),
                         json_encode($template_data)));
}

/*
 * Get the facebook client object for easy access.
 */
function facebook_client() {
  static $facebook = null;
  if ($facebook === null) {
    $facebook = new Facebook(get_api_key(), get_api_secret(), false, get_base_fb_url());

    if (!$facebook) {
      error_log('Could not create facebook client.');
    }

  }
  return $facebook;
}

/**
 * Wrapper method for registering users.  Makes sure we never send a session key.
 */
function facebook_registerUsers($accounts) {
  $facebook = facebook_client();
  $session_key = $facebook->api_client->session_key;
  $facebook->api_client->session_key = null;

  $result = false;
  try {
    $ret = $facebook->api_client->call_method(
             'facebook.connect.registerUsers',
             array('accounts' => json_encode($accounts)));

    // On success, registerUsers returns the set of email hashes registered
    $result = (count($ret) == count($accounts));
  } catch (Exception $e) {
    error_log("Exception thrown while calling facebook.connect.registerUsers: ".$e->getMessage());
  }

  $facebook->api_client->session_key = $session_key;
  return $result;
}

/**
 * Wrapper method for unregistering users.  Makes sure we never send a session key.
 */
function facebook_unregisterUsers($email_hashes) {
  $facebook = facebook_client();
  $session_key = $facebook->api_client->session_key;
  $facebook->api_client->session_key = null;

  // Unregister the account from fb
  $result = false;
  try {
    $ret = $facebook->api_client->call_method(
             'facebook.connect.unregisterUsers',
             array('email_hashes' => json_encode($email_hashes)));
    $result = (count($email_hashes) == count($ret));
  } catch (Exception $e) {
    error_log("Exception thrown while calling facebook.connect.unregisterUsers: ".$e->getMessage());
  }

  $facebook->api_client->session_key = $session_key;
  return $result;
}

/*
 * Fetch fields about a user from Facebook.
 *
 * If performance is an issue, then you may want to implement caching on top of this
 * function. The cache would have to be cleared every 24 hours.
 */
function facebook_get_fields($fb_uid, $fields) {
  try {
    $infos = facebook_client()->api_client->users_getInfo($fb_uid,
                                                          $fields);

    if (empty($infos)) {
      return null;
    }
    return reset($infos);

  } catch (Exception $e) {
    error_log("Failure in the api: ". $e->getMessage());
    return null;
  }
}

/**
 * Returns the "public" hash of the email address, i.e., the one we give out
 * to select partners via our API.
 *
 * @param  string $email An email address to hash
 * @return string        A public hash of the form crc32($email)_md5($email)
 */
function email_get_public_hash($email) {
  if ($email != null) {
    $email = trim(strtolower($email));
    return crc32($email) . '_' . md5($email);
  } else {
    return '';
  }
}

/*
 * Check if the current server matches with the URL configured. If not, then
 * redirect to one that does - but first put up a page with a warning about it.
 *
 * This is mostly for the sake of the demo app, to make sure you've got it setup.
 * Probably not necessary in production as long as you have your own way of making
 * sure you're on the right domain.
 */
function ensure_loaded_on_correct_url() {
  $current_url = get_current_url();
  $callback_url = get_callback_url();

  if (!$callback_url) {
    $error = 'You need to specify $callback_url in lib/config.php';
  }
  if (!$current_url) {
    error_log("therunaround: Unable to figure out what server the "
             ."user is currently on, skipping check ...");
    return;
  }

  if (get_domain($callback_url) != get_domain($current_url)) {
    // do a redirect
    $url = 'http://' . get_domain($callback_url) . $_SERVER['REQUEST_URI'];
    $error = 'You need to access your website on the same url as your callback. '
      .'Accessed at ' . get_domain($current_url) . ' instead of ' . get_domain($callback_url)
      .'. Redirecting to <a href="'.$url.'">'.$url.'</a>...';
    $redirect = '<META HTTP-EQUIV=Refresh CONTENT="10; URL='.$url.'">';
  }

  if (isset($error)) {
    echo '<head>'
      .'<title>The Run Around</title>'
      .'<link type="text/css" rel="stylesheet" href="style.css" />'
      . isset($redirect) ? $redirect : ''
      . '</head>';

    echo render_error($error);
    exit;
  }
}
