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

include_once $_SERVER['PHP_ROOT'].'/lib/display.php';

/**
 * HTTP POST a URL with query parameters, using x-www-form-urlencoded
 * Content-Type. When redirecting, post data will also be re-posted (watch
 * potential security implication of this).
 *
 * @param $url      string  URL with or without some query parameters
 * @param $params   array   post data in name/value pairs
 * @param $options  array   associative array of extra options to pass in. can include:
 *         max_redir          int    how many levels of redirection to attempt (def 1)
 *         conn_timeout       int    number of seconds waiting for a connection (def 3)
 *         timeout            int    total number of seconds to wait, should be
 *                                   strictly bigger than $conn_timeout (def 12)
 *         block_internal     bool   security check - prevent retrieving internal facebook urls (def true)
 *         internal_whitelist array  whitelist these internal domains (def empty)
 *         close_conns        bool   whether to shut down all our db connections
 *                                   before making the request (def true)
 */
function http_post($url, $params, $options=array()) {
  global $APP_MAX_REDIRECTS, $APP_RETRY_CONNECT, $APP_CONNECT_TIMEOUT, $APP_CURL_TIMEOUT;

  $default_options = array('max_redir'          => $APP_MAX_REDIRECTS,
                           'conn_retry'         => $APP_RETRY_CONNECT,
                           'conn_timeout'       => $APP_CONNECT_TIMEOUT,
                           'timeout'            => $APP_CURL_TIMEOUT,
                           'post_tuples'        => null,
                          );
  // $options + $default_options results in an assoc array with overlaps
  // deferring to the value in $options
  extract($options + $default_options);

  $curl = curl_init();
  if ($max_redir < 1) {
    $max_redir = 1;
  }
  $curl_opts = array(CURLOPT_URL => $url,
                     CURLOPT_CONNECTTIMEOUT => $conn_timeout,
                     CURLOPT_TIMEOUT => $timeout,
                     CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
                     CURLOPT_RETURNTRANSFER => 1,
                     CURLOPT_ENCODING => 'gzip',
                     CURLOPT_HTTPHEADER => array('Expect:'));

  curl_setopt_array($curl, $curl_opts);

  $last_url   = $url;
  $redirects  = 0;
  $retries    = 0;

  $post_str = http_build_query($params);
  if (isset($options['post_tuples']) && ($options['post_tuples'])) {
    if ($post_str) {
      $post_str .= '&';
    }
    $assts = array();
    foreach ($options['post_tuples'] as $param_val) {
      list($param, $val) = $param_val;
      $assts []= urlencode($param) . '=' . urlencode($val);
    }
    $post_str .= implode('&', $assts);
  }

  if (isset($params['fb_sig_api_key'])) {
    $app_id = application_get_id_from_key($params['fb_sig_api_key']);
  } else {
    $app_id = null;
  }


  if ($max_redir == 1) {
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_str);
    $response = curl_exec($curl);
  } else {
    $start_time = microtime(true);
    for ($attempt = 0; $attempt < $max_redir; $attempt++) {
      curl_setopt($curl, CURLOPT_HEADER, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post_str);
      $orig_response = curl_exec($curl);

      // Remove any HTTP 100 headers
      $response = preg_replace('/HTTP\/1\.[01] 100.*\r\n(.+\r\n)*\r\n/', '', $orig_response);
      if (preg_match('/^HTTP\/1\.. 30[127].*\nLocation: ([^\r\n]+)\r\n/s',
                     $response, $matches)) {
        $new_url = $matches[1];
        // if $new_url is relative path, prefix with domain name
        if (!preg_match('/^http(|s):\/\//', $new_url) &&
            preg_match('/^(http(?:|s):\/\/.*?)\/|$/', $url, $matches)) {
          $new_url = $matches[1].'/'.$new_url;
        }
        $last_url = $new_url;
        curl_setopt($curl, CURLOPT_URL, $new_url);
        // reduce the timeout, but keep it at least 1 or we wind up with an infinite timeout
        curl_setopt($curl, CURLOPT_TIMEOUT, max($start_time + $timeout - microtime(true), 1));
        ++$redirects;
      } else if ($conn_retry && strlen($orig_response) == 0) {
        // probably a connection failure...if we have time, try again...
        $time_left = $start_time + $timeout - microtime(true);
        if ($time_left < 1) {
          break;
        }
        // ok, we've got some time, let's retry
        curl_setopt($curl, CURLOPT_URL, $last_url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $time_left);
        ++$retries;
      } else {
        break; // we have a good response here
      }
    }
    // NOTE: quicker to use strpos for headers, do not compile a RE
    if (false !== ($pos = strpos($response, "\r\n\r\n"))) {
      $response = substr($response, $pos+4);
    }
  }

  $curl_info = curl_getinfo($curl);

  if ($curl_info['http_code'] == 301 || $curl_info['http_code'] == 302 || $curl_info['http_code'] == 307) {
    throw new HTTPTooManyRedirsException($url);
  }
  if ($curl_info['http_code'] >= 400) {
    throw new HTTPErrorException($url, $curl_info['http_code'], $response);
  }
  if (strlen($response) == 0) {
    if ($curl_info['http_code']) {
      throw new HTTPNoDataException($url, $curl_info['http_code']);
    } else {
      throw new HTTPNoResponseException($url);
    }
  }

  curl_close($curl);

  // take into account http hosts that don't use utf-8
  if (!empty($curl_info['content_type']) && preg_match('#charset=([^;]+)#i', $curl_info['content_type'], $matches)) {
    $encoding = strtoupper($matches[1]);
    if ($encoding != 'UTF-8') {
      $response = iconv($encoding, 'UTF-8', $response);
    }
  }

  return $response;
}

class HTTPException extends Exception {
}

/**
 * An exception that gets thrown when there is an HTTP error (404, 500, etc.)
 */
class HTTPErrorException extends HTTPException {
  public function __construct($url, $error, $content) {
    $msg = 'Received HTTP error code ' . $error . ' while loading ' . htmlize($url);
    parent::__construct($msg, $error);
    $this->_content = $content;
  }
}

class HTTPInvalidUrlException extends HTTPException {
  public function __construct($url) {
    $msg = 'The URL ' . htmlize($url) .  ' is not valid.';
    parent::__construct($msg);
  }
}

class HTTPNoResponseException extends HTTPException {
  public function __construct($url) {
    $msg = 'The URL ' . htmlize($url) . '  did not respond.';
    parent::__construct($msg);
  }
}

class HTTPNoDataException extends HTTPException {
  public function __construct($url, $code) {
    $msg = 'The URL ' . htmlize($url) . ' returned HTTP code ' . $code . ' and no data.';
    parent::__construct($msg, $code);
  }
}

class HTTPTooManyRedirsException extends HTTPException {
  public function __construct($url) {
    $msg = 'The URL ' . htmlize($url) . ' caused too many redirections.';
    parent::__construct($msg);
  }
}

/**
 * Return the raw POST vars
 * @return   dict   {<post-param-1>: <val>, <post-param-2>: <val>, ...}
 *
 * PHP does some weird things with POST var names.  Two examples of this are:
 * 1.  If you have vars named like this x[0], x[1], etc., then PHP will
 * put those into an array for you.
 * 2.  If you any dots (.) in your post var names, then PHP will replace
 * those with underscores.
 *
 * This function returns the POST vars without any of those transformations
 * applied.  It may be useful to do the same thing for GET parameters.
 *
 * Note that the vars returned by this function will never be slash-escaped,
 * regardless of whether you have magic quotes on or off.  yay.
 *
 * IMPORTANT NOTE: this function currently fails to handle 2 things being POSTed
 * with the same value.
 *
 */
function php_input_raw_post_vars() {
  global $PHP_INPUT_RAW_POST_VARS;
  if (isset($PHP_INPUT_RAW_POST_VARS)) {
    return $PHP_INPUT_RAW_POST_VARS;
  }

  $post_string = file_get_contents('php://input');
  $assignments = empty($post_string) ? array() : explode('&', $post_string);
  $post_vars = array();
  foreach ($assignments as $asst) {
    if (strstr($asst, '=')) {
      list($param_e, $val_e) = explode('=', $asst, 2);
      $param = urldecode($param_e);
      $val = urldecode($val_e);
    } else {
      $param = urldecode($asst);
      $val = '';
    }
    $post_vars []= array($param, $val);
  }

  return ($PHP_INPUT_RAW_POST_VARS = $post_vars);

}

/**
 * Tells if this request includes a POSTed multipart form
 * @return    bool    true if the request includes a multipart form
 *
 */
function is_multipart_form() {
  if (!isset($_SERVER['CONTENT_TYPE'])) {
    return false;
  }
  return (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === 0);
}

