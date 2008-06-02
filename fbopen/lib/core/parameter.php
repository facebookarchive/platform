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

/**
 * PARAMETER API
 *
 * API to protect access to $_GET, $_POST, and $_COOKIE via type checking.
 * Useful for validating input in a consistent manner and protecting against
 * SQL injection attacks, etc.
 *
 */

/**
 * Extract the param constants into global scope.
 *
 */
function init_parameter() {
  static $init_parameter = false;
  if ($init_parameter) {
    return;
  }
  $init_parameter = true;

  $params = param_constants();
  foreach ($params as $key => $val) {
    $GLOBALS[$key] = $val;
  }
  $GLOBALS['PARAM_DEBUG_LEVEL_ADD'] = 0;
}

/**
 * Returns an array of the parameter constants
 *
 * @return      an assoc that contains PARAM_STRING, PARAM_INT, etc.
 *
 */
function param_constants() {
  $pv = array(
      // Logical filters
      'PARAM_STRING' => 0x00000001,
      'PARAM_INT'    => 0x00000002,   // default is unsigned
      'PARAM_UINT'   => 0x00000002,   // unsigned integer, this should be the most commonly used int type
      'PARAM_SINT'   => 0x00000004,   // signed integer
      'PARAM_FLOAT'  => 0x00000008,
      'PARAM_BOOL'   => 0x00000010,
      'PARAM_HEX'    => 0x00000020,
      'PARAM_EXISTS' => 0x00000040,   // just validate whether a param is set and cast a boolean
      'PARAM_ARRAY'  => 0x00000080,
      'PARAM_RAW'    => 0x00000100,   // perform no additional processing, this is dangerous!

      // Satitizing filters + options
      'PARAM_STRIPTAGS' => 0x00001000,  // call strip_tags, applies only to strings
      'PARAM_HASHVAR'   => 0x00002000,  // user facing variable is a hash, param_ must be called after init/require_lo gin
      'PARAM_MD5'       => 0x00004000,  // md5 var, value must match url hash, param_ must be called after init/requir e_login
      'PARAM_ERROR'     => 0x00008000,  // error callback instead of sending user home when there is an error
      'PARAM_ALLOW_A'   => 0x00010000,  // allow href when calling strip_tags
      'PARAM_ALLOW_B'   => 0x00020000,  // allow bold when calling strip_tags
      'PARAM_USERID'    => 0x00040000,  // validate against numeric userids
      'PARAM_OBJID'    => 0x00080000,  // validate against numeric object ids
      );

  // type domains
  $pv['PARAM_TEXT']   = $pv['PARAM_STRING'] ^ $pv['PARAM_STRIPTAGS'];
  $pv['PARAM_ID']     = $pv['PARAM_INT'];
  $pv['PARAM_UID']    = $pv['PARAM_INT'] ^ $pv['PARAM_USERID'];
  $pv['PARAM_TID']    = $pv['PARAM_INT'] ^ $pv['PARAM_OBJID'];
  $pv['PARAM_URLMD5'] = $pv['PARAM_STRING'] ^ $pv['PARAM_MD5'];

  return $pv;
}

/**
 * Gets input parameters from GET request.
 * - we don't allow a blank prefix unless you are loading into an array;
 * we force you to distinguish variables coming from the outside world
 *
 * this function returns raw data.  your strings will not be magic quoted.
 *
 * @param array  $def    Definiton
 * @param string $prefix String prefix, default is get_
 * @return bool Success
 */
function param_get($def, $prefix=null, &$scope=null) {
  // see descripton
  if ($prefix === null || !$prefix && is_null($scope)) {
    $prefix = 'get_';
  }
  return _param_handle($_GET, $def, $prefix, $scope);
}

/**
 * Gets input parameters from POST request.
 * - we don't allow a blank prefix unless you are loading into an array;
 * we force you to distinguish variables coming from the outside world
 *
 * @param array  $def    Definiton
 * @param string $prefix String prefix, default is post_
 * @return bool Success
 */
function param_post($def, $prefix=null, &$scope=null) {
  // see descripton
  if ($prefix === null || !$prefix && is_null($scope)) {
    $prefix = 'post_';
  }
  return _param_handle($_POST, $def, $prefix, $scope);
}

/**
 * Gets input parameters from GET/POST request.
 * - we don't allow a blank prefix unless you are loading into an array;
 * we force you to distinguish variables coming from the outside world
 *
 * @param array  $def    Definiton
 * @param string $prefix String prefix, default is req_
 * @return bool Success
 */
function param_request($def, $prefix=null, &$scope=null) {
  // see descripton
  if ($prefix === null || !$prefix && is_null($scope)) {
    $prefix = 'req_';
  }
  return _param_handle($_POST + $_GET, $def, $prefix, $scope);
}

/**
 * Gets input parameters from COOKIE request.
 *
 * @param array  $def    Definiton
 * @param string $prefix String prefix, default is cookie_
 * @return bool Success
 */
// FBOPEN:NOTE - not used but included for completeness
function param_cookie($def, $prefix=null, &$scope=null) {
  if ($prefix === null || !$prefix && is_null($scope)) {
    $prefix = 'cookie_';
  }

  // For MSIE6.0, we don't "delete" cookies, but rather set their
  // value to "deleted".
  foreach ($def as $var => &$flags) {
    if (isset($_COOKIE[$var]) && 'deleted' === $_COOKIE[$var]) {
      unset($_COOKIE[$var]);
    }
  }

  return _param_handle($_COOKIE, $def, $prefix, $scope);
}


function _param_handle($source, $def, $prefix=null, &$scope=null) {
  extract(param_constants());

  $_PARAM_NULLOK = $PARAM_UINT ^ $PARAM_SINT ^ $PARAM_FLOAT ^ $PARAM_BOOL ^ $PARAM_HEX;

  $errors = array();  // keep track of PARAM_ERROR failures

  if (!is_array($scope)) {
    $scope = $GLOBALS;
  }

  if (!is_array($def)) {
    _param_error('PARAM: invalid definition provided');
  }

  foreach ($def as $var => $flags) {
    $sourcevar = $var;  // var name in source

    if ($flags & $PARAM_HASHVAR && isset($GLOBALS['user'])) {
      $sourcevar = md5($user.$var.$GLOBALS['post_md5key']);
    }

    if (isset($source[$sourcevar])) {  // do no change without accounting for $PARAM_EXISTS

      $ref = $source[$sourcevar];  // current var requested

      // raw param
      if ($flags & $PARAM_RAW) {
        $scope[$prefix.$var] = $ref;
        continue;
      }

      // support array type from forms
      if ($flags & $PARAM_ARRAY) {
        if (is_array($ref)) {
          foreach ($ref as $key => $r) {
            $ret = array();
            // call param_handle recursively on each array element to validate value
            if (! _param_handle(array('a' => $r),
                                array('a' => ($flags & ~$PARAM_ARRAY)),
                                'a_',
                                $ret)) {  // don't continue if there's a failure
              return false;
            }


            if (isset($ret['a_a'])) {
              $scope[$prefix.$var][$key] = $ret['a_a'];
            }
          }
          continue;
        }
      }

      if ($flags & $_PARAM_NULLOK) {
        if ($ref == '') {
          $scope[$prefix.$var] = null;
          continue;
        }
      }

      if ($flags & $PARAM_UINT) { // unsigned (non-negative integer)
          if (ctype_digit($ref) || is_int($ref)) {

            if ($flags & $PARAM_OBJID&& !is_fbobj((int)$ref)) {
              _param_error("PARAM: invalid value for $var, expected OBJID. [$ref]", $var, $flags, $ref, $errors);
            }

            if ($flags & $PARAM_USERID && !is_user_id((int)$ref)) {
              _param_error("PARAM: invalid value for $var, expected USERID. [$ref]", $var, $flags, $ref, $errors);
            }

            $scope[$prefix.$var] = (int)$ref;
          } else {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') !== false && preg_match('/[0-9]+[0-9a-f]{8}$/', $ref) == 1) {
              _param_fail(); // don't spew on detectible ie7b2 junk, a char hex is appended to int get var
              exit;
            }
            _param_error("PARAM: invalid content for $var, expected INT. [$ref]", $var, $flags, $ref, $errors);
          }
      } elseif ($flags & $PARAM_SINT) { // signed (negative ok integer)
        if (ctype_digit($ref) || is_int($ref)) {
          $scope[$prefix.$var] = (int)$ref;
        } else {
          if ($ref[0] == '-' && ctype_digit(substr($ref, 1))) {  // allow negative numbers for int
            $scope[$prefix.$var] = (int)$ref;
          } else {
             if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') !== false && preg_match('/[0-9]+[0-9a-f]{8}$/', $ref) == 1) {
              _param_fail();  // don't spew on detectible ie7b2 junk, a char hex is appended to int get var
              exit;
            }
           _param_error("PARAM: invalid content for $var, expected INT. [$ref]", $var, $flags, $ref, $errors);
          }
        }
      } elseif ($flags & $PARAM_FLOAT) {
        if (preg_match('/^[0-9\.]*$/i', $ref)) {
          $scope[$prefix.$var] = (float)$ref;
        } else {
          _param_error("PARAM: invalid content for $var, expected FLOAT. [$ref]", $var, $flags, $ref, $errors);
        }
      } elseif ($flags & $PARAM_BOOL) {
        switch (strtolower($ref)) {
          case '0':
          case '1':
            $scope[$prefix.$var] = (bool)$ref;
            break;
          case 'true':
          case 'on':
          case 'yes':
            $scope[$prefix.$var] = true;
            break;
          case 'false':
          case 'off':
          case 'no':
            $scope[$prefix.$var] = false;
            break;
          default:
            _param_error("PARAM: invalid content for $var, expected BOOL. [$ref]", $var, $flags, $ref, $errors);
            break;
        }
      } elseif ($flags & $PARAM_EXISTS) { // already passed isset above
        $scope[$prefix.$var] = true;
      } elseif ($flags & $PARAM_HEX) {
        if (ctype_xdigit($ref)) {
          // NOTE: (int) cast because hexdec can return a float if the hex
          // string is above INT_MAX. In those cases this will return 0, so
          // that is_int($result) will always be true if $result came from
          // $PARAM_HEX
          $scope[$prefix.$var] = (int)hexdec($ref);
        } else {
          _param_error("PARAM: invalid content for $var, expected HEX. [$ref]", $var, $flags, $ref, $errors);
        }
      } elseif ($flags & $PARAM_STRING) {
        if ($flags & $PARAM_MD5) {
          if (!url_checkmd5($GLOBALS['url_md5key'], $var)) {
            _param_error("PARAM: failed url_md5 check for $var.", $var, $flags, $ref, $errors);
          }
        }

        $ref = noslashes($ref);

        if ($flags & $PARAM_STRIPTAGS) {
          $allowtags = '';

          if ($flags & $PARAM_ALLOW_A) {
            $allowtags .= '<a>';
          }

          if ($flags & $PARAM_ALLOW_B) {
            $allowtags .= '<b>';
          }
          $ref = strip_tags($ref, $allowtags);

          // entity protection
          if ($allowtags && strpos($ref, '=') !== false) {  // is there an entitity we need to protect?
            // um, sorry this is ugly but should get most xss entities
            $exprs = array('/( on[a-z]{1,}|style|class|id|target)="(.*?)"/i',
                           '/( on[a-z]{1,}|style|class|id|target)=\'(.*?)\'/i',
                           '/( on[a-z]{1,}|style|class|id|target)=(.*?)( |>)/i',
                           '/([a-z]{1,})="(( |\t)*?)(javascript|vbscript|about):(.*?)"/i',
                           '/([a-z]{1,})=\'(( |\t)*?)(javascript|vbscript|about):(.*?)\'/i',
                           '/([a-z]{1,})=(( |\t)*?)(javascript|vbscript|about):(.*?)( |>)/i',
                          );

            $reps = array('', '', '$3', '$1=""', '$1=""', '$1=""$6');
            $ref = preg_replace($exprs, $reps, $ref);
          }
        }
        // strip out any \r characters. all we need is \n
        $ref = str_replace("\r","",$ref);

        $scope[$prefix.$var] = (string)$ref;
      } elseif ($flags == 0) {
        error_log("PARAM: var ($var) has invalid type, undefined (0)");
        return false;
      }
    } else { // exists in source
      if ($flags & $PARAM_EXISTS) {
        $scope[$prefix.$var] = false;
      } else {
        $scope[$prefix.$var] = null;
      }
    }
  } // each def

  if (count($errors) > 0) {  // do any callbacks for PARAM_ERROR types
    foreach ($errors as $error) {
      // callback w/ params varname, flags, user entered value
      call_user_func_array('param_callback_error', $error);
    }
  }

  return true;
}

function _param_error($str, $var='', $flags=0, $value='', &$errors=null) {
  global $PARAM_ERROR, $PARAM_DEBUG_LEVEL_ADD;

  if ($flags && ($flags & $PARAM_ERROR)) {
    $errors[] = array($var, $flags, $value);
  } else {
    error_log($str);
    _param_fail();
  }
}

/**
 *  Handle "fatal" parameter API errors, where a user has passed data which is
 *  not convertible to the expected type. Our usual response to this is to call
 *  go_home(); this should be changed but has been the status quo fo a long,
 *  long time.
 *
 *  However, if we're responding to an AsyncRequest or AsyncSignal, we should
 *  always return a legitimate async response. We fish around in $GLOBALS for
 *  an active response (hooray!) and use that to transmit the error if we find
 *  one.
 *
 */
function _param_fail() {
  exit;
}

function noslashes($s) {
  if (!get_magic_quotes_gpc())
    return $s;
  return stripslashes($s);
}

// Initialize
init_parameter();
