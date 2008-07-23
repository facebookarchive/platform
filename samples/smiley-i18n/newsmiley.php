<?php

  /*
   * newsmiley.php - Create a new feed story
   *
   */

include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

echo render_header('New');

$moods = get_moods();

echo '<h2><fb:intl>What\'s your mood today?</fb:intl></h2>';

$fb = get_fb();
$set_count = $fb->api_client->data_getUserPreference(2);
if ($set_count > 0) {
  if ($set_count == 1) {
    echo "<h3><fb:intl>You've set your mood 1 time in the past.</fb:intl></h3>";
  } else {
    echo "<h3><fb:intl>You've set your mood {number} times in the past."
         . '<fb:intl-token name="number">' . $set_count . '</fb:intl-token>'
       . '</fb:intl></h3>';
  }
}

$feed_handler = ROOT_LOCATION . '/handlers/feedHandler.php';
echo '<form fbtype="feedStory" action="' . $feed_handler . '">';

echo render_emoticon_grid(get_moods());
echo '<input type="hidden" id="picked" name="picked" value="-1">'
         . '<div id="centerbutton" class="buttons">'
           . '<fb:tag name="input">'
             . '<fb:tag-attribute name="type">submit</fb:tag-attribute>'
             . '<fb:tag-attribute name="id">mood</fb:tag-attribute>'
             . '<fb:tag-attribute name="label">'
               . '<fb:intl desc="Button label: set your current mood">'
                 . 'My Mood'
               . '</fb:intl>'
             . '</fb:tag-attribute>'
           . '</fb:tag>'
         . '</div>'
         . '<div id="emoticon"></div>'
  .'</form></div>';

echo render_footer();
