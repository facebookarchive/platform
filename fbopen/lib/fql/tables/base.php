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
 * Abstract interface that represents a table that can be queried through FQL.
 */
interface FQLTable {
  /**
   * Gets the list of all of the fields in this table.
   *
   * @return associative array mapping the names of the fields to the corresponding
   *         class names of the FQLField implementation that defines this field.
   */
  public static function get_fields();

  /**
   * Construct the table.
   *
   * @param $user   the currently logged in user.
   * @param $app_id the id of the application, if accessed via the API.
   * @param $name   the name of the table
   */
  public function __construct($user, $app_id, $name);

  /**
   * Gets all of the ids matching a given set of queries.  For many tables, the
   * queries may implicitly already contain the ids, but other tables may do
   * some lookups in this function get the full set of ids.
   *
   * @param  $queries an associative array whose keys are the queries to look up.
   *                  the format for these queries is specific to each table.
   * @param  $cap     the number of ids to limit the number of results to
   * @return an array of ids matching the given queries.
   * @throws NoIndexFunctionException
   */
  public function get_ids_for_queries($queries, $cap);

  /**
   * Given two queries, finds the intersection of them.
   *
   * @param  $query1 one of the queries to intersect, in the table-specific format
   * @param  $query2 the other query to intersect, in the table-specific format
   * @return the resulting query, or false if there is no query that matches.
   */
  public function intersect_queries($query1, $query2);

  /**
   * Prime the cache for the given ids.  Sets up any general caching needed by
   * all fields in the table.
   *
   * @param $ids the set of ids to prepare for
   */
  public function prime_cache($ids);

  /**
   * Check if the logged in user can see the specified id.  Does not care about
   * specific requirements to see individual fields, only does table-wide checks.
   *
   * @param $id   the id the user is attempting to view.
   */
  public function can_see($id);

  /**
   * Get the name of the object type that implements this table.  e.g. "user_profile"
   *
   * @return string name
   */
  public function get_name();
}

/**
 * This class covers the basics of a table.
 * You'll need to implement the following functions:
 *   public function get_fields();
 *   public function can_see($id);
 *   public function prime_cache($ids);
 *
 * This implementation assumes that the table is designed where ids and
 * queries are identical except that the query ":" corresponds to all ids.
 * Override the following functions if this is not the case:
 *   public function intersect_queries($query1, $query2);
 *   public function get_ids_for_queries($queries, $cap);
 */
abstract class _FQLBaseTable implements FQLTable {
  protected $user;
  protected $app_id;
  protected $name;
  public function __construct($user, $app_id, $name) {
    $this->user = $user;
    $this->app_id = $app_id;
    $this->name = $name;
  }

  /*
   * Intersects queries where a query is either an id, or ':' to
   * correspond to a wildcard, matching all ids.
   *
   * intersect_queries('3', '4') => false
   * intersect_queries(':', ':') => ':'
   * intersect_queries(':', '17') => '17'
   * intersect_queries('5', '5') => '5'
   */
  public function intersect_queries($query1, $query2) {
    if ($query1 != ':') {
      if ($query2 != ':' && $query1 != $query2) {
        return false;
      }
      return $query1;
    } else {
      return $query2;
    }
  }

  /**
   * This helper is used by tables that accept two-parameter id1:id2 format
   * queries.
   *
   * intersect_dual_queries('3:', ':4') => '3:4'
   * intersect_dual_queries(':', '17:2') => '17:2'
   * intersect_dual_queries(':', '17:') => '17:
   * intersect_dual_queries('3:', '5:') => false
   */
  protected function intersect_dual_queries($query1, $query2) {
    $q1_arr = explode(':', $query1);
    $q2_arr = explode(':', $query2);

    // result array
    $res_arr = array();
    if ($q1_arr[0]) {
      if ($q2_arr[0] && $q1_arr[0] != $q2_arr[0]) {
        // $query1 and $query2 do not match in id1
        return false;
      }
      $res_arr[0] = $q1_arr[0];
    } else {
      $res_arr[0] = $q2_arr[0];
    }
    if ($q1_arr[1]) {
      if ($q2_arr[1] && $q1_arr[1] != $q2_arr[1]) {
        // $query1 and $query2 do not match in id2
        return false;
      }
      $res_arr[1] = $q1_arr[1];
    } else {
      $res_arr[1] = $q2_arr[1];
    }
    return implode(':', $res_arr);
  }

  /**
   * Translates a list of queries into a list of ids (capped at $cap)
   * that would match those queries. In this simplistic base version of
   * a table, the queries themselves are ids already (e.g. 402, 32545)
   * so this is basically a no-op except to throw an exception when it
   * encounters ':', which matches everything.
   *
   * @param  $queries array of queries (where the keys are the queries)
   *                  that we want to find matchind ids for
   * @param  $cap     maximum number of ids to return
   * @return array of ids (where the keys are the ids) that match
   *         the given queries
   *
   * Throws NoIndexFunctionException when it encounters a query that
   * matches all ids.
   */
  public function get_ids_for_queries($queries, $cap) {
    foreach ($queries as $query=>$true) {
      if ($query == ':') {
        throw new NoIndexFunctionException();
      }
    }
    return $queries;
  }

  public function get_name() {
    return $this->name;
  }

  /**
   * Useful helper function for matching queries.  Find the match of
   * two strings where '' is treated as a "wildcard".
   *
   * @param  $a  first string
   * @param  $b  second string
   * @return false if $a and $b don't match, or the matching value if they do match.
   */
  protected static function match($a, $b) {
    if (!$a) {
      return $b;
    }
    if (!$b) {
      return $a;
    }
    if ($a == $b) {
      return $a;
    }
    return false;
  }
}
