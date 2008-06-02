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


/**
 * Copyright (c) 2006- Facebook
 * Distributed under the Thrift Software License
 *
 * See accompanying file LICENSE or visit the Thrift site at:
 * http://developers.facebook.com/thrift/
 *
 * @package thrift
 * @author Mark Slee <mcslee@facebook.com>
 */

/**
 * Include this file if you wish to use autoload with your PHP generated Thrift
 * code. The generated code will *not* include any defined Thrift classes by
 * default, except for the service interfaces. The generated code will populate
 * values into $GLOBALS['THRIFT_AUTOLOAD'] which can be used by the autoload
 * method below. If you have your own autoload system already in place, rename your
 * __autoload function to something else and then do:
 * $GLOBALS['AUTOLOAD_HOOKS'][] = 'my_autoload_func';
 *
 * Generate this code using the -phpa Thrift generator flag.
 */

$GLOBALS['THRIFT_AUTOLOAD'] = array();
$GLOBALS['AUTOLOAD_HOOKS'] = array();

if (!function_exists('__autoload')) {
  function __autoload($class) {
    global $THRIFT_AUTOLOAD;
    $classl = strtolower($class);
    if (isset($THRIFT_AUTOLOAD[$classl])) {
      include_once $GLOBALS['THRIFT_ROOT'].'/packages/'.$THRIFT_AUTOLOAD[$classl];
    } else if (!empty($GLOBALS['AUTOLOAD_HOOKS'])) {
      foreach ($GLOBALS['AUTOLOAD_HOOKS'] as $hook) {
        $hook($class);
      }
    }
  }
}
