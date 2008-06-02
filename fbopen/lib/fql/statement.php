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

/*
 * Class that represents an FQL query. This class stores all the data
 * related to a query (each of the clauses, e.g. SELECT, WHERE, etc)
 * as well as metadata for the query, like the user and app_id making
 * the query.
 */
class FQLStatement {
  // array of FQLExpressions from the SELECT clause of the query
  public  $select;

  // name of the source table for this query (string)
  public  $from;

  // FQLTable object for the source table
  public  $from_table;

  // FQLExpression from the WHERE clause of the query
  public  $where;

  // max number of results to return
  public  $limit;

  // offset at which results should begin
  public  $offset;

  // FQLExpression that the results should be ordered by
  public  $orderby;

  // boolean indicating whether order by should be descending or ascending
  public  $desc;

  // user on whose behalf the FQL query is made
  public  $user;

  // id of the application making the FQL query
  public  $app_id;

  // list of tables supported by this implementation of FQL
  // maps from table name to class that implements that table
  public static $tables = array(
    'user'              => 'FQLUserTable',
    'friend'            => 'FQLFriendTable',
    'friendlist'        => 'FQLFriendListTable',
    'friendlist_member' => 'FQLFriendListMemberTable',
    // 'table name'  => 'FQLTableClassName',
  );

  /**
   * Create a new FQLStatement.
   *
   * @param  $select  an array of FQLExpressions from the SELECT clause
   * @param  $from    the table to select from
   * @param  $where   FQLExpression from the WHERE clause
   * @param  $orderby array containing:
   *                  0: FQLExpression from the ORDER BY clause
   *                  1: boolean indicating whether it's ascending or descending
   * @param  $limit   array containing:
   *                  0: number to offset the results by
   *                  1: number of results to limit to
   * @param  $user    id of the currently logged-in user
   * @param  $app_id  application id making the request
   */
  public function __construct($select, $from, $where, $orderby, $limit, $user=null, $app_id=null) {
    $this->select = $select;
    $this->from   = $from;
    if (!isset(self::$tables[$from])) {
      throw new UnknownTableException($from);
    }
    $this->from_table = new self::$tables[$from]($user, $app_id, $from);
    $this->where  = $where;
    $this->user   = $user;
    $this->app_id = $app_id;
    if ($orderby) {
      $this->orderby = $orderby[0];
      $this->desc    = $orderby[1];
    }
    if ($limit) {
      $this->offset = $limit[0];
      $this->limit  = $limit[1];
    }
  }

  /**
   * Returns the name of the thrift object that implements the
   * data for this table name. Assumes that the table name
   * can be converted to the thrift object name by just prepending
   * 'api10_'.
   *
   * @param  $table_name name of table that we want the thrift
   *                     object name for
   * @return string that represents thrift object name for this
   *         table, currently just prepends 'fql_'
   */
  static private function thrift_name($table_name) {
    return 'api10_'. $table_name;
  }

  // only used internally, for storing temporary results of
  // FQLInStatements
  const OUT_FORMAT_NUMERIC = 1;

  // outputs the results as serialized thrift objects
  const OUT_FORMAT_THRIFT  = 2;

  // outputs the results in XML
  const OUT_FORMAT_XML     = 3;

  /*
   * Evaluates the query by applying all of the clauses (WHERE constraints,
   * ORDER BY clauses, SELECTing the right data, gathers
   * the results, and returns the data in the specified format.
   *
   * @param  $format indicates what format the results should be in
   *                 (numeric, thrift or XML)
   * @return results of the query in the specified format
   */
  public function evaluate($format) {
    // set the scope of all the children
    foreach ($this->select as $expr) {
      $expr->set_scope($this);
    }
    $this->where->set_scope($this);
    if ($this->orderby) {
      $this->orderby->set_scope($this);
    }

    // based on the WHERE clause, we first get a set of query expressions that
    // represent the constraints on values for the indexable columns contained
    // in the WHERE clause
    $queries = $this->where->get_queries();

    // from these queries, get a list of ids that match these queries, capped
    // at 5000
    $ids = array_slice(array_keys($this->from_table->get_ids_for_queries($queries, 5000)),
                       0, 5000);

    // lots of empty-result queries benefit from this quick checking
    if (!$ids) {
      return array();
    }

    // prime the cache to get data for these ids
    $this->from_table->prime_cache($ids);
    $this->where->prime_cache($ids);
    if ($this->orderby) {
      $this->orderby->prime_cache($ids);
    }

    // sort ids by the ORDER BY clause
    if ($this->orderby) {
      $to_sort = array();
      foreach ($ids as $id) {
        $to_sort[$id] = $this->orderby->evaluate($id);
      }
      if ($this->desc) {
        arsort($to_sort);
      } else {
        asort($to_sort);
      }
      $ids = array_keys($to_sort);
    }

    // filter the set of ids by the WHERE clause and LIMIT params
    $result_ids = array();
    $offset_left = $this->offset;
    foreach ($ids as $id) {
      if ($this->from_table->can_see($id)) {
        $where_result = $this->where->evaluate($id);
        // check if this id passed the WHERE constraints and
        // is not restricted by privacy
        if ($where_result && !($where_result instanceof FQLCantSee)) {
          // do not include this result because of the offset
          if ($offset_left) {
            $offset_left--;
          } else {
            $result_ids []= $id;
            if ($this->limit && (count($result_ids) == $this->limit)) {
              break;
            }
          }
        }
      }
    }

    // prime cache data for the data we're actually selecting
    foreach ($this->select as $expression) {
      $expression->prime_cache($result_ids);
    }

    // fill in the result array with the requested data
    $result = array();
    $row_name = $this->from_table->get_name();
    foreach ($result_ids as $id) {
      // whether or not the thrift format needed to fallback to json,
      // when it can't find a thrift object of the correct name
      $flag_json = false;

      if ($format == self::OUT_FORMAT_XML) {
        $row = new xml_element($row_name, array());
      } else if ($format == self::OUT_FORMAT_THRIFT) {
        // Use a thrift object if one exists, otherwise fall back to PHP
        // associative array
        if (class_exists($thrift_name = self::thrift_name($row_name))) {
          $row = new $thrift_name();
        } else {
          $flag_json = true;
          $row = array();
        }
      } else {
        $row = array();
      }
      foreach ($this->select as $str => $expression) {
        $name = $expression->get_name();
        $col = $expression->evaluate($id);
        if ($format == self::OUT_FORMAT_XML) {
          $row->value[] = new xml_element($name, $col);
        } else if ($format == self::OUT_FORMAT_THRIFT) {
          if ($flag_json) {
            $row[$name] = $col;
          } else {
            $row->$name = $col;
          }
        } else {
          $row[] = $col;
        }
      }
      $result[] = $row;
    }
    return $result;
  }

  /**
   * Returns a normalized string form of an FQL statment.
   *
   * @return string representation of an FQL statement
   */
  public function to_string() {
    if (!$this->select || !$this->from || !$this->from_table || !$this->where) {
      return '';
    }
    $select_statements = array();
    // normalize each of the select statements
    foreach ($this->select as $select_statement) {
      $select_statements[] = $select_statement->to_string();
    }

    $result = 'SELECT ' . implode(', ', $select_statements) . ' ';
    $result .= 'FROM ' . $this->from . ' ';
    $result .= 'WHERE ' . $this->where->to_string() . ' ';
    if ($this->orderby) {
      $result .= 'ORDER BY ' . $this->orderby->to_string();
      if ($this->desc) {
        $result .= ' DESC ';
      }
    }
    if ($this->limit || $this->offset) {
      $result .= 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
    }

    return $result;
  }
}


