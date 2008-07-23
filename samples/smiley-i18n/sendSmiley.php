<?php

include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$fb = get_fb();
echo render_header('Send');
$ret = '<h2><fb:intl>Send a friend a smiley</fb:intl></h2>';
$ret .='<form fbtype="multiFeedStory" action="'.ROOT_LOCATION.'/handlers/multiFeedHandler.php">';
$ret .= '<div class="input_row"> <fb:multi-friend-input /></div>';
$ret .= render_emoticon_grid(get_other_moods());
$ret .= '<input type="hidden" id="picked" name="picked" value="-1" />'
     .  '<div id="centerbutton" class="buttons">'
     .    '<fb:tag name="input">'
     .      '<fb:tag-attribute name="type">submit</fb:tag-attribute>'
     .      '<fb:tag-attribute name="id">mood</fb:tag-attribute>'
     .      '<fb:tag-attribute name="label">'
     .        '<fb:intl desc="Button label">Send Smiley</fb:intl>'
     .      '</fb:tag-attribute>'
     .    '</fb:tag>'
     .  '</div>'
     .  '<div id="emoticon"></div>'
     .'</form></div>';

echo $ret;
