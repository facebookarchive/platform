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

// FBOPEN:NOTE - All errors used in this open source released are referenced
// here, but they are not the complete set of errors found on developers.facebook.com.
// Additional error codes are provided which are not in use here, for completeness.
// If the developer is goingto add his own error codes, to retain compatibility with
// Facebook, you may wish to begin your error codes at 10000 and above, at least
// until a better "namespacing" story is devised.

/**
 * Error codes and descriptions for the Facebook API.
 *
 * Version:
 * Last Modified:
 */

define('API_EC_SUCCESS', 0);

/*
 * GENERAL ERRORS
 */
define('API_EC_UNKNOWN', 1);
define('API_EC_SERVICE', 2);
define('API_EC_METHOD', 3);
define('API_EC_TOO_MANY_CALLS', 4);
define('API_EC_BAD_IP', 5);

/*
 * PARAMETER ERRORS
 */
define('API_EC_PARAM', 100);
define('API_EC_PARAM_API_KEY', 101);
define('API_EC_PARAM_SESSION_KEY', 102);
define('API_EC_PARAM_CALL_ID', 103);
define('API_EC_PARAM_SIGNATURE', 104);
define('API_EC_PARAM_TOO_MANY', 105);
define('API_EC_PARAM_USER_ID', 110);
define('API_EC_PARAM_USER_FIELD', 111);
define('API_EC_PARAM_SOCIAL_FIELD', 112);
define('API_EC_PARAM_SUBCATEGORY', 141);
define('API_EC_PARAM_TITLE', 142);
define('API_EC_PARAM_BAD_JSON', 144);

/*
 * USER PERMISSIONS ERRORS
 */
define('API_EC_PERMISSION', 200);
define('API_EC_PERMISSION_USER', 210);
define('API_EC_PERMISSION_ALBUM', 220);
define('API_EC_PERMISSION_PHOTO', 221);
define('API_EC_PERMISSION_MESSAGE', 230);
define('API_EC_PERMISSION_MARKUP_OTHER_USER', 240);
define('API_EC_PERMISSION_STATUS_UPDATE', 250);

/*
 * FQL ERRORS
 */
define('FQL_EC_UNKNOWN_ERROR', 600); // should never happen
define('FQL_EC_PARSER_ERROR', 601);
define('FQL_EC_UNKNOWN_FIELD', 602);
define('FQL_EC_UNKNOWN_TABLE', 603);
define('FQL_EC_NO_INDEX', 604);
define('FQL_EC_UNKNOWN_FUNCTION', 605);
define('FQL_EC_INVALID_PARAM', 606);

/**
 * Ref stuff
 */
define('API_EC_REF_SET_FAILED', 700);

/**
 * DATA STORE API ERRORS
 */
define('API_EC_DATA_UNKNOWN_ERROR', 800); // should never happen
define('API_EC_DATA_INVALID_OPERATION', 801);
define('API_EC_DATA_QUOTA_EXCEEDED', 802);
define('API_EC_DATA_OBJECT_NOT_FOUND', 803);
define('API_EC_DATA_OBJECT_ALREADY_EXISTS', 804);
define('API_EC_DATA_DATABASE_ERROR', 805);


/*
 * APPLICATION INFO ERRORS
 */
define('API_EC_NO_SUCH_APP', 900);

/*
 * BATCH API ERRORS
 */
define('API_EC_BATCH_TOO_MANY_ITEMS', 950);


$api_error_descriptions = array(API_EC_SUCCESS             => 'Success',
                                API_EC_UNKNOWN             => 'An unknown error occurred',
                                API_EC_SERVICE             => 'Service temporarily unavailable',
                                API_EC_METHOD              => 'Unknown method',
                                API_EC_TOO_MANY_CALLS      => 'Application request limit reached',
                                API_EC_BAD_IP              => 'Unauthorized source IP address',
                                API_EC_PARAM               => 'Invalid parameter',
                                API_EC_PARAM_API_KEY       => 'Invalid API key',
                                API_EC_PARAM_SESSION_KEY   => 'Session key invalid or no longer valid',
                                API_EC_PARAM_CALL_ID       => 'Call_id must be greater than previous',
                                API_EC_PARAM_SIGNATURE     => 'Incorrect signature',
                                API_EC_PARAM_TOO_MANY      => 'The number of parameters exceeded the maximum for this operation',
                                API_EC_PARAM_USER_ID       => 'Invalid user id',
                                API_EC_PARAM_USER_FIELD    => 'Invalid user info field',
                                API_EC_PARAM_SOCIAL_FIELD  => 'Invalid user field',
                                API_EC_PARAM_SUBCATEGORY   => 'Invalid subcategory',
                                API_EC_PARAM_TITLE         => 'Invalid title',
                                API_EC_PARAM_BAD_JSON      => 'Malformed JSON string',
                                API_EC_PERMISSION          => 'Permissions error',
                                API_EC_PERMISSION_USER     => 'User not visible',
                                API_EC_PERMISSION_ALBUM    => 'Album or albums not visible',
                                API_EC_PERMISSION_PHOTO    => 'Photo not visible',
                                API_EC_PERMISSION_MESSAGE  => 'Permissions disallow message to user',
                                API_EC_PERMISSION_MARKUP_OTHER_USER => 'Desktop applications cannot set FBML for other users',
                                API_EC_PERMISSION_STATUS_UPDATE => 'Updating status requires the extended permission status_update',





                                // these descriptions don't actually get used...see lib/fql/exception.php
                                FQL_EC_UNKNOWN_ERROR       => 'An unknown error occurred in FQL',
                                FQL_EC_PARSER_ERROR        => 'Error while parsing FQL statement',
                                FQL_EC_UNKNOWN_FIELD       => 'The field you requested does not exist',
                                FQL_EC_UNKNOWN_TABLE       => 'The table you requested does not exist',
                                FQL_EC_NO_INDEX            => 'Your statement is not indexable',
                                FQL_EC_UNKNOWN_FUNCTION    => 'The function you called down not exist',
                                FQL_EC_INVALID_PARAM       => 'Wrong number of arguments passed into the function',


                                API_EC_DATA_UNKNOWN_ERROR => 'Unknown data store API error',
                                API_EC_DATA_INVALID_OPERATION => 'Invalid operation',
                                API_EC_DATA_QUOTA_EXCEEDED => 'Data store allowable quota was exceeded',
                                API_EC_DATA_OBJECT_NOT_FOUND => 'Specified object cannot be found',
                                API_EC_DATA_OBJECT_ALREADY_EXISTS => 'Specified object already exists',
                                API_EC_DATA_DATABASE_ERROR => 'A database error occurred. Please try again',
                                API_EC_REF_SET_FAILED => 'Unknown failure in storing ref data. Please try again.',

                                API_EC_NO_SUCH_APP => 'No such application exists.',

                                API_EC_BATCH_TOO_MANY_ITEMS => 'Each batch API can not contain more than 20 items',
                               );

