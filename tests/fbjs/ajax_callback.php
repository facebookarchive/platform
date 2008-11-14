<?php

$user = isset($_POST['fb_sig_user']) ? $_POST['fb_sig_user'] : null;
if ($_GET['t'] == 0) { // Ajax.RAW
  //print_r($_POST);
  echo 'This is a raw string.';
} else if ($_GET['t'] == 1) { // Ajax.JSON
  echo '{message: ['.implode(',', range(1,1000)).'], test: ["1"]}';
} else if ($_GET['t'] == 2) { // Ajax.FBML
  echo 'Hello. There is an event called <fb:eventlink eid="84840295206"/>.';
}
