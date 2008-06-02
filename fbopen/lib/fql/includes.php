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

include_once $_SERVER['PHP_ROOT'].'/lib/thrift/packages/api_10/api_10_types.php'; // for FacebookApiException
include_once $_SERVER['PHP_ROOT'].'/lib/thrift/packages/api_10/api_10_xsd.php'; 


include_once $_SERVER['PHP_ROOT'].'/lib/api/api_xml.php';           // for xml_element
include_once $_SERVER['PHP_ROOT'].'/lib/fql/exception.php';

include_once $_SERVER['PHP_ROOT'].'/lib/fql/expressions/base.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/expressions/comparison.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/expressions/arithmetic.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/expressions/conjunction.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/expressions/disjunction.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/expressions/in.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/expressions/function.php';

include_once $_SERVER['PHP_ROOT'].'/lib/fql/tables/base.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/tables/users.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/tables/friends.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/tables/friendlist.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/tables/friendlist_member.php';

include_once $_SERVER['PHP_ROOT'].'/lib/fql/fields/base.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/fields/user.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/fields/friend.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/fields/friendlist.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/fields/friendlist_member.php';

include_once $_SERVER['PHP_ROOT'].'/lib/fql/statement.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/tokenizer.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fql/parser.php';
