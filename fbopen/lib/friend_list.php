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
 * Library of functions that deal with friend lists
 * created by users.
 */


/**
 * Checks to see if the given $uid is the owner of the
 * friend list identified by $flid.
 *
 * @param  $flid  id of friend list
 * @param  $uid   id of user to check ownership
 * @return boolean, true if $uid is owner of friend list
 */
function friend_list_is_owner($flid, $uid) {
  $lists = friend_list_get_lists($uid);
  return isset($lists[$flid]);
}

/**
 * Fetch the metadata for all friend lists owned by $uid
 *
 * @param  $uid  user to fetch friend lists for
 * @return array of friend lists,
 *         friend_list_id => metadata (list id, owner, name)
 */
function friend_list_get_lists($uid) {
  global $data_conn;

  $sql = "SELECT flid, name FROM friend_list WHERE owner=%d";
  $lists  = array();
  if ($data_conn) {
    if ($ret = queryf($data_conn, $sql, $uid)) {
      while ($row = mysql_fetch_assoc($ret)) {
        $lists[$row['flid']] = $row;
      }
    }
    return $lists;
  }
  return null;
}


/**
 * Fetch the members of a friend list
 *
 * @param  $flid  id of friend list
 * @return array of user ids that are members of the friend list
 */
function friend_list_get_members($flid) {
  global $data_conn, $friend_list_members;
  if (isset($friend_list_members[$flid])) {
    return $friend_list_members[$flid];
  }
  $uids  = array();
  if ($data_conn) {
    $sql = 'SELECT uid from friend_list_member WHERE flid = %d';
    if ($ret = queryf($data_conn, $sql, $flid)) {
      while ($row = mysql_fetch_assoc($ret)) {
        $uids[] = $row['uid'];
      }
    }
    $friend_list_members[$flid] = $uids;
    return $uids;
  }
  return null;
}

