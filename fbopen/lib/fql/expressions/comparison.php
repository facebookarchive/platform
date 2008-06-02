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
 * Class that represents a comparison expression that
 * can be contained in an FQL query.
 */
class FQLComparisonExpression extends FQLBinOpExpression {
  protected $op;

  /**
   * Constructs a comparison expression with the comparison
   * operator $op and two operands, $left and $right,
   * which are FQLExpressions themselves.
   *
   * @param $op    comparison operator (<, >, =, ==, <=,
   *               >=, !=, <>, ^=, !^)
   * @param $left  FQLExpression representing left operand
   * @param $right FQLExpression representing right operand
   */
  public function __construct($op, $left, $right) {
    $this->op = $op;

    // canonicalize operators
    switch ($this->op) {
    case '=':  $this->op = '=='; break;
    case '<>': $this->op = '!='; break;
    }

    parent::__construct($left, $right);
  }

  /**
   * Performs the comparison between the evaluated results of
   * the two operands and returns the result.
   *
   * @param  $id
   * @return Boolean result after performing the comparison
   *         between the two operands.
   * Throws ParserErrorException when encountering an
   * unknown comparison operator.
   */
  public function evaluate($id) {
    $left = $this->left->evaluate($id);
    if ($left instanceof FQLCantSee) {
      return $left;
    }
    $right = $this->right->evaluate($id);
    if ($right instanceof FQLCantSee) {
      return $right;
    }

    // convert operands to scalar values, if possible
    if (is_object($left)) {
      $left = self::get_scalar_value($left);
    }
    if (is_string($left)) {
      $left = strtolower($left);
    }

    if (is_object($right)) {
      $right = self::get_scalar_value($right);
    }
    if (is_string($right)) {
      $right = strtolower($right);
    }

    switch ($this->op) {
      case '==':
        return $left == $right;
      case '>':
        return $left > $right;
      case '<':
        return $left < $right;
      case '>=':
        return $left >= $right;
      case '<=':
        return $left <= $right;
      case '!=':
        return $left != $right;
      case '^=':
        return substr($left, 0, strlen($right)) == $right;
      case '!^':
        return substr($left, 0, strlen($right)) != $right;
      default:
        throw new ParserErrorException('unknown operator "' . $this->op . '"');
    }
  }

  /**
   * Gets the query expression that the comparison expression
   * is enforcing. Currently this supports equality only, so an
   * expression like id < 3 will yield ':', which matches all ids.
   * An expression like id == 3 will yield the query for the field
   * 'id' for the value 3.
   *
   * Only comparisons between a field and a constant are valid.
   *
   * @return associative array where the keys are the query expressions
   *         and they all map to 1.
   */
  public function get_queries() {
    if ($this->op != '==') {
      // can only index by equality
      return parent::get_queries();
    }

    if ($this->right instanceof FQLConstantExpression &&
        $this->left instanceof FQLFieldExpression) {
      // expression is something like "id == 3"
      $query = $this->left->field->get_query($this->right->evaluate(null));
    } else if ($this->left instanceof FQLConstantExpression &&
               $this->right instanceof FQLFieldExpression) {
      // expression is something like "3 == id"
      $query = $this->right->field->get_query($this->left->evaluate(null));
    } else {
      return parent::get_queries();
    }
    if (isset($query) && $query) {
      return array($query => 1);
    } else {
      // this means that we're sure there are no matches.  e.g. you did a
      // search for groups where id == -5 or something.
      return array();
    }
  }

  /**
   * Returns a string representation of a comparison expression.
   *
   * @return string representation of a comparison expression
   *         e.g. (id < 201)
   */
  public function to_string() {
    return '(' . $this->left->to_string() . ' ' .
      $this->op . ' ' .
      $this->right->to_string() . ')';
  }
}

/**
 * Class that represents a "not" expression contained in
 * an FQL query (e.g. "NOT id=200")
 */
class FQLNotExpression extends FQLBaseExpression {
  protected $right;

  /**
   * Constructs a "not" expression with the single
   * righthand expression.
   *
   * @param $right  FQLExpression being negated
   */
  public function __construct($right) {
    $this->right = $right;
  }

  /**
   * Primes cache for this set of ids.
   *
   * @param $ids  set of ids to prime cache for
   */
  public function prime_cache($ids) {
    $this->right->prime_cache($ids);
  }

  /**
   * Sets scope for this expression so it knows its
   * source table.
   *
   * @param $stmt  FQLStatement that this expression was contained in
   */
  public function set_scope($stmt) {
    $this->right->set_scope($stmt);
    parent::set_scope($stmt);
  }

  /**
   * Evaluates the value of this "not" expression by evaluating
   * the righthand expression and then negating its value.
   *
   * @param  $id
   * @return boolean result of negating the righthand expression
   */
  public function evaluate($id) {
    $right = $this->right->evaluate($id);
    if ($right instanceof FQLCantSee) {
      return $right;
    } else {
      if (is_object($right)) {
        $right = self::get_scalar_value($right);
      }
      return !$right;
    }
  }

  /**
   * Returns the string representation of a negated expression.
   *
   * @return  string representation of a "not" expression
   *          (e.g. "NOT id=1")
   */
  public function to_string() {
    return 'NOT ' . $this->right->to_string();
  }
}
