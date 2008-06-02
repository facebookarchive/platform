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
 * Class that represents an arithmetic expression contained in
 * an FQL query. Arithmetic expressions are always binary
 * expressions with a left and right operand and an
 * arithmetic operator, one of +, -, *, /.
 */
class FQLArithmeticExpression extends FQLBinOpExpression {
  protected $op;

  /**
   * Constructs the arithmetic expression with the arithmetic
   * operator $op and two operands, $left and $right, which
   * are FQLExpressions themselves.
   *
   * @param  $op     arithmetic op (+, -, *, /)
   * @param  $left   FQLExpression representing left operand
   * @param  $right  FQLExpression representing right operand
   */
  public function __construct($op, $left, $right) {
    $this->op = $op;
    parent::__construct($left, $right);
  }

  /**
   * Static function that converts the parameter $obj into
   * an integer value ($obj is passed by reference). Returns
   * false if $obj has privacy restrictions so the value
   * cannot be evaluated.
   *
   * @param  $obj FQLExpression to be converted to a scalar
   *              value, passed by reference
   * @return false if FQLExpression is restricted by privacy
   *         otherwise returns true
   */
  private static function to_int(&$obj) {
    if (!is_scalar($obj)) {
      if ($obj instanceof FQLCantSee) {
        return false;
      }
      if (is_object($obj)) {
        $obj = self::get_scalar_value($obj);
      }
      if (!is_scalar($obj)) {
        $obj = 0;
      }
    }
    return true;
  }

  /**
   * Evaluates the value for an FQLArithmeticExpression by
   * evaluating the operands and then performing the operation.
   *
   * @param  $id  id for which the operands should be evaluated
   * @return Result of the arithmetic expression. If one of the
   *         operands does not evaluate to a scalar value, it
   *         returns that operand by default and does not perform
   *         the arithmetic operation.
   * Throws ParserErrorException when encountering an unknown
   * operator.
   */
  public function evaluate($id) {
    $left = $this->left->evaluate($id);
    $right = $this->right->evaluate($id);
    if (!self::to_int($left)) {
      return $left;
    }
    if (!self::to_int($right)) {
      return $right;
    }
    switch ($this->op) {
      case '+':
        $result = $left + $right;
        break;
      case '-':
        $result = $left - $right;
        break;
      case '*':
        $result = $left * $right;
        break;
      case '/':
        if ($right == 0) {
          $result = null;
        } else {
          $result = $left / $right;
        }
        break;
      default:
        throw new ParserErrorException('unknown operator "' . $this->op . '"');
    }
    return $result;
  }

  /**
   * Returns a string representation of the arithmetic expression.
   *
   * @return  string representation of arithmetic expression
   *          e.g. "(N + N)" (where N is a constant)
   */
  public function to_string() {
    return '(' . $this->left->to_string() . ' ' .
      $this->op . ' ' .
      $this->right->to_string() . ')';
  }
}
