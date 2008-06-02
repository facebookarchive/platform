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


// Confidential and Proprietary to Facebook
// Facebook Copyright 2006 - 2008

/**
 * Core initialization. You should include this to get the
 * lightweight, self-contained Facebook PHP runtime. This includes all the
 * common components that are needed to safely run common execution
 * inside the Facebook environment. See below for a list of what this
 * includes.
 */

$GLOBALS['THRIFT_ROOT'] = $_SERVER['PHP_ROOT'] . '/lib/thrift/';

include_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
include_once $GLOBALS['THRIFT_ROOT'].'/autoload.php';

// FBID, id space segmentation, allocation, mapping
include_once $_SERVER['PHP_ROOT'] . '/lib/core/fbid/hash.php';

// Parameter API
include_once $_SERVER['PHP_ROOT'] . '/lib/core/parameter.php';

$PROFILE_DEFAULT_FBML_VERSION = '1.1';

// FOR http_post in sample canvas implementation.  Feel free to tweak.
$APP_MAX_REDIRECTS = 3;
$APP_RETRY_CONNECT = 1;
$APP_CONNECT_TIMEOUT  = 3;
$APP_CURL_TIMEOUT = 14;

// For dummy data db
// FBOPEN:SETUP - Add the IP, username, and password here for the database containing
// the results of the MySQLdump provided.
$DB_USERNAME = 'YOUR_DB_USER'; // FBOPEN:NOTE - replace here with your own.
$DB_IP = 'YOUR_DB_IP'; // FBOPEN:NOTE - replace here with your own.
$DB_PWD = 'YOUR_DB_PWD'; // FBOPEN:NOTE - replace here with your own.

$data_conn = mysql_connect($DB_IP, $DB_USERNAME, $DB_PWD);
$select_result = mysql_select_db('fbopentest', $data_conn);

// FBOPEN:SETUP - replace these with your domain name, suffix, and prefix
// Note that these are used only for XML namespaces in the output.
$API_DOMAIN = 'facebook';
$API_DOMAIN_PREFIX = 'api';
$API_DOMAIN_SUFFIX = 'com';

$API_DOMAIN_DOT_SUFFIX = $API_DOMAIN . '.' . $API_DOMAIN_SUFFIX;

// FBOPEN:SETUP - this is the URL path preceding restserver.php.
// You may need to edit it for batch_run.
$API_HOST = $API_DOMAIN_PREFIX . '.' . $API_DOMAIN_DOT_SUFFIX;

$DEMO_SESSION_KEY = 'acacbdbd010123237878dede-1240077';

// FBOPEN:SETUP - thi is the URL from which html/ will be served
// as the platform.  Note that the fbopentest/ application lives here
// as well for convenience.  Normally these are on separate servers.

$YOUR_PLATFORM_SERVER_URL = 'YOUR_PLATFORM_SERVER_URL';

$PLATFORM_JS_FILES = array(
    'moved.js',
    'fbjs.js',
    'lib/prelude.js',
    'lib/util/bootloader.js',
    'lib/type/array.js',
    'lib/type/object.js',
    'lib/type/function.js',
    'lib/type/string.js',
    'lib/type/list.js',
    'lib/ua/ua.js',
    'lib/event/extensions.js',
    'lib/event/onload.js',
    'lib/event/controller.js',
    'lib/ua/adjust.js',
    'lib/string/escape.js',
    'lib/string/misc.js',
    'lib/string/sprintf.js',
    'lib/string/uri.js',
    'lib/util/util.js',
    'lib/util/configurable.js',
    'lib/math/vector.js',
    'lib/math/rect.js',
    'lib/math/extensions.js',
    'base.js',
    'lib/dom/dom.js',
    'lib/dom/css.js',
    'lib/dom/form.js',
    'lib/dom/html.js',
    'lib/dom/misc.js',
    'lib/dom/control.js',
    'lib/dom/controls/text_input.js',
    'lib/dom/controls/text_area.js',
    'key_event_controller.js',
    'editor.js',
    'timezone.js',
    'lib/ui/typeaheadpro.js',
    'dialogpro.js',
    'typeahead_ns.js',
    'suggest.js',
    'error_data.js',
    'lib/ui/animation.js',
    'lib/ui/dialog.js',

    );

