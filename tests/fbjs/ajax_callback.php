<?php

$user = isset($_POST['fb_sig_user']) ? $_POST['fb_sig_user'] : null;
if ($_GET['t'] == 0) { // Ajax.RAW
   print_r($_POST);
  echo 'This is a raw string. The current time is: '.date('r').', and you are '.($user ? 'uid: #'.$user : 'anonymous').'.';
} else if ($_GET['t'] == 1) { // Ajax.JSON
//  echo '{message: "This is a JSON object.", time: "'.date('r').'", test: [{fbml_test: "Hello, '.($user ? '<fb:name uid='.$user.' useyou=false />' : 'anonymous').'. <a href=\'#\' onclick=\'console.log(1)\'>Click</a>"}]}';
//  echo json_encode(array('message' => range(1,2000)));
  echo '{message: ['.implode(',', range(1,1000)).']}';
} else if ($_GET['t'] == 2) { // Ajax.FBML
  echo '<fb:visible-to-owner>visbile-to-owner</fb:visible-to-owner>This is an FBML string. The current time is: '.date('r').', and you are '.($user ? '<fb:name uid='.$user.' useyou=false />' : 'anonymous').'.<script>function test(){}</script>.';
  echo '<pre>';
  print_r($_POST);
  echo '</pre>';
}
