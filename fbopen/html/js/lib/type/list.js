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
 *  This is a generic "Array-like" object which implements some of Array's
 *  behavior. We need to do this because IE will fatally break anything which
 *  extends Array; Dean Edwards has a more in-depth explanation:
 *
 *    http://dean.edwards.name/weblog/2006/11/hooray/
 *
 *  We avoid the iframe magic because the cost of building our own Array-like
 *  object is not high; we lose some Array behaviors like being able to assign
 *  to an arbitrary index and get an array that long, but these are a small
 *  price to pay. This is similar to jQuery's approach.
 *
 *  Basically, we need to keep track of length ourselves and can mostly fall
 *  back to Array to do anything even mildly interesting.
 *
 *  @author   epriestley
 *
 *  @requires array-extensions
 *  @provides list
 */

function /* class */ List(length) {
  if (arguments.length > 1) {
    for (var ii = 0; ii < arguments.length; ii++) {
      this.push(arguments[ii]);
    }
  } else {
    this.resize(length || 0);
  }
}

List.prototype.length = 0;
List.prototype.size = function() {
  return this.length;
}

List.prototype.resize = function(new_size) {
  this.length = new_size;
  return this;
}

List.prototype.push = function(element) {
  this.length += arguments.length;
  return Array.prototype.push.apply(this, arguments);
}

List.prototype.pop = function() {
  --this.length;
  return Array.prototype.pop.apply(this);
}

List.prototype.alloc = function(n) {
  return new List(n);
}

//  Pull in all the Array behaviors we're interested in.

List.prototype.map        = Array.prototype.map;
List.prototype.forEach    = Array.prototype.forEach;
List.prototype.each       = Array.prototype.each;
List.prototype.filter     = Array.prototype.filter;
List.prototype.every      = Array.prototype.every;
List.prototype.some       = Array.prototype.some;
List.prototype.pull       = Array.prototype.pull;
List.prototype.pullEach   = Array.prototype.pullEach;
List.prototype.pullFilter = Array.prototype.pullFilter;