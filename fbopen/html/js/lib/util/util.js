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
 *  @requires sprintf string-extensions
 *  @provides util env
 */


function env_get(k) {
  return typeof(window['Env']) != 'undefined' && Env[k];
}



var Util = {

  fallbackErrorHandler : function(msg) {
    aiert(msg);
  },

  isDevelopmentEnvironment : function( ) {
    return env_get('dev');
  },

  warn : function( ) {
    Util.log(sprintf.apply(null, arguments), 'warn');
  },

  error : function( ) {
    Util.log(sprintf.apply(null, arguments), 'error');
  },

  log : function( msg, type ) {
    if (Util.isDevelopmentEnvironment( )) {

      var written = false;

      if (typeof(window['TabConsole']) != 'undefined') {
        var con = TabConsole.getInstance( );
        if (con) {
          con.log(msg, type);
          written = true;
        }
      }

      if (typeof(console) != "undefined" && console.error) {
        console.error(msg);
        written = true;
      }

      if (!written && type != 'deprecated' && Util.fallbackErrorHandler) {
        Util.fallbackErrorHandler(msg);
      }

    } else {
      if (type == 'error') {
        msg += '\n\n' + Util.stack();
        (typeof(window['Env']) != 'undefined') &&
        (Env.rlog) &&
        (typeof(window['debug_rlog']) == 'function') &&
        debug_rlog(msg);
      }
    }
  },

  deprecated : function(what) {
    if (!Util._deprecatedThings[ what ]) {
      Util._deprecatedThings[ what ] = true;

      var msg = sprintf(
        'Deprecated: %q is deprecated.\n\n%s',
        what,
        Util.whyIsThisDeprecated(what));

      Util.log(msg, 'deprecated');
    }
  },

  stack : function() {
    try {
      try {
        // Induce an error
        ({}).llama();
      } catch(e) {
        // If e.stack exists it's probably Firefox and there's a nice stack trace with line numbers waiting for us
        if (e.stack) {
          var stack = [];
          var trace = [];
          var regex = /^([^@]+)@(.+)$/mg;
          var line = regex.exec(e.stack);
          do {
            stack.push([line[1], line[2]]);
          } while (line = regex.exec());
          for (var i = 0; i < stack.length; i++) {
            trace.push('#' + i + ' ' + stack[i][0] + ' @ ' + (stack[i+1] ? stack[i+1][1] : '?'));
          }
          return trace.join('\n');
        // Otherwise we have to build our own...
        } else {
          var trace = [];
          var pos = arguments.callee;
          var stale = [];
          while (pos) {
            // Check to make sure we're not caught in a loop here...
            for (var i = 0; i < stale.length; i++) {
              if (stale[i] == pos) {
                trace.push('#' + trace.length + ' ** recursion ** @ ?');
                return trace.join('\n');
              }
            }
            stale.push(pos);

            // Convert the arguments into a string
            var args = [];
            for (var i = 0; i < pos.arguments.length; i++) {
              if (pos.arguments[i] instanceof Function) {
                var func = /function ?([^(]*)/.exec(pos.arguments[i].toString()).pop();
                args.push(func ? func : 'anonymous');
              } else if (pos.arguments[i] instanceof Array) {
                args.push('Array');
              } else if (pos.arguments[i] instanceof Object) {
                args.push('Object');
              } else if (typeof pos.arguments[i] == 'string') {
                args.push('"' + pos.arguments[i].replace(/("|\\)/g, '\\$1') + '"');
              } else {
                args.push(pos.arguments[i]);
              }
            }
            trace.push('#' + trace.length + ' ' + /function ?([^(]*)/.exec(pos).pop() + '(' + args.join(', ') + ') @ ?');
            if (trace.length>100)break;
            pos = pos.caller;
          }
          return trace.join('\n');
        }
      }
    } catch(e) {
      return 'No stack trace available';
    }
  },

  whyIsThisDeprecated : function(what) {
    return Util._deprecatedBecause[what.toLowerCase( )] ||
          'No additional information is available about this deprecation.';
  },

  _deprecatedBecause : {},
  _deprecatedThings  : {}
};
