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

/**
 *  Delete troublesome Object properties and provide some helper functions.
 *
 *  @author marcel
 *
 *  @provides object-extensions
 */

// Safety for FBJS.
if (Object.prototype.eval) {
  window.eval = Object.prototype.eval;
}
delete Object.prototype.eval;     // silly Mozilla
delete Object.prototype.valueOf;  // sorry, use Object.valueOf instead

function is_scalar(v) {

  switch (typeof(v)) {
    case 'string':
    case 'number':
    case 'null':
    case 'boolean':
      return true;
  }

  return false;
}

function is_empty(obj) {
  for (var i in obj) {
    return false;
  }
  return true;
}

function object_keys(obj) {
  var keys = [];
  for (var i in obj) {
    keys.push(i);
  }
  return keys;
}

function object_values(obj) {
  var values = [];
  for (var i in obj) {
    values.push(obj[i]);
  }
  return values;
}

function object_key_count(obj) {
  var count = 0;
  for (var i in obj) {
    count++;
  }
  return count;
}

function are_equal(a, b) {
  return JSON.encode(a) == JSON.encode(b);
}

