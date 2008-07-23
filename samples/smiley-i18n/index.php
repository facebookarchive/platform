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

if ($is_set != 'set') {
  // Setting info section for example
  // (Don't do this! Wait for user to add content)
  $info_fields = get_sample_info();
  $fb->api_client->profile_setInfo('<fb:intl desc="Profile box header">My Smilies</fb:intl>', 5, $info_fields, $user);

  // Setting info main profile box for example
  // (Don't do this! Wait for user to add content)
  $main_box =  get_user_profile_box(array('<fb:intl desc="Mood name for \':)\'">Happy</fb:intl>', ':)'), $user);
  $fb->api_client->profile_setFBML(null, $user, null, null, null, $main_box);

 // Don't do this again
  $fb->api_client->data_setUserPreference(1, 'set');
}

echo render_header();

echo '<h2><fb:intl>Welcome to Smiley!</fb:intl></h2>';
echo '<p><fb:intl>Smiley is a sample app created to demonstrate the many '
    .'platform integration points of the Facebook profile.</fb:intl></p>';

// Profile box
echo '<fb:intl>Here is a button for adding a box to your profile. This will go away if you add the box:</fb:intl>';

echo '<div class="section_button"><fb:add-section-button section="profile"/></div>';

// Info section
echo '<fb:intl>Here is a button for adding an info section to your profile. This will go away if you add the section:</fb:intl>';

echo '<div class="section_button"><fb:add-section-button section="info" /></div>';

// Permissions
echo '<fb:intl>These are FBML tags that can prompt users for extended permissions from the canvas page. These will go away if you grant these permissions:</fb:intl><br />';
echo '<fb:prompt-permission perms="email"><fb:intl desc="Link to enable E-mail notification">Enable Email</fb:intl></fb:prompt-permission>';
echo '<br />';
echo '<fb:prompt-permission perms="infinite_session"><fb:intl>Enable Permanent Login</fb:intl></fb:prompt-permission>';

echo '<p><fb:intl>Upon submitting the form below, you will be prompted to grant email permissions (unless you\'ve already done so for this app):</fb:intl>';
echo '<form promptpermission="email"><br /><fb:intl>How often would you like to be notified of new smilies?</fb:intl>';
echo '<br />';
echo '<input type="text" name="frequency">';
echo '<fb:tag name="input">'
     . '<fb:tag-attribute name="type">submit</fb:tag-attribute>'
     . '<fb:tag-attribute name="value">'
       . '<fb:intl desc="Button label: Set notification frequency">'
         . 'Notify Me'
       . '</fb:intl>'
     . '</fb:tag-attribute>'
   . '</fb:tag>';
echo '</form></p>';
