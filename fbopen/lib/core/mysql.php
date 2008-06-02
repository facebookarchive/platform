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
 * MySQL access library. Defines classes and functions for interfacing with
 * the databases. You should never use the mysql_ functions directly. Instead
 * use the methods defined in here and lib/core/connect.php to get access
 * to the DB and query in a safe way.
 */

/**
 * Typechecks parameters to be passed into printf/sprintf/vsprintf.
 *
 * @param string $str     String to be passed into printf
 * @param array  $argv    Argument from calling function
 * @param int    $offset  Index into arguments of calling function where printf parameters start
 * @internal Candidate for inclusion in Facebook PHP extension
 * @see sprintf http://php.net/sprintf
 */
function typecheck_vprintf($str, &$argv, $offset) {  // typecheck_printf(string $str, array $argv, int $offset);
  $argc = count($argv);     // number of arguments total

  $specs = '+-\'-.0123456789';  // +-'-.0123456789 are special printf specifiers
  $pos   = 0;                   // string position
  $param = $offset;                   // current parameter

  while ($pos = strpos($str, '%', $pos)) {          // read each %
    while ($pos2 = strpos($specs, $str{$pos+1})) {  // read past specs chars
      $pos++;
    }

    if ($str[$pos + 1] == '%') {
      // '%%' for literal %
      $pos += 2;
      continue;
    }

    if (ctype_alpha($str{$pos+1})) {

      if ((!is_scalar($argv[$param])) && (!is_null($argv[$param]))) {
        error_log("TYPECHECK_VPRINTF failed, non scalar type passed as parameter, failed on: $str");
        return 0;
      }

      switch ($str{$pos+1}) {     // use ascii value
        case 's': // the argument is treated as and presented as a string.
          if (!is_string($argv[$param])) {
            $argv[$param] = (string)$argv[$param];
          }
          break;

        case 'd': // the argument is treated as an integer, and presented as a (signed) decimal number.
        case 'b': // the argument is treated as an integer, and presented as a binary number.
        case 'c': // the argument is treated as an integer, and presented as the character with that ASCII value.
        case 'e': // the argument is treated as scientific notation (e.g. 1.2e+2).
        case 'u': // the argument is treated as an integer, and presented as an unsigned decimal number.
        case 'o': // the argument is treated as an integer, and presented as an octal number.
        case 'x': // the argument is treated as an integer and presented as a hexadecimal number (with lowercase letters).
        case 'X': // the argument is treated as an integer and presented as a hexadecimal number (with uppercase letters).
          if (!is_int($argv[$param])) {
            $argv[$param] = (int)$argv[$param];
          }
          break;

        case 'f': // the argument is treated as a float, and presented as a floating-point number (locale aware).
        case 'F': // the argument is treated as a float, and presented as a floating-point number (non-locale aware). Available since PHP 4.3.10 and PHP 5.0.3.
          if (!is_float($argv[$param])) {
            $argv[$param] = (float)$argv[$param];
          }
          break;
      }

      $param++;  // next please!
    }

    $pos++;  // your number is up!
  }

  if ($param != $argc) {  // make sure the number of parameters actually matches the number of params in string
    error_log("TYPECHECK_VPRINTF failed, query parameter missing, failed on: $str");
    return 0;
  }

  return 1;
}

/* Converts a parameterized SQL query to a non-parameterized SQL query string
 *
 * Usage: $ret = squeryf($conn, "SELECT * FROM info WHERE id=%d AND name=%s", $id, $name);
 *
 * @param resource $conn       MySQL connection resource
 * @param string   $sql        SQL query to execute
 * @param mixed    $params,... SQL query parameters
 * @return                     SQL query sstring
 *
 *
 * @see sprintf http://php.net/sprintf
 */
function squeryf($conn, $sql) {  // squeryf( resource $conn, string $sql [, mixed $params [, mixed  ...]] );
  $argv = func_get_args();  // variable length function arguments
  $argc = count($argv);     // number of argumentns total
  $sql_params = array();    // container for sql parameters

  if ($argc > 2) {

    if (!typecheck_vprintf($sql, $argv, 2)) {
      return false;
    }

    for ($x=2; $x<$argc; $x++) {                 // get all optional params starting from the third parameter to the last (however many)
      if (is_string($argv[$x])) {               // check for string type
        // w/ string quote handlers x1000 = 0.0937 vs 0.0824 without

        $sql_str = $argv[$x];
        $sql_str = conn_real_escape_string($sql_str, $conn); // use conn_real_escape_string c/api escaping for: \x00, \n, \r, \, ', " and \x1a
        $sql_params[] = '\''.$sql_str.'\'';     // add quotes surrounding string params
      } elseif (is_scalar($argv[$x])) {         // check for int/float/bool
        $sql_params[] = $argv[$x];              // don't do anything to int types, they are safe
      } else {                                  // unsupported type (array, object, resource, null)
        $bad_param = str_replace("\n", '', var_export($argv[$x], true));    // capture variable info for debug msg
        error_log("MYSQL_QUERYF: non-scalar type for SQL query parameter $x: ".$argv[$x].", var: $bad_param, sql: [$sql]");
        return false;
      }
    }

    $ok_sql = vsprintf($sql, $sql_params);    // use vsprintf to merge parameters and the query string

    if ($ok_sql=='') {  // if blank this is because of a parameter count mismatch (maybe even something else)
      error_log("MYSQL_QUERYF: SQL query parameter missing, sprintf failed on: $sql");
    }
  } else {
    $ok_sql = str_replace('%%', '%', $sql);
  }

  return $ok_sql;
}


/**
 * Wrapper for queryf
 *
 * Allows arguments to be passed into queryf as array, see queryf for proper
 * usage.
 *
 * @param resource $conn   mysql connection handle
 * @param string   $sql    formated sql query
 * @param array    $params array of parameters to pass into queryf()
 *
 *
 * @see queryf
 */
function vqueryf($conn, $sql, $params) {
  if (!is_array($params)) {
    error_log('vqueryf: '.$params.' is not an array!');
    return false;
  }
  array_splice($params, 0, 0, array($conn, $sql));
  return call_user_func_array('queryf', $params);
}

function conn_real_escape_string($str, $conn = NULL) {
  if ($conn instanceof managed_connection) {
    $conn = $conn->get_connection();
  } elseif (!$conn) {
    $escaped = @mysql_real_escape_string($str);
    if (!$escaped) {
      return mysql_escape_string($str);
    } else {
      return $escaped;
    }
  }
  return mysql_real_escape_string($str, $conn);
}

/**
 *  Turns some text into a multiline MySQL comment. The MySQL parser is pretty
 *  busted so this function is intentionally overeager, particularly since
 *  user data has a way of ending up in comments and then being really horribly
 *  insecure and/or fragile. See the comments here:
 *    http://dev.mysql.com/doc/refman/5.0/en/comments.html
 *
 *  This problem is double-dangerous because MySQL lets you put executable code
 *  in comments in the form "!XXXXX SELECT * FROM secrets", where 'XXXXX' is
 *  some MySQL version number like 40000. This is totally insane. Comments are
 *  a really bad place to put data. Unfortunately, they're also a really useful
 *  place to put data, so we're left with this mess.
 *
 *  @param  string  A string to render as a MySQL multi-line comment.
 *  @return string  The argument, rendered safe for putting in a SQL query.
 *
 */
function mysql_escape_multiline_comment($comment) {

  //  These can either terminate a comment, confuse the hell out of the parser,
  //  make MySQL execute the comment as a query, or, in the case of semicolon,
  //  are quasi-dangerous because the semicolon could turn a broken query into
  //  a working query plus an ignored query.

  static $bad  = array(
    '--', '*/', '//', '#', '!', ';');
  static $safe = array(
    '(DOUBLEDASH)', '(STARSLASH)', '(SLASHSLASH)', '(HASH)', '(BANG)',
    '(SEMICOLON)');

  $comment = str_replace($bad, $safe, $comment);

  //  For good measure, kill anything else that isn't a nice printable
  //  character.

  $comment = preg_replace('/[^\x20-\x7F]+/', ' ', $comment);

  return '/* '.$comment.' */';
}


function queryf($conn, $sql) {
  $argv = func_get_args();
  return call_user_func_array('queryf_common', $argv);
}

function queryf_common($conn, $sql) {

  $argv = func_get_args();  // variable length function arguments
  $ongoing_transaction = false;

  if (!is_resource($conn)) {    // trap bad resources first, log and exit. ensures only one error in the error log
    error_log("MYSQL_QUERYF failed: [$sql]. connection not a valid resource.");
    return false;
  }

  $ok_sql = call_user_func_array('squeryf', $argv);
  

  $ok_sql = mysql_escape_multiline_comment($pre).' '.$ok_sql;
    $ret = @mysql_query($ok_sql, $conn);
    if (!$ret) { // check for failure and attempt retry if failed
      // retry by reopening the same connection

      $error = mysql_errno($conn);              // trap error
      error_log('MYSQL_QUERYF failed. queryf: ['.$ok_sql.']. mysql_error: '.
          mysql_error($conn).' mysql_get_host_info: '.
          mysql_get_host_info($conn));
    }
  return $ret;    // thank you, come again.
}

