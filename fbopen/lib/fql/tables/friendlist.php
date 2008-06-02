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
 * Sample implementation of a class that represents a table of
 * friend lists that can be queried by FQL.
 *
 * This table is indexable by owner id only.
 *
 * Ids and queries for this table are repesented by $flid:$uid,
 * where $flid is the friend list id and $uid is the user id
 * of the owner of the friend list.
 */
class FQLFriendListTable extends _FQLBaseTable {
  public static function get_fields() {
    return array('flid'    => 'FQLFriendListId',
                 'owner'   => 'FQLFriendListOwner',
                 'name'    => 'FQLFriendListName',
                 );
  }

  // Dummy privacy function that enforces the restriction that
  // friend lists can only be seen if the logged in user is the
  // owner of the friend list
  public function can_see($id) {
    list($flid, $uid) = explode(':', $id);
    return
      ($this->user == $uid) &&
      friend_list_is_owner($flid, $uid);
  }

  public function intersect_queries($query1, $query2) {
    return $this->intersect_dual_queries($query1, $query2);
  }

  /**
   * Helper function that gets all row ids that match the
   * given $uid and $flid parameters and populates them into
   * the $result array, passed by reference.
   *
   * @param  $uid    id of user to get friend lists for
   * @param  $flid   if specified, only gets friend list id
   *                 matching this id
   * @param  $result array passed by reference that will be populated
   *                 by the resulting ids, where row id => 1
   */
  private function get_all_lists($uid, $flid, &$result) {
    $lists = friend_list_get_lists($uid);
    if ($flid) {
      if (isset($lists[$flid])) {
        $result["$flid:$uid"] = 1;
      }
    } else {
      foreach ($lists as $flid => $list) {
        $result["$flid:$uid"] = 1;
      }
    }
  }

  // Enforces the restriction that you can only lookup friend lists
  // owned by the logged in user.
  public function get_ids_for_queries($queries, $cap) {
    $result = array();
    foreach ($queries as $query => $true) {
      $query_arr = explode(':', $query);
      if (!$query_arr[1] || ($query_arr[1] != $this->user)) {
        throw new NoIndexFunctionException("Can only lookup friend lists of the logged in user (" . $this->user . ')');
      }
      $this->get_all_lists($query_arr[1], $query_arr[0], $result);
    }
    return $result;
  }

  public function get_name() {
    return 'friendlist';
  }

  public function prime_cache($ids) {
  }
}
