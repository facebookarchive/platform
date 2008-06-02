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
 * FQLField is the abstract interface that represents a table column that can be
 * referenced in an FQL query.
 */
interface FQLField {

  /**
   * Construct an FQLField.
   *
   * @param $user    the logged in user
   * @param $app_id  the current application id, if running via the api.
   * @param $table   the table this field belongs to
   * @param $name    the name of this field
   */
  public function __construct($user, $app_id, $table, $name);

  /**
   * Get the value of this field for the given id.
   *
   * @param  $id   id for the object to get the field value for
   * @return the value, type is determined by the field
   */
  public function evaluate($id);

  /**
   * Do any specific can_see checks needed for this field.  Note that the table
   * will also do general can_see checks, so this should only do checks specific
   * to this field.
   *
   * @param  $id   the id of the object attempting to be viewed
   * @return boolean, true if no field-specific restrictions prevent the user
   *         from seeing data for this field for $id, false otherwise
   */
  public function can_see($id);

  /**
   * Do any specific cache priming needed for this field.  Note that the table
   * will prime basic info for each id, so this should only prime things
   * specific to this field.
   *
   * @param $ids the set of ids to prime the cache for. format depends on the table.
   */
  public function prime_cache($ids);

  /**
   * Get the query that results in (at least) all of the ids
   * whose value for this field matches the specified value.
   *
   * @param  $value  the value that this field must match.
   * @return string representing the query for this lookup
   *         (in format specific to the table)
   */
  public function get_query($value);

  /**
   * Get a description of this field, just for documentation purposes.
   *
   * @return a string describing what this field is used for.
   */
  public static function get_description();
}

abstract class _FQLBaseField implements FQLField {
  protected $user;
  protected $app_id;
  protected $table;
  protected $name;
  public function __construct($user, $app_id, $table, $name) {
    $this->user = $user;
    $this->app_id = $app_id;
    $this->table = $table;
    $this->name = $name;
  }
  public function prime_cache($ids) {
  }
  public function can_see($id) {
    return true;
  }

  // returns ':' which acts as a wildcard, matching all ids
  public function get_query($data) {
    return ':';
  }
  public static function get_description() {
    return $this->name;
  }
}
