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
 *  We implement a bunch of enhancements to Array.prototype, many of which are
 *  defined in the quasi-official "JavaScript 1.6" specification here:
 *
 *    http://developer.mozilla.org/en/docs/New_in_JavaScript_1.6
 *
 *  Specifically, we offer implementations of Array.map(), Array.forEach() (also
 *  aliased as Array.each()), Array.filter(), Array.every(), Array.some(),
 *  and Array.indexOf().
 *
 *  This is `quasi-official' because Mozilla owns JavaScript but browsers
 *  implement whatever they want, and these extensions have yet to be officially
 *  recognized by ECMA or implemented in JScript, etc. Basically, there are
 *  native implementations available in Firefox which we could fall back to for
 *  speed, and other browsers may implement these methods natively in the
 *  future.
 *
 *  These enhancments are only "mostly" compatible with the JavaScript 1.6
 *  specification; they will raise a TypeError if called with `window' bound as
 *  `this' as a security enhancement for FBJS, and will allocate return values
 *  using `this.alloc(N)', not "new Array(N)" which means the return type
 *  depends on the caller. Other deviations (which are minor) are noted below.
 *
 *  These implementations are not optimized.
 *
 *  @author   epriestley
 *  @provides array-extensions
 */


/**
 *  If a class psuedo-extends Array, it can overload this method to make all the
 *  Array extensions that return arrays return objects of the subclass instead.
 *  See List for a more concrete example of this.
 */
Array.prototype.alloc = function(length) {
  return length ? new Array(length) : [];
}


/**
 *  This function conforms to the JavaScript 1.6 specification.
 */
Array.prototype.map = function(callback, thisObject) {
  if (this == window) {
    throw new TypeError();
  }

  if (typeof(callback) !== "function") {
    throw new TypeError();
  }

  var ii;
  var len = this.length;
  var r   = this.alloc(len);
  for (ii = 0; ii < len; ++ii) {
    if (ii in this) {
      r[ii] = callback.call(thisObject, this[ii], ii, this);
    }
  }

  return r;
};


/**
 *  This function deviates from the Javascript 1.6 specification: it returns
 *  the calling array, not void.
 */
Array.prototype.forEach = function(callback, thisObject) {
  this.map(callback, thisObject);
  return this;
};


/**
 *  This function deviates from the Javascript 1.6 specification: it returns
 *  the calling array, not void.
 */
Array.prototype.each    = function(callback, thisObject) {
  return this.forEach.apply(this, arguments);
}


/**
 *  This function conforms to the JavaScript 1.6 specification.
 */
Array.prototype.filter = function(callback, thisObject) {
  if (this == window) {
    throw new TypeError();
  }

  if (typeof(callback) !== "function") {
    throw new TypeError();
  }

  var ii, val, len = this.length, r = this.alloc();
  for (ii = 0; ii < len; ++ii) {
    if (ii in this) {
      //  Specified, to prevent mutations in the original array.
      val = this[ii];
      if (callback.call(thisObject, val, ii, this)) {
        r.push(val);
      }
    }
  }

  return r;
};


/**
 *  This function deviates from the JavaScript 1.6 specification: it does not
 *  guarantee how many times the callback will be invoked.
 */
Array.prototype.every = function(callback, thisObject) {
  return (this.filter(callback, thisObject).length == this.length);
}


/**
 *  This function deviates from the JavaScript 1.6 specification: it does not
 *  guarantee how many times the callback will be invoked.
 */
Array.prototype.some = function(callback, thisObject) {
  return (this.filter(callback, thisObject).length > 0);
}


/**
 *  This is an object-aware mapper similar to Array.map(). The difference
 *  between the traditional methods (map, each, filter) and the pull methods
 *  (pull, pullEach, pullFilter) is that the pull methods treat the array
 *  as a list of objects and the callback as a method to apply to the objects.
 *
 *  For instance, you can Array.pull() a list of strings with the expected
 *  result:
 *
 *    ['zebra', 'pancake'].pull(''.toUpperCase);
 *
 *  Using map() would be more cumbersome and requires creation of an anonymous
 *  function:
 *
 *    ['zebra', 'pancake'].pull(function(s) { return s.toUpperCase(); });
 *
 *  While map() is ultimately more versatile, pull() can express some maps
 *  more succinctly.
 *
 *  @author epriestley
 */
Array.prototype.pull = function(callback /*, args */) {
  if (this == window) {
    throw new TypeError();
  }

  if (typeof(callback) !== "function") {
    throw new TypeError();
  }

  var args  = Array.prototype.slice.call(arguments, 1);
  var len   = this.length;
  var r     = this.alloc(len);

  for (ii = 0; ii < len; ++ii) {
    if (ii in this) {
      r[ii] = callback.apply(this[ii], args);
    }
  }

  return r;
}

Array.prototype.pullEach = function(callback /*, args */) {
  this.pull.apply(this, arguments);
  return this;
}

Array.prototype.filterEach = function(callback /*, args */) {
  var map = this.pull.apply(this, arguments);
  var len = this.length;
  var r   = this.alloc();

  for (var ii = 0; ii < len; ++ii) {
    if (ii in this) {
      r.push(this[ii]);
    }
  }

  return r;
}


//  These methods are present in some browsers and unsafe. They are not
//  generally useful; we simply remove them rather than providing safe
//  implementations.

Array.prototype.reduce      = null;
Array.prototype.reduceRight = null;


//  These methods are unsafe but highly useful; we reimplement them in terms
//  of themselves with FBJS safety.

Array.prototype.sort = (function(sort) { return function(callback) {
  return (this == window) ? null : (callback ? sort.call(this, function(a,b) {
    return callback(a,b)}) : sort.call(this));
}})(Array.prototype.sort);

Array.prototype.reverse = (function(reverse) { return function() {
  return (this == window) ? null : reverse.call(this);
}})(Array.prototype.reverse);

Array.prototype.concat = (function(concat) { return function() {
  return (this == window) ? null : concat.apply(this, arguments);
}})(Array.prototype.concat);

Array.prototype.slice = (function(slice) { return function() {
  return (this == window) ? null : slice.apply(this, arguments);
}})(Array.prototype.slice);


//  Redefine Array.clone() in terms of (safe) Array.slice().

Array.prototype.clone = Array.prototype.slice;


//  This is a Javascript 1.6 function which we implement using the native
//  version if it is available.

if (Array.prototype.indexOf) {
  Array.prototype.indexOf = (function(indexOf) {
    return function(val, index) {
      return (this == window) ? null : indexOf.apply(this, arguments);
    }
  })(Array.prototype.indexOf);
} else {
  /**
   *  This function conforms to the JavaScript 1.6 specification.
   */
  Array.prototype.indexOf = function(val, index) {
    if (this == window) {
      throw new TypeError();
    }

    var len = this.length;
    var from = Number(index) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);

    if (from < 0) {
      from += len;
    }

    for (; from < len; from++) {
      if (from in this && this[from] === val) {
        return from;
      }
    }
    return -1;
  };
}
