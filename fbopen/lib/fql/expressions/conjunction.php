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
 * Class that represents an "AND" expression contained in
 * an FQL query. Can contain multiple operands
 * (e.g. ((id = 200) AND 1 AND (album_id < 400)))
 */
class FQLConjunction extends FQLMultiOpExpression {

  /**
   * Evaluate the value of this conjunction for this id value.
   *
   * @param  $id   id for which the operands should be evaluated
   * @return Returns true if all operands evaluate to true.
   *         Does short-circuit evaluation by returning false as soon
   *         as it encounters a false expression.
   *         If any of the operands is an instance of FQLCantSee,
   *         returns FQLCantSee.
   */
  public function evaluate($id) {
    $result = true;
    foreach ($this->operands as $expr) {
      $res = $expr->evaluate($id);
      if ($res instanceof FQLCantSee) {
        // if nothing else is false, then we'll return FQLCantSee
        $result = $res;
      } else if (!$res) {
        return false;
      }
    }
    return $result; // either true or FQLCantSee
  }

  /**
   * Get the list of queries that fulfills all clauses of this
   * conjunction expression. Since this is an AND expression,
   * all resulting query expressions from this function must
   * fulfill every clause of the conjunction. Hence we have to
   * intersect every clause's queries with every other clause's
   * queries to get the results.
   *
   * @return  associative array where the keys are query
   *          expressions that fulfill all the clauses of this
   *          AND expression, mapping to 1
   */
  public function get_queries() {
    // first, get all of the queries of each of the operands
    $all_query_sets = array();
    foreach ($this->operands as $expr) {
      $all_query_sets[] = $expr->get_queries();
    }

    $table = $this->scope->from_table;

    // base case: there is just one operand, so we just use its queries
    $result = array_shift($all_query_sets);

    // for each additional operand
    foreach ($all_query_sets as $next_queries) {
      // take the current set of result queries
      $cur_queries = $result;
      $result = array();
      foreach ($cur_queries as $q1=>$true) {
        // compare each of the current result queries with each of
        // the queries for the next operand...
        foreach ($next_queries as $q2 => $true) {
          if ($and = $table->intersect_queries($q1, $q2)) {
            // if intersection of the two queries exist,
            // add it to the accumulating result array
            $result[$and] = 1;
          }
        }
      }
    }

    return $result;
  }

  /**
   * Returns a string representation of the "AND" expression.
   *
   * @return  string representation of the "AND" expression
   *          e.g. "((3 + 4) AND (id < 200) AND (album_id = 4))"
   */
  public function to_string() {
    $result = array();
    foreach ($this->operands as $expr) {
      $result[] = $expr->to_string();
    }
    return '(' . implode(' AND ', $result) . ')';
  }
}
