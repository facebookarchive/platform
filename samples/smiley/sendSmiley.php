<?php

include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$fb = get_fb();
echo render_header('Send');
$ret = '<h2>Send a friend a smiley</h2>';
$ret .='<form fbtype="multiFeedStory" action="'.ROOT_LOCATION.'/handlers/multiFeedHandler.php">';
$ret .= '<div style="margin-left: 131px;margin-top:20px"> <fb:multi-friend-input /></div>';
$ret .= render_emoticon_grid(get_other_moods());
$ret .= '<input type="hidden" id="picked" name="picked" value="-1">'
         .'<div id="centerbutton" class="buttons"><input type="submit" id="mood" label="Send Smiley"></div>'
         .'<div id="emoticon"></div>'
  .'</form></div>';

echo $ret;
