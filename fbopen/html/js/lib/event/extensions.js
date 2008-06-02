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
 *
 *  @provides event-extensions
 */

/**
 *  Chain two or more event handlers together, returning a function that calls
 *  them in sequence. Note that these functions are treated like event
 *  functions: if one of them returns a strict `false', execution will abort and
 *  subsequent functions WILL NOT be called.
 *
 *  The common use case is making sure you don't overwrite existing event
 *  handlers:
 *
 *  <js>
 *    button.onclick = chain(button.onclick, additionalHandler);
 *  </js>
 *
 *  It is safe to pass `null' values to chain, so it's probably not a bad idea
 *  to use this idiom generally when performing event assignments.
 *
 *  @params Zero or more functions to chain together
 *
 *  @return A function which executes the arguments in order, aborting if any
 *          return a strict `false'. This function will return `false' to
 *          indicate that some component function aborted event bubbling, or
 *          `true' to indicate that all functions executed.
 *
 *  @author epriestley
 */
function chain( u, v /*, w, x ... */ ) {

  var calls = [];
  for (var ii = 0; ii < arguments.length; ii++) {
    calls.push(arguments[ii]);
  }

  return function( ) {
    for (var ii = 0; ii < calls.length; ii++) {
      if ( calls[ii] && calls[ii].apply( this, arguments ) === false ) {
        return false;
      }
    }
    return true;
  }

}


// === Event Attaching ===
// (see: http://www.quirksmode.org/blog/archives/2005/10/_and_the_winner_1.html)

// why name_hash? So you can use the same function and pass different name_hashes and ie won't get confused
function addEventBase(obj, type, fn, name_hash)
{
  if (obj.addEventListener) {
    obj.addEventListener( type, fn, false );
  }
  else if (obj.attachEvent)
  {
    var fn_name = type+fn+name_hash;
    obj["e"+fn_name] = fn;
    obj[fn_name] = function() { obj["e"+fn_name]( window.event ); }
    obj.attachEvent( "on"+type, obj[fn_name] );
  }

  return fn;

}

function removeEventBase(obj, type, fn, name_hash)
{
  if (obj.removeEventListener) {
    obj.removeEventListener( type, fn, false );
  }
  else if (obj.detachEvent)
  {
    var fn_name = type+fn+name_hash;
    if (obj[fn_name]) {
      obj.detachEvent( "on"+type, obj[fn_name]);
      obj[fn_name] = null;
      obj["e"+fn_name] = null;
    }
  }
}



// for IE
function event_get(e) {
  return e || window.event;
}

/**
 *  @browser Safari, Firefox
 *    Event target is in `target'.
 *
 *  @browser IE
 *    Event target is in `srcElement'.
 */
function event_get_target(e) {
  return (e = event_get(e)) && (e['target'] || e['srcElement']);
}

function event_abort(e) {
  (e = event_get(e)) && (e.cancelBubble = true) &&
    e.stopPropagation && e.stopPropagation();
  return false;
}

function event_prevent(e) {
  (e = event_get(e)) && !(e.returnValue = false) &&
    e.preventDefault && e.preventDefault();
  return false;
}

function event_kill(e) {
  return event_abort(e) || event_prevent(e);
}

function event_get_keypress_keycode(event) {
  event = event_get(event);
  if (!event) {
    return false;
  }
  switch (event.keyCode) {
    case 63232: // up
      return 38;
    case 63233: // down
      return 40;
    case 63234: // left
      return 37;
    case 63235: // right
      return 39;
    case 63272: // delete
    case 63273: // home
    case 63275: // end
      return null; // IE doesn't support these so they shouldn't be used
    case 63276: // page up
      return 33;
    case 63277: // page down
      return 34;
  }
  if (event.shiftKey) {
    switch (event.keyCode) {
      case 33: // page up
      case 34: // page down
      case 37: // left
      case 38: // up
      case 39: // right
      case 40: // down
        return null; // "!" (and others) can not be detected with this abstraction,
                     // but there will never be a false position on arrow keys
    }
  } else {
    return event.keyCode;
  }
}

function stopPropagation(e) {
    if (!e) var e = window.event;
    e.cancelBubble = true;
    if (e.stopPropagation) {
        e.stopPropagation();
    }
}

