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
 * Concatenates all the parameters into
 * a string, separated by commas.
 *
 * @param  Takes any number of parameters,
 *         should be strings
 * @return string concatenating all params by commas
 */
function ConcatStrings() {
  return implode(func_get_args());
}

/**
 * Function that searches for the first position at which
 * $needle occurs in $haystack.
 *
 * @param  $haystack string in which to search
 * @param  $needle   string to search for
 * @return index at which first occurrence of $needle starts
 *         in $haystack, or -1 if $needle is empty or is not
 *         found in $haystack
 */
function StringPos($haystack, $needle) {
  if (empty($needle)) {
    return -1;
  }

  $res = strpos($haystack, $needle);
  if ($res === false) {
    return -1;
  } else {
    return $res;
  }
}

/**
 * Class that represents a function call contained within
 * an FQLExpression, e.g. strlen or rand()
 */
class FQLFunction extends FQLBaseExpression {
  // valid functions that can be called
  public static $functions = array(
    'now'       => array('phpfunc' => 'time',           'params' => 0),
    'strlen'    => array('phpfunc' => 'strlen',         'params' => 1),
    'concat'    => array('phpfunc' => 'ConcatStrings'), // any # of parameters
    'substr'    => array('phpfunc' => 'substr',         'params' => 3),
    'strpos'    => array('phpfunc' => 'StringPos',      'params' => 2),
    'lower'     => array('phpfunc' => 'strtolower',     'params' => 1),
    'upper'     => array('phpfunc' => 'strtoupper',     'params' => 1),
    'rand'      => array('phpfunc' => 'rand',           'params' => 0),
  );

  // PHP name of function
  protected $fn;

  // function name as parsed in the query
  protected $fn_name;

  // array of arguments (FQLExpressions) to pass to the function
  protected $args;

  /**
   * Constructs a function expression with the given function name
   * and arguments. Checks the validity of the function name and the
   * corresponding number of arguments passed to the function.
   *
   * @param  $fn_name name of the function to call (now, strlen,
   *                  concat, substr, strpos, lower, upper, rand)
   * @param  $args    array of arguments passed to the function
   */
  public function __construct($fn_name, $args) {
    if (!isset(self::$functions[$fn_name])) {
      throw new UnknownFunctionException($fn_name);
    }
    $func = self::$functions[$fn_name];
    $this->fn_name = $fn_name;
    $this->fn = $func['phpfunc'];
    if (isset($func['params']) && count($args) != $func['params']) {
      throw new WrongParamNumException($fn_name, $func['params'], count($args));
    }
    $this->args = $args;
  }

  /**
   * Sets the scope for the function expression by setting
   * the scope for each of the function arguments.
   *
   * @param $scope  FQLStatement that the function call was contained in
   */
  public function set_scope($scope) {
    foreach ($this->args as $arg) {
      $arg->set_scope($scope);
    }
  }

  /**
   * Evaluate the value of the function call, first evaluating each of the
   * arguments for this value of $id.
   *
   * Only scalar values are passed to the function call, objects
   * are either converted to scalar values or passed as null.
   *
   * @param  $id value of id to evaluate the arguments for
   * @return the result of the function call
   */
  public function evaluate($id) {
    $args_eval = array();
    foreach ($this->args as $arg) {
      $arg_eval = $arg->evaluate($id);
      if (is_object($arg_eval)) {
        $arg_eval = self::get_scalar_value($arg_eval);
      }
      if (!is_scalar($arg_eval)) {
        $arg_eval = null;
      }
      $args_eval[] = $arg_eval;
    }
    return call_user_func_array($this->fn, $args_eval);
  }

  /**
   * Primes the cache for this set of id values by priming the
   * cache for each of the arguments.
   *
   * @param $ids  set of ids to prime the cache for
   */
  public function prime_cache($ids) {
    foreach ($this->args as $arg) {
      $arg->prime_cache($ids);
    }
  }

  /**
   * Returns a string representation of the function expression.
   *
   * @return  string representation of the function expression
   *          e.g. "(strlen(id))
   */
  public function to_string() {
    $arg_strings = array();
    foreach ($this->args as $arg_expression) {
      $arg_strings[] = $arg_expression->to_string();
    }
    return $this->fn_name . '(' . implode(', ', $arg_strings) . ')';
  }
}
