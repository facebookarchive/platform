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
 * Class that represents an "OR" expression contained in
 * an FQL query. Can contain multiple operands, which
 * are each its own FQLExpression
 * (e.g. ((id = 200) OR 1 OR (album_id < 400)))
 */
class FQLDisjunction extends FQLMultiOpExpression {

  /**
   * Evaluate the value of this disjunction for this id value.
   *
   * @param  $id   id for which the operands should be evaluated
   * @return Returns true if any operands evaluates to true.
   *         Does short-circuit evaluation by returning true as soon
   *         as it encounters a true expression.
   *         If any of the operands is an instance of FQLCantSee,
   *         returns FQLCantSee if no other operand is true.
   */
  public function evaluate($id) {
    $result = false;
    foreach ($this->operands as $expr) {
      $res = $expr->evaluate($id);
      if ($res instanceof FQLCantSee) {
        // if nothing else is true, then we'll return FQLCantSee
        $result = $res;
      } else if ($res) {
        return true;
      }
    }
    return $result; // either false or FQLCantSee
  }

  /**
   * Get queries for this disjunction expression.  Since
   * the disjunction is OR-ing each of its operands,
   * the queries for the expression as a whole is merely the
   * union of the queries for each of the operands.
   *
   * @return associative array where the keys are query
   *         expressions, and they are all mapped to true
   */
  public function get_queries() {
    $queries = array();
    foreach ($this->operands as $expr) {
      $queries += $expr->get_queries();
    }
    return $queries;
  }

  /**
   * Returns a string representation of the "OR" expression.
   *
   * @return  string representation of the "OR" expression
   *          e.g. "((3 + 4) OR (id < 200) OR (album_id = 4))"
   */
  public function to_string() {
    $result = array();
    foreach ($this->operands as $expr) {
      $result[] = $expr->to_string();
    }
    return '(' . implode(' OR ', $result) . ')';
  }
}
