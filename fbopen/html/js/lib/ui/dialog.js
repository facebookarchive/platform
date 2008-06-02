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
 *  Class for creating pop-up dialog boxes.  For sample code, check out:
 *
 *    - http://www.dev.facebook.com/intern/example/dialog
 *    - html/intern/example/dialog/javascript.js
 *
 *  There are two (compatible) ways to create dialogs: you can set their
 *  content directly from JavaScript, using various setXXX methods, like:
 *
 *    new Dialog()
 *      .setTitle('This is the title')
 *      .setBody('This is the body')
 *      .setButtons(Dialog.OK_AND_CANCEL)
 *      .show();
 *
 *  or you can have the content set in response to an AsyncRequest using
 *  the setAsync method:
 *
 *    var async = new AsyncRequest().setURI(uri);
 *    new Dialog.setAsync(async).show();
 *
 *  where uri is an endpoint that uses DialogResponse, e.g.:
 *
 *    $response = new DialogReponse();
 *    $response->setTitle('Title')
 *             ->setBody('body')
 *             ->setButtons(array(DialogResponse::OK, DialogResponse::CANCEL))
 *             ->send();
 *
 *  You can also set the handler for the dialog in a few different ways:
 *
 *    1) Call dialog.setHandler(f), where f is a function that takes a
 *       button object.
 *    2) Call dialog.setPostURI(uri), to make the contents of form fields
 *       in the dialog get posted asynchronously to a URI when the user
 *       clicks a button.
 *    3) Create a custom button with a 'handler' property.
 *
 *  NOTE: currently, the Dialog class is just a wrapper around dialogpro.js,
 *        but providing a much nicer interface.  Over time, we'll make
 *        the Dialog class's implementation standalone, and port existing
 *        dialogs over to using it.
 *
 *  (also requires key_event_controller.js -- TODO: make that library provide)
 *
 *  @author jrosenstein
 *  @provides dialog
 *  @requires util dom event-extensions array-extensions intl
 *
 */
function /* class */ Dialog() {
  Dialog._setup();
  this._pd = new pop_dialog();
  this._pd._dialog_object = this;
}

Dialog.OK = {
  name : 'ok',
  label : tx('sh:ok-button')
};
Dialog.CANCEL = {
  name : 'cancel',
  label : tx('sh:cancel-button'),
  className : 'inputaux'
};
Dialog.CLOSE = {
  name : 'close',
  label : tx('sh:close-button')
};
Dialog.SAVE = {
  name : 'save',
  label : tx('sh:save-button')
};
Dialog.OK_AND_CANCEL = [Dialog.OK, Dialog.CANCEL];
Dialog._STANDARD_BUTTONS = [Dialog.OK, Dialog.CANCEL, Dialog.CLOSE, Dialog.SAVE];

Dialog.getCurrent = function() {
  var stack = generic_dialog.dialog_stack;
  if (stack.length == 0) {
    return null;
  }
  return stack[stack.length - 1]._dialog_object || null;
};

Dialog._basicMutator = function(private_key) {
  return function(value) {
    this[private_key] = value;
    this._dirty();
    return this;
  };
};

copy_properties(Dialog.prototype, {

  /**
   * Construct/display the dialog, typically after you've set all of its
   * properties via setXXX methods.
   */
  show : function() {
    this._showing = true;
    this._dirty();
    return this;
  },

  /**
   * Destroy this dialog (fading it out from view).
   */
  hide : function() {
    this._showing = false;
    if (this._autohide_timeout) {
      clearTimeout(this._autohide_timeout);
      this._autohide_timeout = null;
    }
    this._pd.fade_out(250);
    return this;
  },

  /**
   * Set the HTML to appear in the title area of the dialog (blue bar
   * along the top).
   */
  setTitle : Dialog._basicMutator('_title'),

  /**
   * Set the HTML to appear in the main white area of the dialog.
   */
  setBody : Dialog._basicMutator('_body'),

  /**
   * Set the timeout to auto-fade the dialog
   */
  setAutohide : function(autohide) {
    if (autohide) {
      if (this._showing) {
        this._autohide_timeout = setTimeout(bind(this, 'hide'), autohide);
      } else {
        this._autohide = autohide;
      }
    } else {
      this._autohide = null;
      if (this._autohide_timeout) {
        clearTimeout(this._autohide_timeout);
        this._autohide_timeout = null;
      }
    }
    return this;
  },

  /**
   * Set the HTML to appear in the space above the body.
   */
  setSummary : Dialog._basicMutator('_summary'),

  /**
   * Specify which buttons should appear in the lower right corner of the
   * dialog.  You can pass in either a single button or an array of buttons.
   *
   * Typically, you can just use the standard dialog buttons, i.e. one of:
   *   dialog.setButtons(Dialog.OK)
   *   dialog.setButtons(Dialog.CANCEL)
   *   dialog.setButtons(Dialog.OK_AND_CANCEL)
   *   dialog.setButtons(Dialog.CLOSE)
   *
   * Or you can specify your own custom "button objects", which look like:
   *
   *   {
   *     name: 'help',   // to be used as the name attribute of the button
   *     label: 'Help',  // user-visible string
   *     className: '',  // optional, if you want to style the button
   *     handler: function(button) { ... }   // optional
   *   }
   *
   * If you do specify a handler, it will be called when the button is pressed,
   * before hiding the dialog box.  If you don't want the dialog box to
   * disappear, just have your handler return false.
   */
  setButtons : function(buttons) {
    if (!(buttons instanceof Array)) {
      buttons = [buttons];
    }

    for (var i = 0; i < buttons.length; ++i) {
      if (typeof(buttons[i]) == 'string') {
        var button = Dialog._findButton(Dialog._STANDARD_BUTTONS, buttons[i]);
        if (!button) {
          Util.error('Unknown button: ' + buttons[i]);
        }
        buttons[i] = button;
      }
    }

    this._buttons = buttons;
    this._dirty();
    return this;
  },

  /**
   * Set the HTML that appears on the left side of the button area (i.e. the
   * lower-left corner) of this dialog.
   */
  setButtonsMessage : Dialog._basicMutator('_buttons_message'),

  /**
   * If set to true, then, if another dialog is created before this one has
   * been hidden, then this one will be resurrected after the new one is
   * hidden.
   */
  setStackable : Dialog._basicMutator('_is_stackable'),

  /**
   * Set the function to be called when the user clicks any button on the
   * dialog other than Cancel.  The function will be passed one argument:
   * the button object for the button that was clicked, which in most cases
   * will be Dialog.OK.
   */
  setHandler : function(handler) {
    this._handler = handler;
    return this;
  },

  /**
   * setPostURI is an alternative to setHandler.  It specifies that, when the
   * user clicks a button other than Cancel, we should fire off an AsyncRequest
   * to post_uri, with method POST, and with data set to name/value pairs of
   * all form fields in the dialog box (including the button that was clicked).
   *
   * The post_uri endpoint can, in turn, send back a payload (via DialogResponse)
   * that can modify the dialog.  Any attributes not specfied in the payload
   * (via a DialogResponse::setXXX method) will remain the same.  If you'd
   * like the dialog to close, call DialogResponse::hide or ::setAutohide.
   *
   * In this way, you can achieve complex back-and-forth workflows.  Note that
   * your close handler (if you set one with setCloseHandler) will be called
   * only at the end of the workflow -- different steps of the workflow do not
   * constitute different dialogs.
   */
  setPostURI : function(post_uri) {
    this.setHandler(this._submitForm.bind(this, 'POST', post_uri));
    return this;
  },

   /*
    * Similar to setPostURI, only that the AsyncRequest is fired off with method GET
    */
  setGetURI : function(get_uri) {
    this.setHandler(this._submitForm.bind(this, 'GET', get_uri));
    return this;
  },

  /**
   * Set whether this dialog is "modal", i.e. whether the user can click on
   * other things in the page while the dialog is visible.
   */
  setModal : function(modal /* = true */) {
    if (modal === undefined) {
      modal = true;
    }

    if (this._showing && this._modal && !modal) {
      Util.error("At the moment we don't support un-modal-ing a modal dialog");
    }

    this._modal = modal;
    return this;
  },

  /**
   * Adjusts the width of the entire dialog box so as to make the width of
   * the body section -- not including padding or border -- equal to width,
   * which is measured in pixels.
   */
  setContentWidth : function(width) {
    this._content_width = width;
    this._dirty();
    return this;
  },

  /**
   * Adds the className to the underlying dialog.
   * If you need to change the width, use setContentWidth. You should
   * probably NOT use this method unless you cannot find any other way of
   * achieving the styling of the Dialog.
   */
  setClassName : Dialog._basicMutator('_class_name'),

  /**
   * Set the function to be called when the dialog disappears, either as the
   * result of the user clicking a button (including Cancel), or another dialog
   * being created (if this dialog is not stackable).
   */
  setCloseHandler : function(close_handler) {
    this._close_handler = call_or_eval.bind(null, null, close_handler);
    return this;
  },

  /**
   * Take an un-sent async request (on which you've done things like setURI,
   * setData, setReadOnly, or setMethod as applicable), and send it.  The
   * resulting payload should be constructed through the DialogRespose class:
   *
   *   $response = new DialogResponse();
   *   $response->setTitle('Dialog title HTML')
   *            ->setBody('Dialog body HTML',
   *            ->setButtons(array(
   *                DialogResponse::OK,
   *                DialogResponse::Button('help', fbt('Help')),
   *                DialogResponse::CANCEL,
   *              )),
   *            ->setModal(true)
   *            ->send()
   *
   * In particular, for any setXXX method in the JS Dialog class (except
   * setAsync itself), there should be a corresponding setXXX method in
   * the PHP DialogResponse class (and, if there isn't, then someone probably
   * just forgot it and you should add it).
   */
  setAsync : function(async_request) {

    var handler = function(response) {
      if (this._async_request != async_request) {
        return;
      }
      this._async_request = null;

      var payload = response.getPayload();
      if (typeof(payload) == 'string') {
        this.setBody(payload);
      } else {
        for (var propertyName in payload) {
          var mutator = this['set' + propertyName.substr(0, 1).toUpperCase()
                                   + propertyName.substr(1)];
          if (!mutator) {
            Util.error("Unknown Dialog property: " + propertyName);
          }
          mutator.call(this, payload[propertyName]);
        }
      }
      this._dirty();
    }.bind(this);

    var hide = bind(this, 'hide');
    async_request
      .setHandler(chain(async_request.getHandler(), handler))
      .setErrorHandler(chain(hide, async_request.getErrorHandler()))
      .setTransportErrorHandler(chain(hide, async_request.getTransportErrorHandler()))
      .send();

    this._async_request = async_request;
    this._dirty();
    return this;
  },

  _dirty : function() {
    if (!this._is_dirty) {
      this._is_dirty = true;
      bind(this, '_update').defer();
    }
  },

  _update : function() {
    this._is_dirty = false;

    if (!this._showing) {
      return;
    }

    // autohide requested, not running an async request, not already autohiding
    if (this._autohide &&
        !this._async_request &&
        !this._autohide_timeout) {
      this._autohide_timeout = setTimeout(bind(this, 'hide'), this._autohide);
    }

    // Handle class, this has to be done before we display the Dialog
    if (this._class_name) {
      this._pd.setClassName(this._class_name);
    }

    if (!this._async_request) {

      // Construct HTML in case where we're not just "Loading...".

      var html = [];

      if (this._title) {
        html.push('<h2><span>' + this._title + '</span></h2>');
      }

      html.push('<div class="dialog_content">');

        if (this._summary) {
          html.push('<div class="dialog_summary">');
            html.push(this._summary);
          html.push('</div>');
        }

        html.push('<div class="dialog_body">');
          html.push(this._body);
        html.push('</div>');

        if (this._buttons || this._buttons_message) {
          html.push('<div class="dialog_buttons">');

          if (this._buttons_message) {
            html.push('<div class="dialog_buttons_msg">');
              html.push(this._buttons_message);
            html.push('</div>');
          }

          if (this._buttons) {
            this._buttons.forEach(function(button) {
              html.push('<input class="inputsubmit ' + (button.className || '') + '"'
                            + ' type="button"'
                            + (button.name ? (' name="' + button.name + '"') : '')
                            + ' value="' + htmlspecialchars(button.label) + '"'
                            + ' onclick="Dialog.getCurrent().handleButton(this.name);" />');
            }, this);
          }

          html.push('</div>');
        }

      html.push('</div>');

      this._pd.show_dialog(html.join(''));

    } else {

      // Handle "Loading..." state.

      var title = this._title || tx('sh:loading');
      this._pd.show_loading_title(title);

    }

    // Handle modality.

    if (this._modal) {
      this._pd.make_modal();
    }

    // Handle content width.

    if (this._content_width) {
      this._pd.popup.childNodes[0].style.width = (this._content_width + 42) + 'px';
    }

    // Extra properties to pass along.

    this._pd.is_stackable  = this._is_stackable;
    this._pd.close_handler = this._close_handler;

  },

  /**
   * Produce the effect of the user having clicked a given button in the dialog.
   *
   * @param button   either the button object itself or
   *                 the 'name' field of the button object.
   */
  handleButton : function(button) {
    if (typeof(button) == 'string') {
      button = Dialog._findButton(this._buttons, button);
    }

    if (!button) {
      Util.error('Huh?  How did this button get here?');
      return;
    }

    if (call_or_eval(button, button.handler) === false) {
      return;
    }

    if (button != Dialog.CANCEL) {
      if (call_or_eval(this, this._handler, {button: button}) === false) {
        return;
      }
    }

    this.hide();

  },

  _submitForm : function(method, uri, button) {
    var data = this._getFormData();
    data[button.name] = button.label;  // simulate how buttons are normally submitted in forms

    var async_request = new AsyncRequest()
      .setURI(uri)
      .setData(data)
      .setMethod(method)
      .setReadOnly(method == 'GET');
    this.setAsync(async_request);
    return false;
  },

  _getFormData : function() {
    var dialog_content_divs = DOM.scry(this._pd.content, 'div.dialog_content');
    if (dialog_content_divs.length != 1) {
      Util.error(dialog_content_divs.length
                 + " dialog_content divs in this dialog?  Weird.");
    }
    return serialize_form(dialog_content_divs[0]);
  }

});

Dialog._findButton = function(buttons, name) {
  for (var i = 0; i < buttons.length; ++i) {
    if (buttons[i].name == name) {
      return buttons[i];
    }
  }
  return null;
};

/**
 * Perform general set up for dialog boxes when the very first dialog is created.
 */
Dialog._setup = function() {
  if (Dialog._is_set_up) {
    return;
  }
  Dialog._is_set_up = true;

  // Escape key handler.
  var filter = function(event, type) {  // don't filter based on event target
    return KeyEventController.filterEventTypes(event, type)
        && KeyEventController.filterEventModifiers(event, type);
  };
  KeyEventController.registerKey('ESCAPE', Dialog._handleEscapeKey, filter);
};

/**
 * If there's a cancel button, simulate the user having pressed it.  Or, if
 * there's only one button, simluate the user having pressed that.
 */
Dialog._handleEscapeKey = function(event, type) {
  var dialog = Dialog.getCurrent();
  if (!dialog) {
    return true;
  }

  var buttons = dialog._buttons;
  if (!buttons) {
    return true;
  }

  var cancel_button = Dialog._findButton(buttons, 'cancel');
  if (cancel_button) {
    var button_to_simulate = cancel_button;
  } else if (buttons.length == 1) {
    var button_to_simulate = buttons[0];
  } else {
    return true;
  }

  dialog.handleButton(button_to_simulate);
  return false;
}

