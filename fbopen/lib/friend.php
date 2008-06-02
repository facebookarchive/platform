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
 * Library of functions dealing with friend relationships
 * between users.
 */

/**
 * Fetches the user ids of all the friends of $user.
 *
 * @param  $user user to fetch friends for
 * @return array of user ids that $user is friends with
 */
function user_get_all_friends($uid) {
  global $data_conn, $all_friend_sets;
  if (isset($all_friend_sets[$uid])) {
    return $all_friend_sets[$uid];
  }

   $sql = "SELECT user2 FROM friend WHERE user1=%d";
   $friend_arr = array(); 
   if ($data_conn) {
     if ($ret = queryf($data_conn, $sql, $uid)) {
       while ($row = mysql_fetch_assoc($ret)) {
         $friend_arr[] = $row['user2'];
       }
     }
   }
   $all_friend_sets[$uid] = $friend_arr;
   return $friend_arr;
}

/**
 * Dummy helper function for sample implementation.
 * Returns whether or not $id1 and $id2 are friends.
 *
 * @param  $id1 first user
 * @param  $id2 second user
 * @return boolean, true if $id1 and $id2 are friends
 */
function are_friends($id1, $id2) {
  $friends = user_get_all_friends($id1);
  return in_array($id2, $friends);
}


