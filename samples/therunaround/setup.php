<?php

  /*
   * register_feed_forms.php
   *
   * Access this PHP file once after you have set up your configuration.
   * Copy/paste the resulting bundle ID and put it into config.php.
   */

include_once 'lib/fbconnect.php';
include_once 'lib/core.php';

echo render_header();

// First verify configuration is in place

if (!is_fbconnect_enabled()) {
  $error = 'Facebook Connect is not enabled. Have you copied config.php.sample to config.php?';
 }

if (!$error) {
  $error = verify_facebook_callback();
}

if ($error) {

  $error = 'Could not configure your facebook configuration stuff properly.';

  echo render_error($error);



}

try {
  $bundle_id = register_feed_forms();

  echo '<div class="bluebox">'
    .'<p>Congratulations! You have registered a feed form.</p>'
    .'<p>Put the following line in lib/config.php</p>'
    .'<pre>'
    .'  $feed_bundle_id = <b>'.$bundle_id.'</b>;'
    .'</pre>'
    .'</div>';
} catch (Exception $e) {
  echo '<div class="error">'
    .'Error while registering bundle: <br /><pre>'
    .print_r($e,true).'</pre>';
}

echo render_footer();

/*
 * Make the API call to register the feed forms. This is a setup call that only
 * needs to be made once.
 *
 */
function register_feed_forms() {
  $one_line_stories = $short_stories = $full_stories = array();

  $one_line_stories[] = '{*actor*} went for a {*distance*} run at {*location*}.';

  $form_id = facebook_client()->api_client->feed_registerTemplateBundle($one_line_stories);
  return $form_id;
}
