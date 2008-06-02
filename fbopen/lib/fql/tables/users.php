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

include_once $_SERVER['PHP_ROOT'].'/lib/profile.php';

/**
 * Sample class implementation for a user table.
 *
 * The only indexable columns are uid and name.
 */
class FQLUserTable extends _FQLBaseTable {
  public static function get_fields() {
    return array(
      'uid'                  => 'FQLUserId',
      'first_name'           => 'FQLUserFirstName',
      'last_name'            => 'FQLUserLastName',
      'name'                 => 'FQLUserName',
      'pic_small'            => 'FQLUserPicThumb',
      'pic_big'              => 'FQLUserPicNorm',
      'pic_square'           => 'FQLUserPicSquare',
      'pic'                  => 'FQLUserPicSmall',
      'affiliations'         => 'FQLUserAffiliations',
      'profile_url'          => 'FQLUserProfileUrl',
      'profile_update_time'  => 'FQLUserProfileUpdateTime',
      'timezone'             => 'FQLUserTimezone',
      'religion'             => 'FQLUserReligion',
      'birthday'             => 'FQLUserBirthday',
      'sex'                  => 'FQLUserGender',
      'hometown_location'    => 'FQLUserHometownLocation',
      'meeting_sex'          => 'FQLUserMeetingSex',
      'meeting_for'          => 'FQLUserMeetingFor',
      'relationship_status'  => 'FQLUserRelationshipStatus',
      'significant_other_id' => 'FQLUserSignificantOtherId',
      'political'            => 'FQLUserPolitical',
      'current_location'     => 'FQLUserCurrentLocation',
      'activities'           => 'FQLUserClub',
      'interests'            => 'FQLUserInterests',
      'music'                => 'FQLUserMusic',
      'tv'                   => 'FQLUserTv',
      'movies'               => 'FQLUserMovies',
      'books'                => 'FQLUserBooks',
      'quotes'               => 'FQLUserQuotes',
      'about_me'             => 'FQLUserAboutMe',
      'hs_info'              => 'FQLUserHsInfo',
      'education_history'    => 'FQLUserSchoolInfo',
      'work_history'         => 'FQLUserWorkHistory',
      'notes_count'          => 'FQLUserNotesCount',
      'wall_count'           => 'FQLUserWallCount',
      'status'               => 'FQLUserStatus',
      'is_app_user'          => 'FQLUserAppUser',
      'has_added_app'        => 'FQLUserInstalledUser',
    );
  }

  /**
   * Constructor: only allows construction if $user is set.
   */
  public function __construct($user, $app_id, $name) {
    if ($user === null) {
      throw new UnknownTableException($name);
    }

    parent::__construct($user, $app_id, $name);
  }

  public function intersect_queries($query1, $query2) {
    $q1_arr = explode(':', $query1);
    $q2_arr = explode(':', $query2);
    $res_arr = array();
    for ($i = 0; $i < 2; $i++) {
      if (($res_arr[$i] = self::match($q1_arr[$i], $q2_arr[$i])) === false) {
        return false;
      }
    }

    return implode(':', $res_arr);
  }

  public function get_ids_for_queries($queries, $cap) {
    $result = array();
    foreach ($queries as $query=>$true) {
      if (count($result) >= $cap && $query != ':') {
        // we're done, but we still want to throw the NoIndexFunctionException if necessary
        continue;
      }
      $query_arr = explode(':', $query);
      // just use id if present

      if ($query_arr[0]) {
        $result[$query_arr[0]] = true;
      } else if ($query_arr[1]) {
        // indexing by name
        $user_ids = get_users_by_name($query_arr[1]);
        foreach ($user_ids as $user_id) {
          $result[$user_id] = true;
        }
      } else {
        throw new NoIndexFunctionException();
      }
    }

    return $result;
  }

  /**
   * Dummy privacy function that enforces privacy restrictions:
   * users can see data about themselves or their friends.
   */
  public function can_see($id) {
    if ($this->user == $id) {
      return true;
    }
    if ($this->user) {
      return are_friends($this->user, $id);
    }
    return false;
  }

  public function prime_cache($ids) {
  }

}
