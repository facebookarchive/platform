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

/*
 * The contents of prelude.js (stripped of comments and the such) will be
 * included inline within the <head> of each page by start_html.
 *
 * -- CANNOT @PROVIDE OR @REQUIRE ANYTHING --
 */


/**
 * Primitive, dependentless version of onloadRegister, so that onload
 * handlers can get queued up before we make it to *any* script includes.
 *
 * @author jrosenstein
 */
window.onloadRegister = window.onloadRegister ||
  function(h) { window.onloadhooks.push(h); };
window.onloadhooks = window.onloadhooks || [];


/**
 * All DOM handlers in the generated page (e.g. onclick) will get wrapped in:
 *
 *   onXXX="return wait_for_load(this, event, function() { ... });"
 *
 * The effect is that, if the user tries to interact with the element before
 * the document has loaded, then the interaction will either be ignored or,
 * if we can do so reliably, deferred until all script files are done loading.
 *
 * @param element    The element on which the event fired.
 * @param e          The window.event at the time of the event firing.
 * @param f          The (unbound) handler the user wants executed.
 *
 * @author jrosenstein
 */
window.wait_for_load = window.wait_for_load ||
function (element, e, f) {
  f = bind(element, f, e);
  if (window.loading_begun) {
    return f();
  }

  switch ((e || event).type) {

    case 'load':
      onloadRegister(f);
      return;

    case 'click':
      // Change the cursor to give the user some feedback to wait.
      if (element.original_cursor === undefined) {
        element.original_cursor = element.style.cursor;
      }
      if (document.body.original_cursor === undefined) {
        document.body.original_cursor = document.body.style.cursor;
      }
      element.style.cursor = document.body.style.cursor = 'progress';

      onloadRegister(function() {
        element.style.cursor = element.original_cursor;
        document.body.style.cursor = document.body.original_cursor;
        element.original_cursor = document.body.original_cursor = undefined;

        if (element.tagName.toLowerCase() == 'a') {

          // Simulate calling the onclick handler.  Don't re-use f, since
          // the onclick handler could have changed (e.g. via LinkController).
          var original_event = window.event;
          window.event = e;
          var ret_value = element.onclick.call(element, e);
          window.event = original_event;

          // If onclick didn't return false, follow the link.
          if (ret_value !== false && element.href) {
            window.location.href = element.href;
          }

        } else if (element.click) {
          // For form elements (and more in IE).
          element.click();
        }
      });
      break;

  }

  return false;
};


/**
 *  Returns a function which binds the parameter object and method together.
 *
 *  Bind takes two arguments: an object (optionally, null), and a function
 *  (either the explicit function itself, or the name of a function). It binds
 *  them together and returns a function which, when called, calls the passed
 *  function with the passed object bound as `this'. That is, the following
 *  are nearly equivalent (but see below):
 *
 *    obj.method();
 *
 *    var fn2 = bind(obj, 'method');   // Late binding, see below.
 *    fn2();
 *
 *    var fn3 = bind(obj, obj.method); // Early binding, see below.
 *    fn3();
 *
 *  Binding can occur either by name (as with fn2) or by explicit method (as
 *  with fn3). When binding by name, the binding is "late" and resolved at call
 *  time, NOT at bind time:
 *
 *    function A() { return this.name + ' says "A".'; }
 *    function B() { return this.name + ' says "B".'; }
 *
 *    var obj = { name: 'zebra', f: A };
 *
 *    var earlyBind = bind(obj, f);   // Passing method = early binding
 *    var lateBind  = bind(obj, 'f'); // Passing string = late binding
 *
 *    earlyBind(); // A zebra says "A".
 *    lateBind();  // A zebra says "A".
 *
 *    obj.f = B;
 *
 *    earlyBind(); // A zebra says "A".
 *    lateBind();  // A zebra says "B".
 *
 *  One principle advantage of late binding is that you can late-bind an event
 *  handler, and change it without breaking the bindings.
 *
 *  Note that, because late binding isn't resolved until call time, it can also
 *  fail at call time.
 *
 *    var badLateBind = bind({ f: 42 }, 'f');
 *    badLateBind(); // Fatal error, can't call an integer.
 *
 *  Also note that you can not late bind a global function if you provide an
 *  object. This is a design decision that probably has arguments both ways,
 *  but forcing object bindings to always bind within object scope means global
 *  scope can't accidentally bleed into an object, which could be extremely
 *  astonishing.
 *
 *  Additionally, bind() can curry (purists might argue that this is actually
 *  "partial function application", but they can die in a well fire). Currying
 *  binds arguments to the return function:
 *
 *    function add(a, b) { return a + b; }
 *    var add3 = bind(null, add, 3);
 *    add3(4);                  // 7
 *    add3(5);                  // 8
 *    bind(null, add, 2, 3)();  // 5
 *
 *  bind() is also available as a member of Function:
 *
 *    var fn = function() { }.bind(obj);
 *
 *  This version of bind() can also curry, but it is impossible to perform late
 *  binding this way. For this reason, you may prefer to use the functional
 *  form of bind(), but you should prefer early binding (which catches errors
 *  sooner) to late binding (which may miss them) unless you actually need late
 *  binding (e.g., for event handlers).
 *
 *  bind() can be difficult to understand, particularly if you are not familiar
 *  with functional programming. However, it is worth understanding because it
 *  is awesomely powerful. bind() is the solution to every piece of code which
 *  looks like this:
 *
 *    // Everyone does this at first, but it's bad! Don't do it!
 *    var localCopyOfThis = this;
 *    this.onclick = function(event) {
 *      localCopyOfThis.doAction(event);
 *    }
 *
 *  Clearly, this is hacky, but it's not obvious how to do this better. The
 *  solution is:
 *
 *    this.onclick = this.doAction.bind(this);
 *
 *  @param obj|null An object to bind.
 *  @param function|string A function or method to bind, early or late.
 *  @param any... Zero or more arguments to curry.
 *
 *  @return function A function which, when called, calls the method with object
 *                   and arguments bound.
 *
 *  @author epriestley
 */
window.bind = window.bind ||
function (obj, method /*, arg, arg, arg*/) {

  var args = [];
  for (var ii = 2; ii < arguments.length; ii++) {
    args.push(arguments[ii]);
  }

  return function() {
    var _obj = obj || this;

    var _args = args.slice(); // copy
    for (var jj = 0; jj < arguments.length; jj++) {
      _args.push(arguments[jj]);
    }

    if (typeof(method) == "string") {
      if (_obj[method]) {
        return _obj[method].apply(_obj, _args);
      }
    } else {
      return method.apply(_obj, _args);
    }
  }

};
