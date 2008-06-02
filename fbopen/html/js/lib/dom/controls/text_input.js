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
 *
 *  @requires control-dom function-extensions
 *  @provides control-textinput
 */

function /* class */ TextInputControl(textinput) {
  this.parent.construct(this, textinput);

  copy_properties(this, {
      placeholderText : null,
            maxLength : this.getRoot().maxLength || null,
                radio : null,
              focused : false,
    nativePlaceholder : false
  });

  var r = this.getRoot();

  //  If this is a "Search" input in Safari, there's a native placeholder
  //  implementation available; use that instead of our own.



  if ((String(r.type).toLowerCase() == 'search') && ua.safari()) {
    this.nativePlaceholder = true;
    this.setPlaceholderText(r.getAttribute('placeholder'));
  }

  DOM.addEvent(r, 'focus',    this.setFocused.bind(this, true));
  DOM.addEvent(r, 'blur',     this.setFocused.bind(this, false));

  var up = this.update.bind(this);


  DOM.addEvent(r, 'keydown',  up);
  DOM.addEvent(r, 'keyup',    up);
  DOM.addEvent(r, 'keypress', up);
  setInterval(up, 150);

  this.setFocused(false);
}

TextInputControl.extend(DOMControl);

copy_properties(TextInputControl.prototype, {

  /**
   *  Associate the attached element with a radio button, which will be
   *  automatically focused when the text input is selected.
   */
  associateWithRadioButton : function(element) {
    this.radio = element && $(element);
    return this;
  },

  setMaxLength : function(maxlength) {
    this.maxLength = maxlength;
    this.getRoot().maxLength = this.maxLength || null;
    return this;
  },


  getValue : function() {
    if (this.getRoot().value == this.placeholderText) {
      return null;
    }
    return this.getRoot().value;
  },


  isEmpty : function() {
    var v = this.getValue();
    return (v === null || v == '');
  },


  setValue : function(value) {
    this.getRoot().value = value;
    this.update();

    return this;
  },


  clear : function() {
    return this.setValue('');
  },


  isFocused : function() {
    return this.focused;
  },

  setFocused : function(focused) {
    this.focused = focused;


    //  Inputs with type "search" handle their own "placeholder" behavior.


    if (this.placeholderText && !this.nativePlaceholder) {
      var r = this.getRoot();
      var v = r.value;
      if (this.focused) {
        CSS.removeClass(r, 'DOMControl_placeholder');
        if (this.isEmpty()) {
          this.clear();
        }
      } else if (this.isEmpty()) {
        CSS.addClass(r, 'DOMControl_placeholder');
        this.setValue(this.placeholderText);
      }
    }


    this.update();

    return this;
  },

  setPlaceholderText : function(text) {
    this.placeholderText = text;

    if (this.nativePlaceholder) {
      this.getRoot().setAttribute('placeholder', text);
    }

    return this.setFocused(this.isFocused());
  },

  /**
   *  Respond to an event.
   */
  onupdate : function() {

    if (this.radio) {
      if (this.focused) {
        this.radio.checked = true;
      }
    }

    //  Note: the default "maxlength" property of inputs without one in Firefox
    //  is "-1", so test for maxLength > 0.
    //
    //    >>> $N('input').maxLength
    //    -1

    var r = this.getRoot();
    if (this.maxLength > 0) {
      if (r.value.length > this.maxLength) {
        r.value = r.value.substring(0, this.maxLength);
      }
    }
  }
});


/* -(  Deprecated Placeholder API  )----------------------------------------- */


function placeholderSetup(id) {
  if (!ge(id)) {
    Util.warn(
      'Setting up a placeholder for an element which does not exist: %q.',
      id);
    return;
  }


  //  Firefox will allow you to access the value of `.placeholder' ONLY by using
  //  getAttribute().

  if (!$(id).getAttribute('placeholder')) {
    Util.warn(
      'Setting up a placeholder for an element with no placeholder text: %q.',
      id);
    return;
  }


  return new TextInputControl($(id))
    .setPlaceholderText($(id).getAttribute('placeholder'));
}