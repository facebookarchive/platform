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
 * Class that represents an "IN" expression that can be contained
 * in an FQL query, specifically one where the clause following the
 * keyword IN is an independent FQL statement on its own (e.g.
 * SELECT name FROM user WHERE uid IN (SELECT uid2 FROM friends WHERE
 * uid1 = 4);
 */
class FQLInStatement extends FQLBaseExpression {

  /**
   * Constructs an FQLInStatement.  An FQLInStatement has
   * two parts, the expression and the statement (e.g.
   * for (uid IN (SELECT uid FROM user WHERE name = 'Mary')),
   * uid is the expression and the entire SELECT statement
   * is the statement.
   *
   * @param $expression FQLExpression that we're checking the
   *                    value for
   * @param $stmt       FQLStatement that will yield results in
   *                    which we should check for $expression
   */
  public function __construct($expression, $stmt) {
    $this->expression = $expression;
    $this->stmt = $stmt;
    $this->inv_stmt_res = null;
  }

  /**
   * Sets the scope for this IN statement by setting the scope
   * for the expression.
   *
   * @param $scope  the FQLStatement that this IN statement
   *                is contained within
   */
  public function set_scope($scope) {
    $this->expression->set_scope($scope);
    parent::set_scope($scope);
  }

  /**
   * Static internal helper function for building an associative
   * array of values represented by $elem.  $elem is the result
   * of evaluation of an FQLStatement, and this function iterates
   * through all the results (including nested arrays within the
   * results) to produce a single associative array where the keys
   * are all the values that it has encountered.
   *
   * @param  $elem Results of evaluation of an FQLStatement.
   *               This can be a value itself, or an array, or
   *               an array containing many nested arrays.
   * @return associative array of values found in $elem, where
   *         the keys are the values, all mapping to 1
   */
  static private function build_inv_array($elem) {
    $arr = $elem instanceof xml_element ? $elem->value : $elem;
    if (is_scalar($arr)) {
      return array($arr => 1);
    }
    $inv_res = array();
    foreach ($arr as $row) {
      $inv_res += self::build_inv_array($row);
    }
    return $inv_res;
  }

  /**
   * Evaluates the IN expression by first evaluating the value for the
   * initial expression.  Then it fetches results for the FQLStatement
   * $stmt, in numeric format (since this is internal to FQL). It then
   * checks those results for the initial expression value, and returns
   * true if it finds it.
   *
   * @param  $id  id value for which the expression should be evaluated
   * @return boolean, true if the value for $expression is contained
   *         in the results of evaluating $stmt, false otherwise
   */
  public function evaluate($id) {
    $val = $this->expression->evaluate($id);
    if (is_object($val)) {
      // get scalar value (in case it's something like location.zip)
      $val = self::get_scalar_value($val);
    }
    if (is_scalar($val)) {
      if (!isset($this->inv_stmt_res)) {
        // evaluate the inner FQLStatement
        $stmt_result = $this->stmt->evaluate(FQLStatement::OUT_FORMAT_NUMERIC);
        $this->inv_stmt_res = self::build_inv_array($stmt_result);
      }
      return isset($this->inv_stmt_res[$val]);
    }
    return false;
  }

  /**
   * Gets a list of query expressions that represents the constraints
   * expressed in this IN expression.
   *
   * Queries are returned only when the initial expression to be
   * checked is a field expression.  Otherwise, it returns the default
   * wildcard (':'), which matches all ids.
   *
   * This function first evaluates the inner FQLStatement, and then
   * generates queries based on the field that $expression represents
   * for each of the results of the inner FQLStatement evaluation.
   *
   * @return  associative array of queries, where the keys are the queries
   *          all mapping to 1
   */
  public function get_queries() {
    if ($this->expression instanceof FQLFieldExpression) {
      $ids = array();
      $stmt_result = $this->stmt->evaluate(FQLStatement::OUT_FORMAT_NUMERIC);
      $this->inv_stmt_res = self::build_inv_array($stmt_result);
      foreach ($this->inv_stmt_res as $val=>$true) {
        $query = $this->expression->field->get_query($val, '=');
        if ($query) {
          $ids[$query] = 1;
        }
      }
      return $ids;
    }
    return parent::get_queries();
  }

  /**
   * Primes the cache for this set of ids by priming the cache for
   * this expression.
   *
   * @param $ids  list of ids to prime cache for
   */
  public function prime_cache($ids) {
    $this->expression->prime_cache($ids);
  }

  /**
   * Returns a string representation of this IN statement.
   *
   * @return string representation of the IN statement
   */
  public function to_string() {
    return $this->expression->to_string() . ' IN (' .
      $this->stmt->to_string() . ')';
  }
}

/**
 * Class that represents an IN expression contained within
 * an FQL query where the IN clause consists of an array of
 * FQLExpressions.  For instance, an expression like
 * (uid IN (1, 2, 3)) would be an FQLInArray.
 */
class FQLInArray extends FQLBaseExpression {
  /**
   * Constructs an FQLInArray by storing the expression being checked
   * and the list of values to check against (e.g. uid and (1,2,3)
   * for the expression (uid IN (1,2,3)).
   *
   * @param $expression FQLExpression being checked for membership in the list
   * @param $list       List of FQLExpressions to check to see if they
   *                    match $expression
   */
  public function __construct($expression, $list) {
    $this->expression = $expression;
    $this->list = $list;
  }

  /**
   * Sets the scope for this IN expression by setting the scope for
   * the initial expression and the list of expressions.
   *
   * @param $scope the FQLStatement that this IN expression was contained in
   */
  public function set_scope($scope) {
    $this->expression->set_scope($scope);
    foreach ($this->list as $expression) {
      $expression->set_scope($scope);
    }
    parent::set_scope($scope);
  }

  /**
   * Static helper function that returns true if $val is contained
   * in $arr.  It performs a variety of checks so that it will
   * return true if $val == $arr, if $arr contains $val directly,
   * or if $val is contained in a nested array within $arr.
   *
   * @param  $val  value to search for
   * @param  $arr  structure to search for $val, can be nested to any level
   * @return true if $val is found in $arr, false otherwise
   */
  static private function deep_in($val, $arr) {
    if (is_scalar($arr)) {
      return $val == $arr;
    }
    foreach ($arr as $row) {
      if (self::deep_in($val, $row instanceof xml_element ? $row->value : $row)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Evaluates the IN expression for the value of id by evaluating
   * the expression to be checked for the value of id, and then
   * evaluating all expressions to be checked against, also for
   * the value of id.  Then returns true if the evaluated result
   * of the initial expression is found anywhere in the list of
   * results from expressions to be checked against.
   *
   * @param  $id  value of id to evaluate all expressions for
   * @return true if evaluated result of initial expression is
   *         found anywhere in the evaluated results of expressions
   *         to be checked against
   */
  public function evaluate($id) {
    $val = $this->expression->evaluate($id);
    if (is_object($val)) {
      $val = self::get_scalar_value($val);
    }
    if (is_scalar($val)) {
      if (isset($this->const_list)) {
        // don't have to re-eval all the consts if that's all we've got
        return isset($this->const_list[$val]);
      }
      foreach ($this->list as $expression) {
        $arr = $expression->evaluate($id);
        if (isset($arr) && self::deep_in($val, $arr)) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Gets the list of query expressions that matches the constraints
   * expressed in the IN expression.
   *
   * Queries are only generated when the expression is of the form
   * uid IN (1, 2, 3,...) where uid is a field name and the list
   * contains only constants, or 90210 IN (hometown_location, current_location)
   * where the initial expression is a constant and the list
   * contains only field names.  In all other cases, this function
   * will just return the generic ':' wildcard query, which matches all
   * ids.
   *
   * @return associative array of queries where the keys are the queries,
   *         all mapping to 1
   */
  public function get_queries() {
    if ($this->expression instanceof FQLFieldExpression) {
      // like (uid IN (1,3,5))
      $queries = array();
      $all_consts = array();
      foreach ($this->list as $const) {
        if (!($const instanceof FQLConstantExpression)) {
          return parent::get_queries();
        }
        $val = $const->evaluate(null);
        $all_consts[$val] = 1;
        $query = $this->expression->field->get_query($val, '=');
        if ($query) {
          $queries[$query] = 1;
        }
      }
      // save the list of constants so it doesn't need to be reevaluated each time
      $this->const_list = $all_consts;
      return $queries;
    } else if ($this->expression instanceof FQLConstantExpression) {
      // like (90210 IN (hometown_location, current_location))
      $queries = array();
      $val = $this->expression->evaluate(null);
      foreach ($this->list as $field) {
        if (!($field instanceof FQLFieldExpression)) {
          return parent::get_queries();
        }
        $query = $field->field->get_query($val, '=');
        if ($query) {
          $queries[$query] = 1;
        }
      }
      return $queries;
    }
    return parent::get_queries();
  }

  /**
   * Primes the cache for this set of ids for this expression by priming
   * the cache both for the expression to be checked and all the expressions
   * in the membership list to be checked against.
   *
   * @param $ids  list of ids to prime the cache for
   */
  public function prime_cache($ids) {
    $this->expression->prime_cache($ids);
    foreach ($this->list as $expression) {
      $expression->prime_cache($ids);
    }
  }

  /**
   * Returns a string representation of this IN expression.
   *
   * @return string representation of this IN expression
   */
  public function to_string() {
    $in_strings = array();
    foreach ($this->list as $expression) {
      $in_strings[] = $expression->to_string();
    }
    return $this->expression->to_string() . ' IN (' .
      implode(', ', $in_strings) . ')';
  }
}
