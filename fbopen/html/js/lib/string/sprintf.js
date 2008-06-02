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
 *  @requires util string-escape
 *  @provides sprintf
 */

/**
 *  Limited implementation of sprintf. Conversions:
 *    %s  A string, which will be HTML escaped.
 *    %d  An integer.
 *    %f  A floating point number.
 *    %q  A quoted string. Like %s, but puts (pretty) quotes around the output.
 *        This is purely a display conversion, it does not render the string
 *        appropriate for output in any specific context. Use %e to generate an
 *        escaped string.
 *    %e  An excaped string which you can embed into HTML as a JS parameter. For
 *        example:
 *
 *          sprintf( '<a onclick="aiert(%e);">see message</a>', msg );
 *
 *    %h  An HTML string; it will not be escaped.
 *    %x  An exception.
 *
 *  This "sprintf" now attempts to support some of the fancy options of real
 *  sprintf(), like "%'Q8.8d" to produce a string like "QQQQQQ35". Any
 *  behavioral differences between this sprintf() and real sprintf() should be
 *  considered bugs or deficiencies in this implementation.
 *
 *  These thing still don't work:
 *    - min/max arguments as applied to floating point numbers
 *    - using a '*' for length
 *    - esoteric conversions
 *    - weird positive/negative number formatting
 *    - argument swapping
 *
 *  @author epriestley
 */
function sprintf( ) {

  if (arguments.length == 0) {
    Util.warn(
      'sprintf() was called with no arguments; it should be called with at '   +
      'least one argument.');
    return '';
  }

  var args = [ 'This is an argument vector.' ];
  for ( var ii = arguments.length - 1; ii > 0; ii-- ) {
    if ( typeof( arguments[ii] ) == "undefined" ) {
      Util.log(
        'You passed an undefined argument (argument '+ii+' to sprintf(). '     +
        'Pattern was: `'+(arguments[0])+'\'.',
        'error');
      args.push('');
    } else if (arguments[ii] === null) {
      args.push('');
    } else if (arguments[ii] === true) {
      args.push('true');
    } else if (arguments[ii] === false) {
      args.push('false');
    } else {
      if (!arguments[ii].toString) {
        Util.log(
          'Argument '+(ii+1)+' to sprintf() does not have a toString() '       +
          'method. The pattern was: `'+(arguments[0])+'\'.',
          'error');
        return '';
      }
      args.push(arguments[ii]);
    }
  }

  var pattern = arguments[0];
  pattern = pattern.toString().split('%');
  var patlen = pattern.length;
  var result = pattern[0];
  for (var ii = 1; ii < patlen; ii++) {

    if (args.length == 0) {
      Util.log(
        'Not enough arguments were provide to sprintf(). The pattern was: '    +
        '`'+(arguments[0])+'\'.',
        'error');
      return '';
    }

    if (!pattern[ii].length) {
      result += "%";
      continue;
    }

    var p = 0;
    var m = 0;

    var r = '';

    var padChar  = ' ';
    var padSize  = null;
    var maxSize  = null;
    var rawPad   = '';
    var pos = 0;

    if (m = pattern[ii].match(/^('.)?(?:(-?\d+\.)?(-?\d+)?)/)) {

      if (m[2] !== undefined && m[2].length) {
        padSize = parseInt(rawPad = m[2]);
      }

      if (m[3] !== undefined && m[3].length) {
        if (padSize !== null) {
          maxSize = parseInt(m[3]);
        } else {
          padSize = parseInt(rawPad = m[3]);
        }
      }

      pos = m[0].length;

      if (m[1] !== undefined && m[1].length) {
        padChar = m[1].charAt(1);
      } else {
        if (rawPad.charAt(0) == 0) {
          padChar = '0';
        }
      }
    }

    switch (pattern[ii].charAt(pos)) {
      // A string.
      case 's':
        raw = htmlspecialchars(args.pop( ).toString( ));
        break;
      // HTML.
      case 'h':
        raw = args.pop( ).toString( );
        break;
      // An integer.
      case 'd':
        raw = parseInt(args.pop( )).toString();
        break;
      // A float.
      case 'f':
        raw = parseFloat(args.pop( )).toString();
        break;
      // A quoted something-or-other.
      case 'q':
        raw = "`" + htmlspecialchars(args.pop( ).toString( ))+ "'";
        break;
      // A string parameter.
      case 'e':
        raw = "'" + escape_js_quotes(args.pop( ).toString( )) + "'";
        break;
      // A list parameter.
      case 'L':
        var list = args.pop( );
        for (var ii = 0; ii < list.length; ii++) {
          list[ii] = "`" + htmlspecialchars(args.pop( ).toString( ))+ "'";
        }
        if (list.length > 1) {
          list[list.length - 1] = 'and ' + list[list.length - 1];
        }
        raw = list.join(', ');
        break;
      // An exception.
      case 'x':
        x = args.pop();

        var line = '?';
        var src  = '?';

        try {

          if (typeof(x['line']) != 'undefined') {
            line = x.line;
          } else if (typeof(x['lineNumber']) != 'undefined') {
            line = x.lineNumber;
          }

          if (typeof(x['sourceURL']) != 'undefined') {
            src = x['sourceURL'];
          } else if (typeof(x['fileName']) != 'undefined') {
            src = x['fileName'];
          }

        } catch (exception) {

          //  Ignore the exception; it just means we're trying to get properties
          //  of some "magic" object which resists property access. For one
          //  example of such an object, do:
          //
          //    document.appendChild('some_string')
          //
          //  ...in Firefox. Specifically, Firefox will throw an "exception"
          //  which throws another exception when you try to access its
          //  lineNumber. Good job, Firefox. You're one heckuva browser.

        }

        var s = '[An Exception]';
        try {
          s = x.message || x.toString( );
        } catch (exception) {
          //  Don't care.
        }

        raw = s + ' [at line ' + line + ' in ' + src + ']';
        break;
      // Something we don't recognize.
      default:
        raw = "%" + pattern[ii].charAt(pos+1);
        break;
    }

    if (padSize !== null) {
      if (raw.length < Math.abs(padSize)) {
        var padding = '';
        var padlen  = (Math.abs(padSize)-raw.length);
        for (var ll = 0; ll < padlen; ll++) {
          padding += padChar;
        }

        if (padSize < 0) {
          raw += padding;
        } else {
          raw = padding + raw;
        }
      }
    }

    if (maxSize !== null) {
      if (raw.length > maxSize) {
        raw = raw.substr(0, maxSize);
      }
    }

    result += raw + pattern[ii].substring(pos+1);
  }

  if ( args.length > 1 ) {
    Util.log(
      'Too many arguments ('+(args.length-1)+' extras) were passed to '        +
      'sprintf(). Pattern was: `'+(arguments[0])+'\'.',
      'error');
   }

  return result;
}
