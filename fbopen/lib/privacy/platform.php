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
 * Returns whether or not an app has permissions to all available user data via tha api
 *
 * @param int   $app_id
 * @param int   $user_id
 * @return bool
 */
function platform_app_has_full_permission($app_id, $user_id) {
  // FBOPEN:NOTE - For simplicity, assume these functions are the same.
  return is_platform_app_installed($app_id, $user_id);
}

/**
 * Returns whether an application is visible to a certain user
 * useful when testing a beta version of an app
 * @param int $app_id
 * @param int $user_id
 * @param arr app_info - optional app_info in case we have it already from doing a phatty multiget earlier
 * @return bool
 *
 */
function platform_can_see_app($app_id, $user_id, $app_info=null) {
  return true;
}

