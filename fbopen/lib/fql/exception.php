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
 * Superclass for all FQL exception types that can be thrown
 * while processing an FQL query.
 */
class FacebookFQLException extends api10_FacebookApiException {
}

/**
 * Exception thrown when an error is encountered while parsing
 * the FQL query.
 */
class ParserErrorException extends FacebookFQLException {
  public function __construct($str) {
    $vals = array('error_code' => FQL_EC_PARSER_ERROR,
                  'error_msg'  => 'Parser error: ' . $str);
    parent::__construct($vals);
  }
}

/**
 * Exception thrown when encountering a query with "SELECT *"
 * which is not supported.
 */
class SelectStarException extends ParserErrorException {
  public function __construct() {
    parent::__construct('SELECT * is not supported.  Please manually list the columns you are interested in.');
  }
}

/**
 * Exception thrown when a query tries to access a field name that is not a
 * member of the source table.
 */
class UnknownFieldException extends FacebookFQLException {
  public function __construct($field, $table, $table_type) {
    $vals = array('error_code' => FQL_EC_UNKNOWN_FIELD,
                  'error_msg'  => "$field is not a member of the $table $table_type.");
    parent::__construct($vals);
  }
}

/**
 * Exception thrown when a query tries to access a table name that does not
 * exist.
 */
class UnknownTableException extends FacebookFQLException {
  public function __construct($table) {
    $vals = array('error_code' => FQL_EC_UNKNOWN_TABLE,
                  'error_msg'  => 'Unknown table: '.$table);
    parent::__construct($vals);
  }
}

/**
 * Exception thrown when a query's WHERE clause does not contain one of the
 * index columns for that table.
 */
class NoIndexFunctionException extends FacebookFQLException {
  public function __construct($txt=null) {
    if (!$txt) {
      $txt = 'Your statement is not indexable - the WHERE clause must contain one of the columns marked with a star in http://developers.facebook.com/documentation.php?doc=fql';
    }
    $vals = array('error_code' => FQL_EC_NO_INDEX, 'error_msg'  => $txt);
    parent::__construct($vals);
  }
}

/**
 * Exception thrown when a query contains a function name not contained
 * in the list of supported functions (e.g. strlen, lower, rand, etc)
 */
class UnknownFunctionException extends FacebookFQLException {
  public function __construct($fn) {
    $vals = array('error_code' => FQL_EC_UNKNOWN_FUNCTION,
                  'error_msg'  => $fn . ' is not a valid function name.');
    parent::__construct($vals);
  }
}

/**
 * Exception thrown when a query passes invalid arguments to a function
 * in the query (e.g. strlen, rand, etc.)
 */
class InvalidParamException extends FacebookFQLException {
  public function __construct($fn, $desc) {
    $msg  = empty($desc) ? ' was given invalid parameter(s)' : $desc;
    $vals = array('error_code' => FQL_EC_INVALID_PARAM,
                  'error_msg'  => $fn . ' function ' . $msg . '.');
    parent::__construct($vals);
  }
}

/**
 * Exception thrown when the wrong number of arguments is supplied
 * to a function called in the query (e.g. strlen, rand, etc.)
 */
class WrongParamNumException extends InvalidParamException {
  public function __construct($fn, $expected, $num) {
    parent::__construct($fn, 'expects ' . $expected . ' parameters; ' . $num . ' given');
  }
}

/**
 * Generic exception thrown when an unexpected error is encountered in
 * the processing of an FQL query.
 */
class UnknownErrorException extends FacebookFQLException {
  public function __construct($fn, $desc) {
    $vals = array('error_code' => FQL_EC_UNKNOWN_ERROR,
                  'error_msg'  => 'An unknown error occurred in ' . $fn . ':  ' . $desc);
    parent::__construct($vals);
  }
}
