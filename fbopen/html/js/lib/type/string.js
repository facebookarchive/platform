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
 *  @provides string-extensions
 */

String.prototype.trim = function() {
  if (this == window) {
    return null;
  }
  return this.replace(/^\s*|\s*$/g, '');
}

function trim(text) {
  return String(text).trim();
}

String.prototype.startsWith = function(substr) {
  if (this == window) {
    return null;
  }
  return this.substring(0, substr.length) == substr;
};

//----------------------------------------------------------------------------------------------
/* Cross-Browser Split v0.1; MIT-style license
By Steven Levithan <http://stevenlevithan.com>
An ECMA-compliant, uniform cross-browser split method */
/* several modifications by marcel for performance. he loves MIT licenses. */
String.prototype.split = (function(split) {
  return function(separator, limit) {
  var flags = "";

  /* Behavior for separator: If it's...
  - Undefined: Return an array containing one element consisting of the entire string
  - A regexp or string: Use it
  - Anything else: Convert it to a string, then use it */
  if (separator === null || limit === null) {
    return [];
  } else if (typeof separator == 'string') {
    return split.call(this, separator, limit);
  } else if (separator === undefined) {
    return [this.toString()]; // toString is used because the typeof this is object
  } else if (separator instanceof RegExp) {

    if (!separator._2 || !separator._1) {
      flags = separator.toString().replace(/^[\S\s]+\//, "");
      if (!separator._1) {
        if (!separator.global) {
          separator._1 = new RegExp(separator.source, "g" + flags);
        } else {
          separator._1 = 1;
        }
      }
    }
    separator1 = separator._1 == 1 ? separator : separator._1;

    // Used for the IE non-participating capturing group fix
    var separator2 = (separator._2 ? separator._2 : separator._2 = new RegExp("^" + separator1.source + "$", flags));

    /* Behavior for limit: If it's...
    - Undefined: No limit
    - Zero: Return an empty array
    - A positive number: Use limit after dropping any decimal value (if it's then zero, return an empty array)
    - A negative number: No limit, same as if limit is undefined
    - A type/value which can be converted to a number: Convert, then use the above rules
    - A type/value which cannot be converted to a number: Return an empty array */
    if (limit === undefined || limit < 0) {
      limit = false;
    } else {
      limit = Math.floor(limit);
      if (!limit) return []; // NaN and 0 (the values which will trigger the condition here) are both falsy
    }

    var match,
    output = [],
    lastLastIndex = 0,
    i = 0;

    while ((limit ? i++ <= limit : true) && (match = separator1.exec(this))) {
      // Fix IE's infinite-loop-resistant but incorrect RegExp.lastIndex
      if ((match[0].length === 0) && (separator1.lastIndex > match.index)) {
        separator1.lastIndex--;
      }

      if (separator1.lastIndex > lastLastIndex) {
        /* Fix IE to return undefined for non-participating capturing groups (NPCGs). Although IE
        incorrectly uses empty strings for NPCGs with the exec method, it uses undefined for NPCGs
        with the replace method. Conversely, Firefox incorrectly uses empty strings for NPCGs with
        the replace and split methods, but uses undefined with the exec method. Crazy! */
        if (match.length > 1) {
          match[0].replace(separator2, function() {
            for (var j = 1; j < arguments.length - 2; j++) {
              if (arguments[j] === undefined) match[j] = undefined;
            }
          });
        }

        output = output.concat(this.substring(lastLastIndex, match.index), (match.index === this.length ? [] : match.slice(1)));
        lastLastIndex = separator1.lastIndex;
      }

      if (match[0].length === 0) {
        separator1.lastIndex++;
      }
    }

    return (lastLastIndex === this.length)
         ? (separator1.test("") ? output : output.concat(""))
         : (limit ? output : output.concat(this.substring(lastLastIndex)));
  } else {
    return split.call(this, separator, limit); // this should probably never happen...
  }
}})(String.prototype.split);


