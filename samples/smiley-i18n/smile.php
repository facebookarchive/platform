<?php
include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$smile = $_GET['smile'];
$moods = get_moods();

echo render_inline_style();
echo '<div class="big_box"><div class="big_smiley">'
     .    $moods[$smile][1]
     .  '</div><div>'
     .    '<fb:intl desc="Mood name for \'' . $moods[$smile][1] . '\'">'
     .       $moods[$smile][0]
     .    '</fb:intl>'
     .  '</div></div>';

