<?php
/*
 * index.php - (and For setting app boxes)
 *
 */
include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';


$fb = get_fb();
$user = $fb->require_login();

// You need to set info or profile box in order for the button's below to show up.
// Don't set them every time.
$is_set = $fb->api_client->data_getUserPreference(1);

if ($is_set != 'set1') {
  // Setting info section for example
  // (Don't do this! Wait for user to add content)
  $info_fields = get_sample_info();
  $fb->api_client->profile_setInfo('My Smilies', 5, $info_fields, $user);

  // Setting info main profile box for example
  // (Don't do this! Wait for user to add content)
  $main_box =  get_user_profile_box(array('Happy', ':)'));
  $fb->api_client->profile_setFBML(null, $user, null, null, null, $main_box);

 // Don't do this again
  $fb->api_client->data_setUserPreference(1, 'set');
}

echo render_header();

echo '<h2>Welcome to Smiley!</h2>';

// Profile box
echo '<fb:add-section-button section="profile"/>';

// Info section
echo '<fb:add-section-button section="info" />';
