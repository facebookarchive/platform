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

include_once $_SERVER['PHP_ROOT'].'/lib/api/auth.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/api_xml.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/api_json.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/errorcodes.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/session.php';
include_once $_SERVER['PHP_ROOT'].'/lib/api/application.php';

include_once $_SERVER['PHP_ROOT'].'/lib/api/1.0/implementation.php';

// Fetch method name and session key
param_request(array('method'      => $PARAM_STRING,
                    'session_key' => $PARAM_STRING,
                    'format'      => $PARAM_STRING,
                    'callback'    => $PARAM_STRING,
                    'v'           => $PARAM_STRING,
             ));

$version = api_resolve_version($req_v);

// Sanitize response format
$format = strtolower($req_format);
if (!in_array($format, array('xml', 'json'))) {
  $format = 'xml';
}

// Set content type
if ($req_callback) {
  header('Content-type: text/javascript');
} else if ($format == 'json') {
  header('Content-type: application/json');
} else {
  header('Content-type: text/xml');
}

// Initialize result
$result = array();

// Use this instead of $_REQUEST, which contains cookies that we don't want
$request = array_merge($_GET, $_POST);

if (get_magic_quotes_gpc()) {
  foreach ($request as &$slashed) {
    $slashed = stripslashes($slashed);
  }
  unset($slashed);
}

// Fix method name
if (starts_with($req_method, $API_DOMAIN . '.')) {
  $method = substr($req_method, 9);
} else {
  $method = $req_method;
}

// Replace periods with underscores in method name
$method_underscore = str_replace('.', '_', $method);

// Authenticate the request
try {

  $ec = null;
  $user_id = null;

  // FBOPEN: NOTE - you may wish to throttle the use of certain methods.
  // One wy is to put "infinite-use" methods in this $nothrottle array.
  $nothrottle = array();

  $throttle = !isset($nothrottle[$method_underscore]);


  $use_session_secret = isset($request['ss']) ? $request['ss'] : false;

  // Check call limits, IPs, signatures, etc.
  $ec = api_validate_api_request($request, $app_id, $user_id, $throttle, $use_session_secret);

  if ($ec !== API_EC_SUCCESS) {
    $msg = $api_error_descriptions[$ec];
    if ($ec === API_EC_BAD_IP) {
      $msg .= ' (ip was: '.$_SERVER['REMOTE_ADDR'].')';
    }
    throw new api10_FacebookApiException(array('error_code' => $ec,
                                               'error_msg' => $msg));
  }

  // Create API handler
  switch ($version) {
    case API_VERSION_10:
    default:
      $impl = new FacebookApi10Implementation($app_id, $user_id, $req_session_key, $format, $use_session_secret);
      $api = new FacebookApi10Rest($impl);
      break;
  }

  // Check that the method is valid
  if (!method_exists($api, $method_underscore) ||
      !method_exists($impl, $method_underscore) ||
      !api_can_call_method($app_id, $method_underscore, $use_session_secret)) {

    $ec = api10_FacebookApiErrorCode::API_EC_METHOD;
    throw new api10_FacebookApiException(array('error_code' => $ec,
                                               'error_msg' => $api_error_descriptions[$ec]));
  } else {

    // Call the method and catch any exceptions
    $result = $api->$method_underscore($request);

  }
} catch (api10_FacebookApiException $fax) {
  if ($fax instanceof FacebookFQLException && $method_underscore != 'fql_query') {
    $ec = api10_FacebookApiErrorCode::API_EC_PARAM;
    $fax = new api10_FacebookApiException(array('error_code' => $ec,
                                                'error_msg' => $api_error_descriptions[$ec]));
  }
  $ec = $fax->error_code;
  $result = $fax;
  $args = array();
  foreach ($request as $key => $val) {
    $args []= new api10_arg(array('key' => $key, 'value' => $val));
  }
  $result->request_args = $args;
} 

switch ($format) {
 case 'manual':
   print api_xml_render_manual_error($ec, $msg, $request);
   break;

 case 'xml':
   // Prepare the XML response
   $xml_memory = xmlwriter_open_memory();
   xmlwriter_set_indent($xml_memory, true);
   xmlwriter_set_indent_string($xml_memory, '  ');
   xmlwriter_start_document($xml_memory, API_VERSION_10, 'UTF-8');

   switch ($version) {
   case API_VERSION_10:
   default:
     if ($result instanceof Exception) {
       $name = 'error_response';
     } else {
       $name = $method_underscore.'_response';
     }
     $attrs = array();
     $attrs['xmlns']              = 'http://api.'.$API_DOMAIN_DOT_SUFFIX.'/'.$version.'/';
     $attrs['xmlns:xsi']          = 'http://www.w3.org/2001/XMLSchema-instance';
     if ($method_underscore != 'fql_query') {
       $attrs['xsi:schemaLocation'] = 'http://api.'.$API_DOMAIN_DOT_SUFFIX.'/'.$version.'/ http://api.'.$API_DOMAIN_DOT_SUFFIX.'/'.$version.'/facebook.xsd';
     }
     if (is_array($result) && isset($result[0]) && $result[0] instanceof xml_element) {
       $attrs['list'] = 'true';
       api_xml3_render_object($xml_memory, new xml_element($name, $result, $attrs));
     } else {
       api_xml2_render_object($xml_memory, $name, $result, $attrs);
     }
     break;
   }

   xmlwriter_end_document($xml_memory);

   // Write XML response
   $xml = xmlwriter_output_memory($xml_memory, true);
   if ($req_callback) {
     $xml = addslashes($xml);
     $xml = str_replace("\n", '\\n', $xml);
     echo $req_callback.'(\''.$xml.'\');';
   } else {
     echo $xml;
   }
   break;

 case 'json':
   $json = api_json2_render_object($result);
   if ($req_callback) {
     echo $req_callback.'('.$json.');';
   } else {
     echo $json;
   }
   break;
}

