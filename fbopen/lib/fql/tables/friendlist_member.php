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

include_once $_SERVER['PHP_ROOT'].'/lib/friend_list.php';

/**
 * Sample implementation of a table containing friend list
 * membership data that can be queried by FQL.
 *
 * This table is only indexed by $flid (friend list id).
 *
 * Ids and queries for this table are represented by $flid:$uid
 * where $flid is the friend list id and $uid is the id of a member
 * in the friend list.
 */
class FQLFriendListMemberTable extends _FQLBaseTable {
  public static function get_fields() {
    return array('flid'  => 'FQLFriendListId',
                 'uid'   => 'FQLFriendListMember',
                );
  }

  // Dummy privacy function that enforces that you can only see
  // a friend list id - member id pair if you are the owner
  // of the friend list and you are friends with the member
  public function can_see($id) {
    list($flid, $uid) = explode(':', $id);
    return
      friend_list_is_owner($flid, $this->user) &&
      are_friends($this->user, $uid);
  }

  public function intersect_queries($query1, $query2) {
    return $this->intersect_dual_queries($query1, $query2);
  }

  /**
   * Helper function that populates the $result array with
   * all the row ids that match the given $flid and $id pair.
   *
   * @param  $flid    id of the friend list
   * @param  $id      if specified, only gets row ids for
   *                  rows matching this user id as a member
   * @param  $result  array passed by reference that will be populated
   *                  with the row ids, all mapping to 1
   */
  private function get_all_members($flid, $id, &$result) {
    $members = friend_list_get_members($flid);
    if ($id) {
      if (isset($members[$id])) {
        $result["$flid:$id"] = 1;
      }
    } else {
      foreach ($members as $member_id) {
        $result["$flid:$member_id"] = 1;
      }
    }
  }

  // Enforces the restriction that you can only examine friend list members
  // of friend lists owned by the logged in user
  public function get_ids_for_queries($queries, $cap) {
    $result = array();
    foreach ($queries as $query => $true) {
      list($flid, $uid) = explode(':', $query);
      if (!$flid || !friend_list_is_owner($flid, $this->user)) {
        throw new NoIndexFunctionException("Can't lookup friend lists not owned by the logged in user (" . $this->user . ')');
      }
      $this->get_all_members($flid, $uid, $result);
    }
    return $result;
  }

  public function prime_cache($ids) {
  }
}
