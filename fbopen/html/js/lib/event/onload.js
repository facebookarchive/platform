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
 *  @requires event-extensions util ua
 *  @provides onload
 */

/**
 *  Register a function for execution before a page is loaded. If the page
 *  loads, functions registered in this way are guaranteed to: execute; execute
 *  exactly once; execute in the order they are registered; and execute after
 *  the DOM is ready.
 *
 *  Note, however, that execution will be attempted in response to the
 *  DOMContentLoaded event, and will succeed to some degree in at least Firefox
 *  2, Safari 2, IE6, and IE7. This means that your onload handler may (and
 *  probably will) fire BEFORE images are loaded or the page is flushed to the
 *  display -- this is generally good, because it prevents a flash of content
 *  before onload handlers fire. However, it also means that you MUST NOT
 *  perform operations which depend on image dimensions, because they probably
 *  will not be available or correct.
 *
 *  A primitive, dependentless version of this function is rendered during
 *  start_html, so it should pretty much be safe to queue up handlers from
 *  anywhere using onloadRegister.
 *
 *  @author marcel, epriestley
 */
window.onloadRegister = function(handler) {
  // If implementation changes, make sure to update primitive version
  // rendered in start_html.
  window.loaded ? _runHook(handler) : _addHook('onloadhooks', handler);
};

/**
 *  Register a function for execution after a page is loaded. These functions
 *  are guaranteed to execute after the window.onload event and after any hooks
 *  registered by onloadRegister().
 */
function onafterloadRegister(handler) {
  window.loaded ? _runHook(handler) : _addHook('onafterloadhooks', handler);
}


/**
 * If you omit the include_quickling_events argument from onunloadRegister or
 * onbeforeunloadRegister, then those will default to respect Quickling
 * navigation iff either:
 *
 *   - you're making the call some time between start_page and close_page, or
 *   - you're making the call after the page is done loading.
 *
 * The effect we're going for is that we respect Quickling events when the
 * call is made 'by the content of the page', but don't if it was requested
 * 'by the chrome of page' (e.g. Chirp).
 */
function _include_quickling_events_default() {
  return window.loading_initial_content_div || window.loaded;
}


/**
 *  Register a function for execution in response to the window's onbeforeunload
 *  event. Because these functions may be executed an arbitrary number of times,
 *  this event is probably not generally useful except for warning users that
 *  they have unsaved changes; instead, use onunloadRegister(). Functions
 *  executing here must not behave like normal event functions -- instead, they
 *  should return a string to prompt the browser to generate a warning dialog.
 *
 *  If `onbeforeunload' returns a string, browsers will prompt the user with
 *  a dialog which includes the string and asks the user to confirm that they
 *  want to navigate away from the page.
 *
 *  These are the strings reported by browsers, so this will turn up when
 *  the code is grepped for; we had some trouble debugging this because no
 *  one knew this mechanism existed and these strings aren't greppable since
 *  they're in the browser:
 *
 *    Are you sure you want to navigate away from this page?
 *
 *    [The return value string.]
 *
 *    Press OK to continue, or Cancel to stay on the current page.
 *
 *  @param include_quickling_events  (optional -- see _include_quickling_events_default for default behavior)
 *                                   Run the handler the next time the user
 *                                   leaves the page OR navigates somewhere
 *                                   using full-page Quickling.
 *
 *  @author epriestley, jrosenstein
 */
function onbeforeunloadRegister(handler, include_quickling_events /* optional */) {
  if (include_quickling_events === undefined) {
    include_quickling_events = _include_quickling_events_default();
  }

  if (include_quickling_events) {
    _addHook('onbeforeleavehooks', handler);
  } else {
    _addHook('onbeforeunloadhooks', handler);
  }
}


/**
 *  Register a function for execution before the page is unloaded. Functions
 *  registered in this way are guaranteed to execute; guaranteed to execute
 *  exactly once; guaranteed to execute in the order they are registered; and
 *  guaranteed to execute in response to the window's onbeforeunload event.
 *
 *  @param include_quickling_events  (optional -- see _include_quickling_events_default for default behavior)
 *                                   Run the handler the next time the user
 *                                   leaves the page OR navigates somewhere
 *                                   using full-page Quickling.
 *
 *  @author epriestley, jrosenstein
 */
function onunloadRegister(handler, include_quickling_events /* optional */) {
  if (include_quickling_events === undefined) {
    include_quickling_events = _include_quickling_events_default();
  }

  if (include_quickling_events) {
    _addHook('onleavehooks', handler);
  } else {
    _addHook('onunloadhooks', handler);
  }
}


/**
 *  Hook function called "onload" -- this probably means DOMContentReady, not
 *  window.onload. Use onloadRegister() to register functions for onload
 *  execution; see that function for more information about how "onload"
 *  handlers work and when they will be executed.
 *
 *  @author marcel, epriestley
 */
function _onloadHook() {
  window.loading_begun = true;
  !window.loaded && window.Env &&
    (Env.t_willonloadhooks=(new Date()).getTime());
  _runHooks('onloadhooks');
  !window.loaded && window.Env &&
    (Env.t_doneonloadhooks=(new Date()).getTime());
  window.loaded = true;
}

function _runHook(handler) {
  try {
    handler( );
  } catch (ex) {
    Util.error('Uncaught exception in hook (run after page load): %x', ex);
  }
}

function _runHooks(hooks) {

  var isbeforeunload = hooks == 'onbeforeleavehooks'
                    || hooks == 'onbeforeunloadhooks';
  var warn = null;

  do {

    var h = window[hooks];
    if (!isbeforeunload) {
      window[hooks] = null;
    }

    if (!h) {
      break;
    }

    for (var ii = 0; ii < h.length; ii++) {
      try {
        if (isbeforeunload) {
          warn = warn || h[ii]();
        } else {
          h[ii]();
        }
      } catch (ex) {
        Util.error('Uncaught exception in hook (%q) #%d: %x', hooks, ii, ex);
      }
    }

    if (isbeforeunload) {
      break;
    }

  } while (window[hooks]);

  if (isbeforeunload && warn) {
    return warn;
  }
}

function _addHook(hooks, handler) {
  (window[hooks] ? window[hooks] : (window[hooks] = [])).push(handler);
}

/**
 *  Bootstrap hooks for `onload', `onbeforeunload', and `onunload' handlers. Use
 *  the functions onloadRegister(), onbeforeunloadRegister(), and
 *  onunloadRegister() to register events for execution; see those functions
 *  for details on what they do, what guarantees they provide, and when they
 *  will fire their handlers.
 *
 *  @author marcel, epriestley
 */
function _bootstrapEventHandlers( ) {

  if (document.addEventListener) {
    if (ua.safari()) {
      var timeout = setInterval(function() {
        if (/loaded|complete/.test(document.readyState)) {
          (window.Env&&(Env.t_domcontent=(new Date()).getTime()));
          _onloadHook();
          clearTimeout(timeout);
        }
      }, 3);
    } else {
      document.addEventListener("DOMContentLoaded", function() {
        (window.Env&&(Env.t_domcontent=(new Date()).getTime()));
        _onloadHook();
        }, true);
    }
  } else {

    var src = 'javascript:void(0)';
    if (window.location.protocol == 'https:') {
      //  The `Gomez' monitoring software freaks out about this a bit, but
      //  browser behavior seems correct.
      src = '//:';
    }

    //  If a client tries to render base.js inline, many browsers will identify
    //  the closing script tag in the string below as the actual end of the
    //  inline script. Escaping the / and > prevents this from happening without
    //  changing the semantics.
    document.write(
      '<script onreadystatechange="if (this.readyState==\'complete\') {'       +
      '(window.Env&&(Env.t_domcontent=(new Date()).getTime()));'               +
      'this.parentNode.removeChild(this);_onloadHook();}" defer="defer" '      +
      'src="' + src + '"><\/script\>');
  }

  //  We need to chain here because Cavalry writes directly to window.onload
  //  and currently needs to register itself before any Javascript includes
  //  get pulled in. With the advent of Env.start, this is technically
  //  unnecessary, but it's not hurting anything for now.
  window.onload = chain(
    window.onload,
    function() {


      //  Force layout before firing onload; this affects Safari 3 and gives us
      //  better rendering benchmarks and more consistent behavior; it can
      //  degrade performance but pretty much anything you're doing should be
      //  onloadRegistered() anyway, which will fire and take effect before
      //  we force a layout.

      //    http://www.howtocreate.co.uk/safaribenchmarks.html


      (window.Env&&(Env.t_layout=(new Date()).getTime()));
      var force_layout = document && document.body && document.body.offsetWidth;
      (window.Env&&(Env.t_onload=(new Date()).getTime()));


      _onloadHook( );
      _runHooks('onafterloadhooks');
    });

  window.onbeforeunload = function( ) {
    var warn = _runHooks('onbeforeleavehooks')
            || _runHooks('onbeforeunloadhooks');
    if (!warn) {
      window.loaded = false;
    }
    return warn;
  };

  window.onunload = chain(
    window.onunload,
    function( ) {
      _runHooks('onleavehooks');
      _runHooks('onunloadhooks');
    });

}

/**
 *  If Javascript is triggered in the href attribute of an anchor tag, IE will
 *  trigger an onbeforeunload event after which we will set window.loaded to
 *  false. Anything that checks this value, such as onloadRegister and
 *  subsequently dialogpro, will not function properly because they will act as
 *  if the page has not yet loaded, but an onload event will never come
 *  leaving the page in a broken state.
 *
 *  In case it is necessary to do this (which is the case when calling
 *  Javascript from Flash), then you can put this function before your function
 *  calls to fix the state of the window. However, you should use the onclick
 *  attribute instead in almost all situations.
 *
 *  BAD:
 *    <a href="javascript:some_function()">Go!</a>
 *
 *  LESS BAD:
 *    <a href="javascript:keep_window_set_as_loaded(); some_function()">Go!</a>
 *
 *  BETTER:
 *    <a href="#" onclick="some_function_that_returns_false()">Go!</a>
 *
 *  Fixing the state of the page involves setting window.loaded back to true
 *  and making sure that any onload or onafterload hooks that may have been
 *  queued get called. If window.loaded was not set to false, then nothing
 *  should happen.
 *
 *  Note: The onbeforeunload event occurs before the Javascript in the href
 *  attribute is called, so it is not prevented. Events that are registered
 *  with onbeforeunloadRegister will still be called. This just fixes the
 *  broken state of window.loaded.
 *
 *  @author blair
 */
function keep_window_set_as_loaded() {
  if (window.loaded == false) {
    window.loaded = true;
    _runHooks('onloadhooks');
    _runHooks('onafterloadhooks');
  }
}
