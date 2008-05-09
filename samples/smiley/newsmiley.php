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

echo '<h2>' . 'What\'s your mood today?' . '</h2>';

$feed_handler = ROOT_LOCATION . '/handlers/feedHandler.php';
echo '<form fbtype="feedStory" action="' . $feed_handler . '">';

echo render_emoticon_grid(get_moods());
echo '<input type="hidden" id="picked" name="picked" value="-1">'
         . '<div id="centerbutton" class="buttons"><input type="submit" id="mood" label="My Mood"></div>'
         . '<div id="emoticon"></div>'
  .'</form></div>';

echo render_footer();
