<?php

/******BEGIN LICENSE BLOCK*******
* 
* Common Public Attribution License Version 1.0.
*
* The contents of this file are subject to the Common Public Attribution 
* License Version 1.0 (the "License") you may not use this file except in 
* compliance with the License. You may obtain a copy of the License at
* http://developers.facebook.com/fbopen/cpal.html. The License is based 
* on the Mozilla Public License Version 1.1 but Sections 14 and 15 have 
* been added to cover use of software over a computer network and provide 
* for limited attribution for the Original Developer. In addition, Exhibit A 
* has been modified to be consistent with Exhibit B.
* Software distributed under the License is distributed on an "AS IS" basis, 
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License 
* for the specific language governing rights and limitations under the License.
* The Original Code is Facebook Open Platform.
* The Original Developer is the Initial Developer.
* The Initial Developer of the Original Code is Facebook, Inc.  All portions 
* of the code written by Facebook, Inc are 
* Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
*
*
********END LICENSE BLOCK*********/


// Facebook Copyright 2006 - 2008

include_once $_SERVER['PHP_ROOT'].'/lib/core/init.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/application.php';
include_once $_SERVER['PHP_ROOT'].'/lib/networking.php';
include_once $_SERVER['PHP_ROOT'].'/lib/privacy/platform.php';
include_once $_SERVER['PHP_ROOT'].'/lib/utils/strings.php';
include_once $_SERVER['PHP_ROOT'].'/lib/http.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/fetch.php';

include_once $_SERVER['PHP_ROOT'].'/lib/fql/includes.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/wrapper.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/flavors/canvas_page_flavor.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/implementation/css.php';

param_get(array('fb_app_name'   => $PARAM_STRING,
                'fb_url_suffix' => $PARAM_STRING,
                'fb_api_key'    => $PARAM_STRING,
                'fb_user_id'    => $PARAM_INT, // FBOPEN:NOTE - you may wish to make user_id a string in your system
                'fb_force_mode' => $PARAM_STRING));



// FBOPEN:NOTE : html/canvas.php is a simple demonstration of canvas-like
// : functionality, and will vary widely on your system.
// : You can try this demo by putting this under your document root and
// : sending a request to:
// :  yourapp.demo/canvas.php?fb_app_name=AAAA&fb_user_id=UUUU&fb_url_suffix=SSSSS
// : where AAAA is your app's "display" name (apps.facebook.com/AAAA),
// : UUUU is the 'logged in' user id,
// : and fb_url_suffix is a URL-encoded string of the parameters
// : you wish to send to your callback URL.

$rel_canvas_url = $_SERVER['REQUEST_URI'];
$url_suffix = urldecode($get_fb_url_suffix);


if ($get_fb_app_name) {
  $app_id = application_get_fbframe_id($get_fb_app_name);
} else if ($get_fb_api_key) {
  $app_id = application_get_id_from_key($get_fb_api_key);
} else {
  print('No app corresponding to app name or api key parameters');
  error_log('No app corresponding to app name or api key parameters');
  exit();
}

print_canvas_javascript_references();

if (!($user = $get_fb_user_id)) {
  print('No user id parameter');
  error_log('No user id parameter');
  exit();
}

$canvas_url = redirect_str($rel_canvas_url, 'www',
                           $ssl=0, $force_prod=false, $force_protocol=true);

print "Facebook Open Platform: Output of Canvas url: $canvas_url<hr>";

// no app_id found so assume bad link
if (!$app_id) {
  print('No app corresponding to app name or api key parameters');
  error_log('No app corresponding to app name or api key parameters');
  exit();
}

$app_info = application_get_info($app_id);

if (!$app_info) {
  print('No app corresponding to app name or api key parameters');
  error_log('No app corresponding to app name or api key parameters');
  exit();
}

if (!platform_can_see_app($app_id, $user)) {
  print("User $user cannot see app id $app_id.  FBOPEN:NOTE - this message should be invisible to the user.");
  error_log("User $user cannot see app id $app_id.  FBOPEN:NOTE - this message should be invisible to the user.");
  exit();
}

$app_icon_url = application_get_icon_url($app_id);
$url = $app_info['callback_url'] . $url_suffix;


$fbml_env = array('user' => $user,
    'app_id' => $app_id,
    'canvas_url' => $canvas_url,
    'source_url' => $url
    );

switch ($get_fb_force_mode) {
  case 'fbml':
    $use_iframe = false;
    break;
  case 'iframe':
    $use_iframe = true;
    break;
  default:
    $use_iframe = $app_info['use_iframe'];
    break;
}

if ($use_iframe) {
  $fbml_from_callback = '<fb:iframe src="' . htmlize_filters($url) . '" smartsize="true" frameborder="0"/>';
} else {
  //
  // If we are interpreting a regular form, then we avoid the mangling that
  // happens when PHP constructs $_POST by interpreting the urlencoded form
  // directly from php://input.
  //
  $in_post_vars = noslashes_recursive($_POST);
  if (is_multipart_form()) {
    $post_tuples = null;
  } else {
    $in_post_tuples = php_input_raw_post_vars();
    $post_tuples = array();
    foreach ($in_post_tuples as $param_val) {
      $post_tuples []= $param_val;
    }
  }


  list($others, $post_vars) = api_get_valid_fb_params($in_post_vars, $app_info['secret']);
  // If we took POST tuples that we want to pass along raw, then we
  // won't use the vars we got from $_POST
  if ($post_tuples !== null) {
    $post_vars = array();
  }

  $others += api_canvas_parameters();
  $data_params = api_canvas_parameters_other($app_id, $user);
  $post_vars += get_fb_validation_vars($user, $app_id, $others, $data_params);

  $path_str = '/'.$url_suffix;
  if (($char_pos = strpos($path_str, '?', 0)) !== false) {
    $path_str = substr($path_str, 0, $char_pos);
  }

  $char_pos = strrpos($path_str, '/', 0);
  if ($char_pos > 0) {
    $path_str = substr($path_str, 0, $char_pos+1);
  }

  try{
    try {
      $fbml_from_callback = http_post($url, $post_vars, array(
        'post_tuples' => $post_tuples,
        ));
    } catch (HTTPErrorException $e) {
      print "got http exception: " . $e->getCode();
      exit();
      header('HTTP/1.x '.$e->getCode());  //Respect the 400+ status codes
      throw $e; //rethrow, so that the page renders properly
    }
  } catch (HTTPException $e) {
    $fbml_from_callback = isset($e->_content) ? $e->_content : '';
  }
}

$fbml_flavor = new FBMLCanvasPageFlavor($fbml_env);
$fbml_impl = new FBJSEnabledFacebookImplementation($fbml_flavor);

$html = fbml_sample_parse($fbml_from_callback, $fbml_impl);

// Note: These act as blocks so that getParentId will never actually get the
// true <body> element in js.
echo '<div id="' . fbml_css_app_box_id($app_id) . '" class="'.fbml_css_app_box_id($app_id).' "><div>';
echo $html;
echo '</div></div>';
