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

class FQLUserId extends _FQLBaseField {
  public function evaluate($id) {
    return $id;
  }

  // implements get_query, which means it's an indexable field
  // returns the id as the query, unless id is negative
  public function get_query($data) {
    $data = (int)$data;
    if ($data <= 0) return null;
    return "$data:";
  }
}

abstract class _FQLDummyUserField extends _FQLBaseField {
  public function evaluate($id) {
    $info = user_get_info($id);
    if ($info && isset($info[$this->name])) {
      return $info[$this->name];
    }
    return null;
  }
}

class FQLUserName extends _FQLDummyUserField {
  public function get_query($data) {
    return ":$data";
  }
}
class FQLUserFirstName extends _FQLDummyUserField {
}
class FQLUserLastName extends _FQLDummyUserField {
}
class FQLUserPicThumb extends _FQLDummyUserField {
}
class FQLUserPicNorm extends _FQLDummyUserField {
}
class FQLUserPicSquare extends _FQLDummyUserField {
}
class FQLUserPicSmall extends _FQLDummyUserField {
}
class FQLUserProfileUrl extends _FQLDummyUserField {
}
class FQLUserProfileUpdateTime extends _FQLDummyUserField {
}
class FQLUserTimezone extends _FQLDummyUserField {
}
class FQLUserReligion extends _FQLDummyUserField {
}
class FQLUserBirthday extends _FQLDummyUserField {
}
class FQLUserGender extends _FQLDummyUserField {
}
class FQLUserMeetingSex extends _FQLDummyUserField {
}
class FQLUserMeetingFor extends _FQLDummyUserField {
}
class FQLUserRelationshipStatus extends _FQLDummyUserField {
}
class FQLUserSignificantOtherId extends _FQLDummyUserField {
}
class FQLUserPolitical extends _FQLDummyUserField {
}
class FQLUserClub extends _FQLDummyUserField {
}
class FQLUserInterests extends _FQLDummyUserField {
}
class FQLUserMusic extends _FQLDummyUserField {
}
class FQLUserTv extends _FQLDummyUserField {
}
class FQLUserMovies extends _FQLDummyUserField {
}
class FQLUserBooks extends _FQLDummyUserField {
}
class FQLUserQuotes extends _FQLDummyUserField {
}
class FQLUserAboutMe extends _FQLDummyUserField {
}
class FQLUserNotesCount extends _FQLDummyUserField {
}
class FQLUserWallCount extends _FQLDummyUserField {
}
class FQLUserStatus extends _FQLDummyUserField {
}

class FQLUserHsInfo extends _FQLBaseField {
  public function evaluate($id) {
    $info = user_get_info($id);
    if ($info && isset($info['hs_info'])) {
      $hs_info = new api10_hs_info($info['hs_info']);
    } else {
      $hs_info = new api10_hs_info();
    }
    return $hs_info;
  }
}

class FQLUserAppUser extends _FQLDummyUserField {
    public function evaluate($id) {
          return is_platform_app_authorized($this->app_id, $id);
    }
}

class FQLUserInstalledUser extends _FQLDummyUserField {
  public function evaluate($id) {
    return is_platform_app_installed($this->app_id, $id);
  }
}

class FQLUserAffiliations extends _FQLBaseField {
  public function evaluate($id) {
    $info = user_get_info($id);
    $affils = array();
    if ($info && isset($info['affiliations'])) {
      foreach ($info['affiliations'] as $data) {
        $affils[] = new api10_affiliation($data);
      }
    }
    return $affils;
  }
}

class FQLUserHometownLocation extends _FQLBaseField {
  public function evaluate($id) {
    $info = user_get_info($id);
    if ($info && isset($info['hometown_location'])) {
      $location = new api10_location($info['hometown_location']);
    } else {
      $location = new api10_location();
    }
    return $location;
  }
}

class FQLUserCurrentLocation extends _FQLBaseField {
  public function evaluate($id) {
    $info = user_get_info($id);
    if ($info && isset($info['current_location'])) {
      $location = new api10_location($info['current_location']);
    } else {
      $location = new api10_location();
    }
    return $location;
  }
}

class FQLUserWorkHistory extends _FQLBaseField {
  public function evaluate($id) {
    $info = user_get_info($id);
    $jobs = array();
    if ($info && isset($info['work_history'])) {
      foreach ($info['work_history'] as $work) {
        $job = new api10_work_info();
        $location = new api10_location($location);
        $location->city =  $work['city'];
        $location->state =  $work['state'];
        $location->country = $work['country'];
        $job->company_name = $work['company_name'];
        $job->position = $work['position'];
        $job->description = $work['description'];
        $job->start_date = $work['start_date'];
        $job->end_date = $work['end_date'];
        $jobs[] = $job;
      }
    }
    return $jobs;
  }
}

class FQLUserSchoolInfo extends _FQLBaseField {
  public function evaluate($id) {
    $info = user_get_info($id);
    $schools = array();
    if ($info && isset($info['education_history'])) {
      foreach ($info['education_history'] as $school) {
          $school_info = new api10_education_info();
          $school_info->name = $school['school_name'];
          $school_info->year = $school['year'];
          $school_info->degree = $school['degree'];
          $concentrations = array();
          foreach (array(1,2,3) as $conc_num) {
            $curr_conc = $school['concentration' . $conc_num];
            if ($curr_conc) {
              $concentrations[] = $curr_conc;
            }
          }
        $school_info->concentrations = $concentrations;
        $schools[] = $school_info;
      }
    }
    return $schools;
  }
}
