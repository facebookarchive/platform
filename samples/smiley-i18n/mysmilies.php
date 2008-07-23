<?php

  /*
   * mysmilies.php - Show the user's moods
   *
   */

include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$fb = get_fb();
$user = $fb->require_login();

// The list of possible moods
$moods = get_moods();

// Your past moods are stored in a preference for simplicity
$mood_list = $fb->api_client->data_getUserPreference(0);

// This can be viewed as an app page or a tabs
if (!isset($_POST['is_tab'])) {
  echo render_header('Mine');
 } else {
  echo render_inline_style();
 }


echo '<div style="text-align: center">';
echo '<h2>'
   .   '<fb:intl desc="Page header for list of someone\'s smilies">'
   .     '<fb:name firstnameonly="true" useyou="false" possessive="true" '
   .              'linked="false" uid="'.$user.'"/>'
   .     ' Smilies'
   .   '</fb:intl>'
   . '</h2>';

$user_name = '<fb:name useyou="false" uid="'.$fb->user.'"/>';
echo '<h3 style="padding: 7px 0px">'
   .   '<fb:intl>'
   .     'We are pleased to announce that ' . $user_name . ' has been feeling:'
   .   '</fb:intl>'
   . '</h3>';
echo '<div style="overflow:hidden"><div class="past">';

$n = max(3,count($mood_list));
for ($i =0; $i< $n; $i++) {
  $v = intval($mood_list[$i]);
  $mood = $moods[$v];
  echo '<a class="box" href="smile.php?smile='.$v.'"><div class="smiley">'
     .    $mood[1]
     .  '</div><div>'
     .    '<fb:intl desc="Mood name for \'' . $mood[1] . '\'">'
     .       $mood[0]
     .    '</fb:intl>'
     .  '</div></a>';
}
echo '</div></div>';
if (isset($_POST['is_tab'])) {
  echo '<br/>'
     . '<a href="http://apps.facebook.com/'.APP_SUFFIX.'" >'
     . '<fb:intl desc="Link to the Smiley application">Check out Smiley</fb:intl>'
     . '</a>';
}
echo '</div>';


