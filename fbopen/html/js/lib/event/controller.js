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

function /* class */ EventController(eventResponderObject) {

  copy_properties(this, {
        queue : [],
        ready : false,
    responder : eventResponderObject
  });

};

copy_properties(EventController.prototype, {

  startQueue : function( ) {
    this.ready = true;
    this.dispatchEvents( );
    return this;
  },

  pauseQueue : function( ) {
    this.ready = false;
    return this;
  },

  addEvent : function(event) {

    if (event.toLowerCase() !== event) {
      Util.warn(
        'Event name %q contains uppercase letters; events should be lowercase.',
        event);
    }

    var args = [];
    for (var ii = 1; ii < arguments.length; ii++) {
      args.push(arguments[ii]);
    }

    this.queue.push({ type: event, args: args });
    if (this.ready) {
      this.dispatchEvents( );
    }

    return false;
  },

  dispatchEvents : function( ) {

    if (!this.responder) {
      Util.error(
        'Event controller attempting to dispatch events with no responder! '   +
        'Provide a responder when constructing the controller.');
    }

    for (var ii = 0; ii < this.queue.length; ii++) {
      var evtName = 'on' + this.queue[ii].type;
      if (typeof(this.responder[evtName]) != 'function' &&
          typeof(this.responder[evtName]) != 'null') {
        Util.warn(
          'Event responder is unable to respond to %q event! Implement a %q '  +
          'method. Note that method names are case sensitive; use lower case ' +
          'when defining events and event handlers.',
          this.queue[ii].type,
          evtName);
      } else {
        if (this.responder[evtName]) {
          this.responder[evtName].apply(this.responder, this.queue[ii].args);
        }
      }
    }
    this.queue = [];
  }

});

