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
 * Extract the privacy constants used to specify option values:
 *
 * 5 = EVERYBODY: Everyone on Facebook, outside your networks and non-friends
 * 4 = NETWORKS: All my networks and friends
 * 3 = SOME_NETWORKS: Some of my networks and friends
 * 2 = ALL_FRIENDS: Only my friends
 * 1 = SELF: Only yourself
 * 0 = NOBODY: Absolutely nobody, not even yourself
 *
 * @return array  The option constants associative array
 */
function privacy_extract_option_constants() {

  $extract['PRIVACY_OPTION'] = array(
           'EVERYBODY'     => 5,
           'ALL_NETWORKS'  => 4,
           'SOME_NETWORKS' => 3,
           'ALL_FRIENDS'   => 2,
           'SELF'          => 1,
           'NOBODY'        => 0 );

  return $extract;
}


