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
 * Dummy implementation of a class that represents a table
 * of friend relationships that can be queried by FQL.
 *
 * Only two columns are present, uid1 and uid2, the presence
 * of a row containing uid1 and uid2 implies a friend relationship
 * between the two users.
 *
 * Both uid1 and uid2 are indexable.  However, this table
 * enforces the restriction that one cannot query for all of a
 * user's friends unless the logged in user is the user being
 * queried.
 */
class FQLFriendTable extends _FQLBaseTable {
  public static function get_fields() {
    return array('uid1' => 'FQLFriend1',
                 'uid2' => 'FQLFriend2');
  }

  public function can_see($id) {
    // don't enforce any permissions
    return true;
  }

  public function prime_cache($ids) {
  }

  /**
   * Intersects two queries for this dummy implementation of
   * the friends table.
   *
   * A query for this table has the format uid1:uid2.
   * The following queries translate to these logical meanings:
   * ':'     => matches all rows in the table
   * '17:'   => matches all rows where uid1 = 17, a.k.a. all of 17's friends
   * ':21'   => matches all rows where uid2 = 21, a.k.a. all of 21's friends
   * '18:21' => matches rows where uid1 = 18 and uid2 = 21, only returns
   *            a row if 18 and 21 are friends
   *
   * Queries are intersected to return a single query that fulfills
   * the constraints expressed in the original queries. If no single
   * query can fulfill all the constraints (e.g. intersecting '17:' and
   * '18:' then it returns false. This function also performs an are_friends
   * check before returning a query of the form ('18:21') to ensure
   * that the two ids are indeed friends.
   */
  public function intersect_queries($query1, $query2) {
    $q1_arr = explode(':', $query1);
    $q2_arr = explode(':', $query2);
    $result_arr = array();
    if ($q1_arr[0]) {
      if ($q2_arr[0] && $q1_arr[0] != $q2_arr[0]) {
        return false;
      }
      $res_arr[0] = $q1_arr[0];
    } else {
      $res_arr[0] = $q2_arr[0];
    }
    if ($q1_arr[1]) {
      if ($q2_arr[1] && $q1_arr[1] != $q2_arr[1]) {
        return false;
      }
      $res_arr[1] = $q1_arr[1];
    } else {
      $res_arr[1] = $q2_arr[1];
    }
    if ($res_arr[0] && $res_arr[1] && !are_friends($res_arr[0], $res_arr[1])) {
      return false;
    }
    return implode(':', $res_arr);
  }

  /**
   * Dummy helper function for sample implementation that fetches
   * all rows where $id is one of the friends. The parameter $pos
   * indicates whether we are looking for $id to be uid1 or uid2
   * ($pos has a value of either 0 or 1), so that the id returned
   * is of the correct format.
   * Fills in the appropriate field in $result (passed by reference)
   *
   * Enforces the restriction that you can only request all friends of
   * a user if that is the currently logged in user.
   *
   * @param  $id1 first user
   * @param  $id2 second user
   * @return boolean, true if $id1 and $id2 are friends
   */
  private function get_all_friends($id, $pos, &$result) {
    if ($this->user != $id) {
      throw new NoIndexFunctionException("Can't lookup all friends of $id; only for the logged in user(" . $this->user . ") or for pairs of users.");
    }
    $friend_arr = user_get_all_friends($id);
    foreach ($friend_arr as $friend) {
      if ($pos == 0) {
        $result["$id:$friend"] = 1;
      } else {
        $result["$friend:$id"] = 1;
      }
    }
  }

  /**
   * Implementation to fetch a list of ids that match
   * the given queries for the table. The list of ids returned
   * still have the form uid1:uid2, but these must have both
   * values filled in instead of allowing, for instance, ':3'.
   *
   * This enforces the restriction that none of the queries can
   * be ':', since this would match all rows.
   */
  public function get_ids_for_queries($queries, $cap) {
    $result = array();
    foreach ($queries as $query => $true) {
      if (count($result) >= $cap && $query != ':') {
        continue;
      }
      $query_arr = explode(':', $query);
      if ($query_arr[0]) {
        if ($query_arr[1]) {
          $result[$query] = 1;
        } else {
          $this->get_all_friends($query_arr[0], 0, $result);
        }
      } else if ($query_arr[1]) {
        $this->get_all_friends($query_arr[1], 1, $result);
      } else {
        throw new NoIndexFunctionException();
      }
    }
    return $result;
  }

  public function get_name() {
    return 'friend_info';
  }
}
