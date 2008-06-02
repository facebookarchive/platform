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
 * Abstract interface that represents an expression that can be
 * used in an FQL query.  Examples of types of expressions are:
 *
 * Comparisons: x < 3, x = 5
 * Arithmetic expressions: x + 3, 5 * 5
 * FQL fields: user_id, album_id, hometown_location
 * Conjunctions: (x < 3) AND (user_id = 4)
 * Disjunctions: (x < 3) OR (user_id = 4)
 * Functions: strlen(user_id)
 *
 * Expressions are frequently built on top of one another, i.e.
 * field expressions will be used within comparisons, which may be
 * used within a conjunction
 */
interface FQLExpression {

  /**
   * Sets the scope of the expression so that we know what FROM
   * clause to evaluate this expression with respect to (the source table).
   *
   * @param $stmt the FQLStatement that contains this expression
   */
  public function set_scope($stmt);

  /**
   * Evaluate the expression for this value of $id.
   *
   * @param  $id the id of the object being evaluated
   *             (user id, object id, etc)
   * @return result of evaluation, type determined by the expression type
   */
  public function evaluate($id);

  /**
   * Get the name of the expression.
   *
   * @return name of expression (field name if expression represents
   *         an FQLField, or "anon")
   */
  public function get_name();

  /**
   * Get a list of query expressions that convey the constraints contained
   * in this expression on the values for the index columns of the table.
   *
   * Query expressions are generally formatted as value:value:value:... where
   * each value represents a value for that table's indexable column. So
   * the query format is determined by the number of indexable columns in the
   * table; 1 index column means the queries are just single values, with ':'
   * as a wildcard value.  2 index columns means the queries are value:value,
   * and anytime that a value is empty, it's considered a wildcard. So a query
   * of :17 would mean that the first column value can be anything, but the second
   * indexable column value must be 17.
   *
   * Simple example: User table, indexed by uid
   * A comparison expression like "uid = 65" would have "65" as a query
   * A comparison expression like "name = Mary" would have ":" as a query
   *  because the field is not indexable
   * A disjunction of "(uid = 65) OR (uid = 5)" would have "65" and "5" as queries
   *
   * More complicated example: Friends table, indexed by uid1, uid2
   * A comparison expression like "uid1 = 65" would have "65:" as a query
   * A comparison expression like "uid2 = 17" would have ":17" as a query
   * A disjunction like "(uid1 = 65) OR (uid2 = 17)" would have "65:" and ":17" as queries
   * A conjunction like "(uid1 = 65) AND (uid2 = 17)" would have "65:17" as a query
   * A conjunction like "(uid1 = 65) AND (uid1 = 17)" would have no queries, because
   *  it's impossible
   *
   * @return an associative array of queries where the keys are the queries
   *         and they all map to 1.
   */
  public function get_queries();

  /**
   * For performance, prime the cache with the appropriate data needed
   * to evaluate this expression for this set of ids.
   *
   * @param $ids a list of ids to retrieve the data for
   */
  public function prime_cache($ids);

  /**
   * Returns a normalized string form of the expression.
   *
   * @return string representation of the expression
   */
  public function to_string();
}

/**
 * This just implements the basics of an expression to save a lot of repetition.
 * Most expression types will want to extend this and implement the missing functions:
 *   public function evaluate($id);
 */
abstract class FQLBaseExpression implements FQLExpression {
  public $scope = null;
  public function set_scope($scope) {
    $this->scope = $scope;
  }
  public function get_queries() {
    return array(':'=>1);
  }
  public function prime_cache($ids) {
  }
  public function get_name() {
    return 'anon';
  }

  /**
   * Static utility function for comparing field values contained within
   * structs, e.g. current_location.zip.
   * FQLFieldExpression, when evaluated for current_location.zip, would
   * return an object with exactly one value populated (the zip code).
   * However, in order to perform comparisons properly (like
   * current_location.zip = 90210), this function will return
   * the value of the only element in the object if there is only one,
   * otherwise it will return the whole object, so that a comparison
   * like "current_location.zip = 90210" will succeed but
   * "current_location = 90210" would fail.
   *
   * @param $obj an object representing the evaluation of an FQLFieldExpression
   * @return returns $obj if $obj contains more than one element, otherwise
   *         returns the value of the only element in $obj
   */
  protected static function get_scalar_value($obj) {
    $result = null;
    foreach ($obj as $val) {
      if (isset($val)) {
        if (isset($result)) {
          return $obj;
        } else {
          $result = $val;
        }
      }
    }
    return $result;
  }
}

/**
 * Class that represents a constant expression contained in
 * an FQL query (e.g. 3)
 */
class FQLConstantExpression extends FQLBaseExpression {
  public $value = null;

  /**
   * Constructs a constant expression with this value.
   *
   * @param $value value of the constant expression
   */
  public function __construct($value) {
    $this->value = $value;
  }

  /**
   * Evaluates the constant expression for this $id,
   * which is always just its value.
   *
   * @param  $id  param is ignored
   * @return returns the value of the constant expression
   */
  public function evaluate($id) {
    return $this->value;
  }

  /**
   * Returns a string representation of a constant expression.
   *
   * @return always returns 'N'
   */
  public function to_string() {
    return 'N';
  }
}

/**
 * Abstract class that represents an expression with two operands
 * contained in an FQL query.
 */
abstract class FQLBinOpExpression extends FQLBaseExpression {
  public $left = null;
  public $right = null;

  /**
   * Constructs a binary expression with two operands.
   *
   * @param $left  FQLExpression that is the left operand
   * @param $right FQLExpression that is the right operand
   */
  public function __construct($left, $right) {
    $this->left = $left;
    $this->right = $right;
  }

  /**
   * Primes the cache for this set of ids by priming the cache
   * for each operand.
   *
   * @param $ids  list of ids to prime cache for
   */
  public function prime_cache($ids) {
    $this->left->prime_cache($ids);
    $this->right->prime_cache($ids);
  }

  /**
   * Sets the scope for this expression by setting the
   * scope for each of the operands.
   *
   * @param $stmt  FQLStatement this expression is contained in.
   */
  public function set_scope($stmt) {
    $this->left->set_scope($stmt);
    $this->right->set_scope($stmt);
    parent::set_scope($stmt);
  }
}

/**
 * Abstract class that represents an expression with
 * multiple operands (potentially more than two) contained
 * in an FQL query.
 */
abstract class FQLMultiOpExpression extends FQLBaseExpression {
  public $operands;

  /**
   * Constructs an expression with any number of operands.
   * Function takes variable number of args, each arg
   * will be stored as an operand.
   *
   * @param Any number of args representing the operands for
   *        this expression
   */
  public function __construct() {
    $this->operands = func_get_args();
  }

  /**
   * Primes the cache for this set of ids by priming the cache
   * for each operand.
   *
   * @param $ids  list of ids to prime cache for
   */
  public function prime_cache($ids) {
    foreach ($this->operands as $expr) {
      $expr->prime_cache($ids);
    }
  }

  /**
   * Sets the scope for this expression by setting the
   * scope for each of the operands.
   *
   * @param $stmt  FQLStatement this expression is contained in.
   */
  public function set_scope($stmt) {
    foreach ($this->operands as $expr) {
      $expr->set_scope($stmt);
    }
    parent::set_scope($stmt);
  }

  /**
   * Adds another operand to this expression.
   *
   * @param $expr  FQLExpression to append as an operand
   */
  public function append($expr) {
    $this->operands[] = $expr;
  }
}

/**
 * Class that represents a field expression value that can't be
 * evaluated for a particular $id due to visibility permissions.
 */
class FQLCantSee { }

/**
 * Class that represents a field expression contained in
 * an FQL query.  A field expression is represented in the
 * query as the name of a field from an FQL table (e.g. gender).
 */
class FQLFieldExpression extends FQLBaseExpression {
  // FQLField object for the field this represents
  public $field;

  // string name of the field
  private $field_name;

  // array of more specific field names for fields that represent
  // structs (e.g. current_location), this might contain zipcode
  private $other_fields;

  /**
   * Constructs a field expression with the given field name.
   * Accepts field names representing structs using dot notation
   * (e.g. 'current_location.zipcode')
   *
   * @param  $field_name
   */
  public function __construct($field_name) {
    $this->other_fields = explode('.', $field_name);
    $this->field_name = array_shift($this->other_fields);
  }

  /**
   * Sets the scope for this field expression. Performs validity
   * check on the field name by checking if the field name is
   * contained in the source table. Initializes the appropriate
   * field object for the field name for this table.
   *
   * @param  $scope FQLStatement that this expression is
   *                contained in
   *
   * Throws UnknownFieldException if this field name is not
   * a valid field in the source table.
   */
  public function set_scope($scope) {
    $fields = $scope->from_table->get_fields();
    if (isset($fields[$this->field_name])) {
      $this->field = new $fields[$this->field_name]
        ($scope->user, $scope->app_id, $scope->from_table, $this->field_name);
    } else {
      throw new UnknownFieldException($this->field_name, $scope->from, 'table');
    }
    parent::set_scope($scope);
  }

  /**
   * Primes cache for this set of ids by priming the cache data for this field.
   *
   * @param $ids set of ids to prime the cache for
   */
  public function prime_cache($ids) {
    $this->field->prime_cache($ids);
  }

  /**
   * Static utility function that strips subfields from a field
   * expression that represents a struct.
   *
   * For instance, when the field expression is current_location.zip,
   * this function will strip out all other subfields (city, region, etc)
   * from current_location.
   *
   * @param  $result  result of evaluation of a field expression
   *                  passed by reference so that modifications to strip
   *                  unnecessary fields will be preserved
   * @param  $fields  array of field names representing the subfield
   *                  progression for this field expression.
   *                  for a field like: schools.school.name, the $fields
   *                  would be array('school', 'name')
   * @param  $context the root field name ('schools' in the above example)
   *
   * Throws UnknownFieldException if it encounters a subfield name that
   * does not exist in the structure (e.g. schools.zipcode)
   */
  private static function strip_nonmatching_fields(&$result, $fields, $context) {
    if (empty($fields)) {
    // no subfields, so nothing needs to be done
      return;
    }
    if (is_array($result)) {
      foreach ($result as $result_elt) {
        // call recursively on each structure in the list
        self::strip_nonmatching_fields($result_elt, $fields, $context);
      }
    } else if (is_object($result)) {
      // get first subfield name
      $field = array_shift($fields);
      if (!array_key_exists($field, $result)) {
        // subfield name was not in structure, invalid subfield
        throw new UnknownFieldException($field, $context, 'object');
      }
      // strip nonmatching subfields in the structure
      foreach ($result as $name=>$value) {
        if ($name != $field) {
          unset($result->$name);
        } else {
          // call recursively on the matching subfield
          self::strip_nonmatching_fields($value, $fields, $field);
        }
      }
      return;
    } else {
      // trying to access subfield on a field that just returns a
      // string or int
      throw new UnknownFieldException($fields[0], $context, 'scalar');
    }
  }

  /**
   * Evaluates the field expression for this $id. Essentially equivalent
   * to looking up the value of this field for this id in the source table.
   *
   * @param  $id value to look up in the table for this column
   * @return value for this field for this id
   *         if permissions do not allow this lookup to occur, returns FQLCantSee
   */
  public function evaluate($id) {
    if ($this->field->can_see($id)) {
      $result = $this->field->evaluate($id);
      self::strip_nonmatching_fields($result, $this->other_fields, $this->field_name);
      return $result;
    } else {
      return new FQLCantSee();
    }
  }

  /**
   * Returns the name of the field that this expression represents.
   *
   * @return field name
   */
  public function get_name() {
    return $this->field_name;
  }

  /**
   * Returns a string representation of the field expression.
   *
   * @return  string representation of the field expression
   *          e.g. "id" or "current_location.zip"
   */
  public function to_string() {
    return $this->field_name .
      ($this->other_fields ? '.' . implode('.', $this->other_fields) : '');
  }
}

