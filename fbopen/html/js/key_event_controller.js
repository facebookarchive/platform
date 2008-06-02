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
 *  @author   epriestley
 *  @requires vector keycodes
 *  @provides key-event-controller
 */

/**
 *  KeyEventController allows you to capture and respond to keyboard commands.
 *  For example, to play a sound every time the user presses the "m" key:
 *
 *    function quack_key_handler(event, type) {
 *      play_sound('/intern/sound/quack.wav');
 *      return false;
 *    }
 *
 *    KeyEventController.registerKey('m', quack_key_handler);
 *
 *  If your application is consuming the keystroke, you should return `false'
 *  from your handler; this will abort the event and keep it from propagating.
 *
 *  You can also register for most special keys, such as the arrow keys, by
 *  name (e.g. "LEFT", "RIGHT", "RETURN", "ESCAPE", etc.).
 *
 *  By default, your handler will be called only on keydown, only for unmodified
 *  (by control, alt or meta) keypresses, and only if the event target is 
 *  innocuous (for instance, not a textarea). These are the correct filters for
 *  most applications, but if you want to be notified of a broader or narrower
 *  set of events you may provide your own filter function:
 *
 *    function custom_key_filter(event, type) {
 *      if (event.ctrlKey) {
 *        return true;
 *      }
 *      return false;
 *    }
 *
 *    KeyEventController.registerKey('n', quack_key_handler, custom_key_filter);
 *
 *  The filter function should return true to allow the event, and false to
 *  filter it. In this example, the handler will receive keydown, keypress, and
 *  keyup events regardless of event target, provided the control key is
 *  pressed.
 *
 *  Several primitive filters are provided: filterEventTypes,
 *  filterEventTargets, and filterEventModifiers. These filters can be 
 *  selectively chained with custom logic.
 *
 *  For both filter and handler callbacks, the first parameter will be the 
 *  event and the second will be a string indicating its type, one of 
 *  "onkeyup", "onkeydown", or "onkeypress".
 *
 *  @author epriestley
 */
function /* class */ KeyEventController( ) {

  copy_properties(this, {
    handlers: {}
  });

  document.onkeyup    = this.onkeyevent.bind(this, 'onkeyup');
  document.onkeydown  = this.onkeyevent.bind(this, 'onkeydown');
  document.onkeypress = this.onkeyevent.bind(this, 'onkeypress');

}

copy_properties(KeyEventController, {

  instance : null,

  getInstance : function() {
    return KeyEventController.instance ||
          (KeyEventController.instance = new KeyEventController());
  },
  
  defaultFilter : function(event, type) {
    event = event_get(event);
    return KeyEventController.filterEventTypes(event, type)   &&
           KeyEventController.filterEventTargets(event, type) &&
           KeyEventController.filterEventModifiers(event, type);
  },
  
  filterEventTypes : function(event, type) {
    
    if (type === 'onkeydown') {
      return true;
    }
    
    return false;
  },
  
  filterEventTargets : function(event, type) {
    

    var target = event_get_target(event);

    if (target !== document.body            &&  // Safari
        target !== document.documentElement) {  // Firefox
      
      if (!ua.ie()) {
        return false;
      }
      
      if (is_node(target, ['input', 'select', 'textarea', 'object', 'embed'])) {
        return false;
      }
    }
    
    return true;    
  },
  
  filterEventModifiers : function(event, type) {

    if (event.ctrlKey || event.altKey || event.metaKey || event.repeat) {
      return false;
    }

    return true;
  },

  registerKey : function(key, callback, filter_callback) {
    if (filter_callback === undefined) {
      filter_callback = KeyEventController.defaultFilter;
    }
    
    var ctl = KeyEventController.getInstance();
    var eqv = ctl.mapKey(key);

    for (var ii = 0; ii < eqv.length; ii++) {
      key = eqv[ii];
      if (!ctl.handlers[key]) {
        ctl.handlers[key] = [];
      }

      ctl.handlers[key].push({
        callback : callback,
          filter : filter_callback
      });
    }
  },

  bindToAccessKeys : function( ) {
    var ii, k;
    var links = document.getElementsByTagName('a');
    for (ii = 0; ii < links.length; ii++) {
      if (links[ii].accessKey) {
        if (k) {
          KeyEventController.registerKey(
            k,
            bind(KeyEventController, 'accessLink', links[ii]));
        }
      }
    }

    var inputs = document.getElementsByTagName('input');
    for (ii = 0; ii < inputs.length; ii++) {
      if (inputs[ii].accessKey) {
        if (k) {
          KeyEventController.registerKey(
            k,
            bind(KeyEventController, 'accessInput', inputs[ii]));
        }
      }
    }

    var areas  = document.getElementsByTagName('textarea');
    for (ii = 0; ii < areas.length; ii++) {
      if (areas[ii].accessKey) {
        if (k) {
          KeyEventController.registerKey(
            k,
            bind(KeyEventController, 'accessInput', areas[ii]));
        }
      }
    }

  },

  accessLink : function(l, e) {
    if (l.onclick) {
      return l.onclick(e);
    }

    if (l.href) {
      window.location.href = l.href;
    }
  },

  accessInput : function(i, e) {
    Vector2.scrollTo(i);
    i.focus(e);

    if (i.type == 'submit') {
      i.form.submit( );
    }
  },

  keyCodeMap : {
         '[' : [219],
         ']' : [221],
         '`' : [192],
      'LEFT' : [KEYS.LEFT, KeyCodes.Left],
     'RIGHT' : [KEYS.RIGHT, KeyCodes.Right],
    'RETURN' : [KEYS.RETURN],
       'TAB' : [KEYS.TAB],
      'DOWN' : [KEYS.DOWN, KeyCodes.Down],
        'UP' : [KEYS.UP, KeyCodes.Up],
    'ESCAPE' : [KEYS.ESC]
  }

});

copy_properties(KeyEventController.prototype, {

  mapKey : function(k) {
    if (typeof(k) == 'number') {
      return [k];
    }

    if (KeyEventController.keyCodeMap[k.toUpperCase()]) {
      return KeyEventController.keyCodeMap[k.toUpperCase()];
    }

    var l = k.charCodeAt(0);
    var u = k.toUpperCase().charCodeAt(0);
    if (l != u) {
      return [l, u];
    }

    return [l];
  },

  onkeyevent : function(type, e) {
    e = event_get(e);

    var evt = null;
    var handlers = this.handlers[e.keyCode];
    var callback, filter, abort;

    if (handlers) {
      for (var ii = 0; ii < handlers.length; ii++) {
        callback = handlers[ii].callback;
        filter   = handlers[ii].filter;
        
        try {
          if (!filter || filter(e, type)) {
            abort = callback(e, type);
            if (abort === false) {
              return event_abort(e) || event_prevent(e);
            }
          }
        } catch (exception) {
          Util.error('Uncaught exception in key handler: %x', exception);
        }
      }
    }

    return true;
  }

});