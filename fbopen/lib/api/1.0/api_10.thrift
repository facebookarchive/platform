#!/usr/local/bin/thrift -php -rest -xsd

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

xsd_namespace "http://api.facebook.com/1.0/"
php_namespace api10
java_package com.facebook.api

typedef i32 nid
typedef i32 hsid
typedef i32 uid
typedef i64 flid
typedef i64 id


typedef string uid_list
typedef string field_list

typedef i32 time
typedef string date
typedef i32 year

typedef string auth_token
typedef string session_key

typedef string friend_link
typedef string member_type

typedef string json_string /* Just so we can be explicit */

struct user_status {
  1: string message,
  2: time time
}

enum FacebookApiErrorCode {
  /*
   * NOT AN ERROR
   */
  API_EC_SUCCESS = 0,

  /*
   * GENERAL ERRORS
   */
  API_EC_UNKNOWN = 1,
  API_EC_SERVICE = 2,
  API_EC_METHOD = 3,
  API_EC_TOO_MANY_CALLS = 4,
  API_EC_BAD_IP = 5,
  /*
   * PARAMETER ERRORS
   */
  API_EC_PARAM = 100,
  API_EC_PARAM_API_KEY = 101,
  API_EC_PARAM_SESSION_KEY = 102,
  API_EC_PARAM_CALL_ID = 103,
  API_EC_PARAM_SIGNATURE = 104,
  API_EC_PARAM_TOO_MANY = 105,
  API_EC_PARAM_USER_ID = 110,
  API_EC_PARAM_USER_FIELD = 111,
  API_EC_PARAM_SOCIAL_FIELD = 112,
  API_EC_PARAM_SUBCATEGORY = 141,
  API_EC_PARAM_TITLE = 142,
  API_EC_PARAM_BAD_JSON = 144,

  /*
   * USER PERMISSIONS ERRORS
   * FBOPEN:NOTE - Many of these are not used in the open source implementation.
   *            - They are provided for reference.
   */
  API_EC_PERMISSION = 200,
  API_EC_PERMISSION_USER = 210,
  API_EC_PERMISSION_ALBUM = 220,
  API_EC_PERMISSION_PHOTO = 221,
  API_EC_PERMISSION_MESSAGE = 230,
  API_EC_PERMISSION_MARKUP_OTHER_USER = 240,
  API_EC_PERMISSION_STATUS_UPDATE = 250,

  /*
   * FQL ERRORS
   */
  FQL_EC_UNKNOWN_ERROR = 600,
  FQL_EC_PARSER_ERROR = 601,
  FQL_EC_UNKNOWN_FIELD = 602,
  FQL_EC_UNKNOWN_TABLE = 603,
  FQL_EC_NO_INDEX = 604,

  /*
   * PLATFORM ERRORS
   */
  API_EC_REF_SET_FAILED = 700,

  /*
   * DATA STORE API ERRORS
   */
  API_EC_DATA_UNKNOWN_ERROR = 800;
  API_EC_DATA_INVALID_OPERATION = 801;
  API_EC_DATA_QUOTA_EXCEEDED = 802;
  API_EC_DATA_OBJECT_NOT_FOUND = 803;
  API_EC_DATA_OBJECT_ALREADY_EXISTS = 804;
  API_EC_DATA_DATABASE_ERROR = 805;


  /*
   * APPLICATION INFO ERRORS
   */
  API_EC_NO_SUCH_APP = 900;

  /*
   * BATCH API ERRORS
   */
  API_EC_BATCH_TOO_MANY_ITEMS = 950,

}

/**
 * Exceptions oh no!
 */
struct arg {
  1: string key,
  2: string value
}

exception FacebookApiException {
  1: FacebookApiErrorCode error_code,
  2: string error_msg,
  3: list<arg> request_args
}

/**
 * Api session
 */
struct session_info {
  1: session_key session_key,
  2: uid uid,
  3: i32 expires
  4: string secret xsd_optional
}

/**
 * Location
 */
struct location {
  1: string street xsd_optional,
  2: string city,
  3: string state,
  4: string country,
  5: string zip xsd_optional,
  6: double latitude xsd_optional,
  7: double longitude xsd_optional,
}


/**
 * Network affiliation
 */
struct affiliation {
  1: nid nid,
  2: string name,
  3: string type,
  4: string status,
  5: year year
}

/**
 * High School info
 */
struct hs_info {
  1: string hs1_name,
  2: string hs2_name,
  3: year grad_year,
  4: hsid hs1_id,
  5: hsid hs2_id
}

/**
 * School info
 */
typedef string concentration
struct education_info {
  1: string name,
  2: year year,
  3: list<concentration> concentrations,
  4: string degree
}

/**
 * Work info
 */
struct work_info {
  1: location location,
  2: string company_name,
  3: string position,
  4: string description,
  5: date start_date,
  6: date end_date
}

/**
 * Profile
 */
typedef string sex
typedef string seeking
struct user xsd_all {
  1:  string about_me xsd_nillable,
  2:  string activities xsd_nillable,
  3:  list<affiliation> affiliations,
  4:  string birthday xsd_nillable,
  5:  string books xsd_nillable,
  6:  location current_location xsd_nillable,
  7:  list<education_info> education_history xsd_nillable,
  8:  string first_name,
  9:  location hometown_location xsd_nillable,
  10: hs_info hs_info xsd_nillable,
  11: string interests xsd_nillable,
  12: bool is_app_user,
  13: string last_name,
  14: list<seeking> meeting_for xsd_nillable,
  15: list<sex> meeting_sex xsd_nillable,
  16: string movies xsd_nillable,
  17: string music xsd_nillable,
  18: string name,
  19: i32 notes_count xsd_nillable,
  20: string pic xsd_nillable,
  21: string pic_big xsd_nillable,
  22: string pic_small xsd_nillable,
  23: string political xsd_nillable,
  24: time profile_update_time xsd_nillable,
  25: string quotes xsd_nillable,
  26: string relationship_status xsd_nillable,
  27: string religion xsd_nillable,
  28: sex sex xsd_nillable,
  29: uid significant_other_id xsd_nillable,
  30: user_status status xsd_nillable,
  31: double timezone xsd_nillable,
  32: string tv xsd_nillable,
  33: uid uid,
  34: i32 wall_count xsd_nillable,
  35: list<work_info> work_history xsd_nillable,
  36: string pic_square xsd_nillable,
  37: bool has_added_app xsd_nillable,
}

/**
 * Friend link status
 */
struct friend_info xsd_all
{
  1: uid uid1,
  2: uid uid2,
  3: bool are_friends xsd_nillable,
}

struct friendlist {
  1: flid flid,
  2: string name,
  3: uid owner xsd_optional
}

/**
* Application Public Info
*/
struct developer_info {
  1: i32 uid;
  2: string name;
}

struct app_info {
  1: i64 app_id;
  2: string api_key;
  3: string canvas_name;
  4: string display_name;
  5: string icon_url;
  6: string logo_url;
  7: list<developer_info> developers;
  8: string company_name;
}

/**
 * The main Facebook API service definition
 */
service FacebookApi10 {

  /**
   * Authentication
   */
  auth_token auth_createToken()
    throws (1:FacebookApiException error_response),
  session_info auth_getSession(1:string auth_token)
    throws (1:FacebookApiException error_response),

  /**
   * Feed
   */
  bool feed_publishStoryToUser(1:string title, 2:string body,
                               3:string image_1, 4:string image_1_link,
                               5:string image_2, 6:string image_2_link,
                               7:string image_3, 8:string image_3_link,
                               9:string image_4, 10:string image_4_link)
    throws (1:FacebookApiException error_response),

  bool feed_publishActionOfUser(1:string title, 2:string body,
                                3:string image_1, 4:string image_1_link,
                                5:string image_2, 6:string image_2_link,
                                7:string image_3, 8:string image_3_link,
                                9:string image_4, 10:string image_4_link)
    throws (1:FacebookApiException error_response),

  /**
   * Friends
   */
  list<friend_info> friends_areFriends(1:uid_list uids1, 2:uid_list uids2)
    throws (1:FacebookApiException error_response),
  list<uid> friends_get(1:uid uid, 2:flid flid)
    throws (1:FacebookApiException error_response),
  list<uid> friends_getAppUsers()
    throws (1:FacebookApiException error_response),
  list<friendlist> friends_getLists()
    throws (1:FacebookApiException error_response),

  /**
   * Profile
   */
  bool profile_setFBML(1:string markup, 2:id uid, 3:string profile, 4:string profile_action, 5:string mobile_profile)
    throws (1:FacebookApiException error_response),

  string profile_getFBML(1:id uid)
    throws (1:FacebookApiException error_response),

  /**
   * Users
   */
  list<user> users_getInfo(1:uid_list uids, 2:field_list fields)
    throws (1:FacebookApiException error_response),

  bool users_isAppAdded()
    throws (1:FacebookApiException error_response),

  uid users_getLoggedInUser()
    throws (1:FacebookApiException error_response),

  /**
   * Get properties for an app
   *
   * @param  properties  a list of properties to look up
   * @return             a json list of the current values for those properties
   */
  string admin_getAppProperties(1:list<string> properties)
    throws (1:FacebookApiException error_response),
  
  /**
   * Set properties for an app
   *
   * @param  properties  a key val list of properties to set
   * @return             whether the set was successful
   */
  bool admin_setAppProperties(1:string properties)
    throws (1:FacebookApiException error_response),

  list<app_info> application_getPublicInfo(1:i64 application_id,
    2:string application_api_key,
    3:string application_canvas_name)
    throws (1:FacebookApiException error_response),

 /**
   * FQL - note: response is not XSD-typeable
   */
  void fql_query(1:string query)
    throws (1:FacebookApiException error_response),

  /**
   * Execute a series of method in parallel and return all the results together
   * Note - result is not XSD-typeable
   * @param  method_feed     feed of method
   * @return             feed of results
   */
  list<string> batch_run(1:list<string> method_feed, 2:bool serial_only)
    throws (1:FacebookApiException error_response),


}
