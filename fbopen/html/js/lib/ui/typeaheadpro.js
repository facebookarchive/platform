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
 *  @provides typeaheadpro tokenizer tokenizer-input token dynamic-custom-source
 *            custom-source regions-source language-source keywords-source
 *            time-source network-source concentration-souce
 *            friend-source friend-and-email-source friendlist-source
 *            static-source typeahead-source
 *  @requires event-extensions ua vector intl typeaheadpro-css
 */

//
// typeahead class. for... typing ahead
// =======================================================================================

function typeaheadpro(obj, source, properties) {

  // h4x. don't do u\a checking until we need to.
  if (!typeaheadpro.hacks) {
    // this hack is for missing keypress events. if you type really fast and hit enter at the same time as a letter it'll forget
    //   to send us a keypress for the enter and we can't cancel the form submit. this hack introduces another bug where if you hold down
    //   a key and the blur off the input you can't submit the form, but that's the lesser of two evils in this case.
    typeaheadpro.should_check_missing_events = ua.safari() < 500;
    // MSIE will make select boxes shine through our div unless we cover up with an iframe
    typeaheadpro.should_use_iframe =
    // Returning false in a keydown in Safari or IE means you will not get a keypress event (until the key repeat rate fires). However,
    //   we need to return false in the keydown to prevent the cursor from moving in the textbox.
    typeaheadpro.should_simulate_keypress = ua.ie() || (ua.safari() > 500 && ua.safari() < 523 || ua.safari() >= 525);
    // Opera \ Safari 2 doesn't support overflow-y (which we need to make Safari 3 work)
    typeaheadpro.should_use_overflow = ua.opera() < 9.5 || ua.safari() < 500;
    // Firefox doesn't give us magic keydown events when people type
    // CJK characters, so we can't turn on the input checker on demand.
    if (ua.firefox()) {
      this.poll_handle = setInterval(this.check_value.bind(this), 100);
      this.deactivate_poll_on_blur = false;
    }
    typeaheadpro.hacks = true;
  }

  // link a reference to this instance statically
  typeaheadpro.instances = (typeaheadpro.instances || []);
  typeaheadpro.instances.push(this);
  this.instance = typeaheadpro.instances.length - 1;

  // copy over supplied parameters
  copy_properties(this, properties || {});

  // setup pointers every which way
  this.obj = obj;
  this.obj.typeahead = this;

  // attach event listeners where needed
  this.obj.onfocus = this._onfocus.bind(this);
  this.obj.onblur = chain(this.obj.onblur, this._onblur.bind(this));
  this.obj.onchange = this._onchange.bind(this);

  this.obj.onkeyup = function(event) {
    return this._onkeyup(event || window.event);
  }.bind(this);

  this.obj.onkeydown = function(event) {
    return this._onkeydown(event || window.event);
  }.bind(this);

  this.obj.onkeypress = function(event) {
    return this._onkeypress(event || window.event);
  }.bind(this);

  // setup custom icon
  this.want_icon_list = false;
  this.showing_icon_list = false;
  this.stop_suggestion_select = false;


  if (this.typeahead_icon_class && this.typeahead_icon_get_return) {
    this.typeahead_icon = document.createElement('div');
    this.typeahead_icon.className = 'typeahead_list_icon ' + this.typeahead_icon_class;
    this.typeahead_icon.innerHTML = '&nbsp;';
    this.setup_typeahead_icon();
    // in FF doing setup_typeahead_icon() unfocuses the input b/c it moves it in the DOM.. so refocus
    setTimeout(function() { this.focus(); }.bind(this), 50);
    this.typeahead_icon.onmousedown = function(event) {
      return this.typeahead_icon_onclick(event || window.event);
    }.bind(this);
  }

  // setup container for results
  this.focused = this.obj.offsetWidth ? true : false;
  this.anchor = this.setup_anchor();
  this.dropdown = document.createElement('div');
  this.dropdown.className = 'typeahead_list';
  if (!this.focused) {
    this.dropdown.style.display = 'none';
  }
  this.anchor_block = this.anchor_block || this.anchor.tagName.toLowerCase() == 'div';
  if (this.should_use_absolute) {
    document.body.appendChild(this.dropdown);
    this.dropdown.className += ' typeahead_list_absolute';
  } else {
    // If the parent node is the wrapper we use so we can shift the bottom
    // border color of the input when there are results, add the dropdown
    // to our grandparent node rather than the parent so the wrapper contains
    // only the input.
    var us = this.anchor;
    var parent = us.parentNode;
    if (parent.id == 'qsearch_wrapper') {
      us = parent;
      parent = parent.parentNode;
    }
    if (us.nextSibling) {
      parent.insertBefore(this.dropdown, us.nextSibling);
    } else {
      parent.appendChild(this.dropdown);
    }
    if (!this.anchor_block) {
      parent.insertBefore(document.createElement('br'), this.dropdown);
    }
  }

  this.dropdown.appendChild(this.list = document.createElement('div'));
  this.dropdown.onmousedown = function(event) {
    return this.dropdown_onmousedown(event || window.event);
  }.bind(this);

  // iframe for hacky stuff
  if (typeaheadpro.should_use_iframe && !typeaheadpro.iframe) {
    typeaheadpro.iframe = document.createElement('iframe');
    typeaheadpro.iframe.src = "/common/blank.html";
    typeaheadpro.iframe.className = 'typeahead_iframe';
    typeaheadpro.iframe.style.display = 'none';
    typeaheadpro.iframe.frameBorder = 0;
    document.body.appendChild(typeaheadpro.iframe);
  }

  // set the iframe zIndex to one below the dropdown... to fix an issue with typeaheads in dialogs
  if (typeaheadpro.should_use_iframe && typeaheadpro.iframe) {
    typeaheadpro.iframe.style.zIndex = parseInt(get_style(this.dropdown, 'zIndex')) - 1;
  }

  // get this party started
  this.results_text = '';
  this.last_key_suggestion = 0;
  this.status = typeaheadpro.STATUS_BLOCK_ON_SOURCE_BOOTSTRAP;
  this.clear_placeholder();
  if (source) {
    this.set_source(source);
  }
  if (this.source) {
    this.selectedindex = -1;
    if (this.focused) {
      this.show();
      this._onkeyup();
      this.set_class('');
      this.capture_submit();
    }
  } else {
    this.hide();
  }
}
// don't change these
typeaheadpro.prototype.enumerate = false;
typeaheadpro.prototype.interactive = false;
typeaheadpro.prototype.changed = false;
typeaheadpro.prototype.render_block_size = 50;
typeaheadpro.prototype.typeahead_icon_class = false;
typeaheadpro.prototype.typeahead_icon_get_return = false;
typeaheadpro.prototype.old_value = "";
typeaheadpro.prototype.poll_handle = null;
typeaheadpro.prototype.deactivate_poll_on_blur = true;
typeaheadpro.prototype.suggestion_count = 0;
typeaheadpro.STATUS_IDLE = 0;
typeaheadpro.STATUS_WAITING_ON_SOURCE = 1;
typeaheadpro.STATUS_BLOCK_ON_SOURCE_BOOTSTRAP = 2;

// ok to change these
typeaheadpro.prototype.should_use_absolute = false;
typeaheadpro.prototype.max_results = 0;
typeaheadpro.prototype.max_display = 10;
typeaheadpro.prototype.allow_placeholders = true;
typeaheadpro.prototype.auto_select = true;

// set a source for this typeahead
typeaheadpro.prototype.set_source = function(source) {
  this.source = source;
  this.source.set_owner(this);
  this.status = typeaheadpro.STATUS_IDLE;
  this.cache = {};
  this.last_search = 0;
  this.suggestions = [];
}

// grab the anchor for the typeahead list
typeaheadpro.prototype.setup_anchor = function() {
  return this.obj;
}

// destroys this typeahead instance
typeaheadpro.prototype.destroy = function() {

  if (this.typeahead_icon) {
    this.typeahead_icon.parentNode.removeChild(this.typeahead_icon);
    this.toggle_icon_list = function () {};
  }

  this.clear_render_timeouts();
  if (!this.anchor_block && this.anchor.nextSibling.tagName.toLowerCase() == 'br') {
    this.anchor.parentNode.removeChild(this.anchor.nextSibling);
  }
  if (this.dropdown) {
    this.dropdown.parentNode.removeChild(this.dropdown);
  }

  // blank out the events because these can lag sometimes it seems
  this.obj.onfocus =
  this.obj.onblur =
  this.obj.onkeyup =
  this.obj.onkeydown =
  this.obj.onkeypress = null;

  // pull it out the dom
  this.obj.parentNode.removeChild(this.obj);

  // clear up pointers
  this.anchor =
  this.obj =
  this.obj.typeahead =
  this.dropdown = null;
  delete typeaheadpro.instances[this.instance];
}

// check for changes to the value; needed because Asian input
// methods don't fire JS events when the user finishes composing a
// multi-keystroke character on all browsers, and sometimes fire
// events when the user is in the middle of entering a character.
typeaheadpro.prototype.check_value = function() {
  if (this.obj) {
    var new_value = this.obj.value;
    if (new_value != this.old_value) {
      this.dirty_results();
      this.old_value = new_value;
    }
  }
}

// event handler when the input box receives a key press
typeaheadpro.prototype._onkeyup = function(e) {
  this.last_key = e ? e.keyCode : -1;

  // safari h4x
  if (this.key_down == this.last_key) {
    this.key_down = 0;
  }

  switch (this.last_key) {
    case 27: // esc
      this.selectedindex = -1;
      this._onselect(false);
      this.hide();
      break;
  }
}

// event handler when a key is pressed down on the text box
typeaheadpro.prototype._onkeydown = function(e) {
  this.key_down = this.last_key=e ? e.keyCode : -1;
  this.interactive = true;

  switch (this.last_key) {
    case 33:
    case 34:
    case 38:
    case 40:
      if (typeaheadpro.should_simulate_keypress) {
        this._onkeypress({keyCode: this.last_key});
      }
      return false;

    case 9: // tab
      this.select_suggestion(this.selectedindex);
      this.advance_focus();
      break;

    case 13: // enter
     if (this.select_suggestion(this.selectedindex)) {
       this.hide();
     }
     // we capture the return of _onsubmit here and return it onkeypress to prevent the form from submitting
     if (typeof(this.submit_keydown_return) != 'undefined') {
       this.submit_keydown_return = this._onsubmit(this.get_current_selection());
     }
     return this.submit_keydown_return;

    case 229:
      // IE and Safari send this fake keycode to indicate we're in an IME
      // compose state. Since we won't necessarily get an event when the
      // user selects a character after composing it, start polling the
      // input to see if it has changed.
      if (!this.poll_handle) {
        this.poll_handle = setInterval(this.check_value.bind(this), 100);
      }
      break;

    default:
      // Safari doesn't give us a key-down on backspace, etc.
      setTimeout(bind(this, 'check_value'), 10);
  }
}

// event handler for when a key is pressed
typeaheadpro.prototype._onkeypress = function(e) {
  var multiplier = 1;
  this.last_key = e ? event_get_keypress_keycode(e) : -1;
  this.interactive = true;

  switch (this.last_key) {
    case 33: // page up
      multiplier = this.max_display;
      // fallthrough
    case 38: // up
      this.set_suggestion(multiplier > 1 && this.selectedindex > 0 && this.selectedindex < multiplier ? 0 : this.selectedindex - multiplier);
      this.last_key_suggestion = (new Date()).getTime();
      return false;

    case 34: // page down
      multiplier = this.max_display;
      // fallthrough
    case 40: // down
      if (trim(this.get_value()) == '' && !this.enumerate) {
        this.enumerate = true;
        this.results_text = null;
        this.dirty_results();
      } else {
        this.set_suggestion(this.suggestions.length <= this.selectedindex + multiplier ? this.suggestions.length - 1 : this.selectedindex + multiplier);
        this.last_key_suggestion = (new Date()).getTime();
      }
      return false;

    case 13: // enter
      var ret = null;
      if (typeof(this.submit_keydown_return) == 'undefined') {
        ret = this.submit_keydown_return = this._onsubmit(this.get_current_selection());
      } else {
        ret = this.submit_keydown_return;
        delete this.submit_keydown_return;
      }
      return ret;

    default:
      // Key isn't part of the value yet, so do the typeahead logic
      // after the element state is updated (which happens after this
      // event handler returns.)
      setTimeout(bind(this, 'check_value'), 10);
      break;
  }
  return true;
}

// mostly used for compatibility with mobile browsers
typeaheadpro.prototype._onchange = function() {
  this.changed = true;
}

// event handler when a match is found (happens a lot)
typeaheadpro.prototype._onfound = function(obj) {
  return this.onfound ? this.onfound.call(this, obj) : true;
}

// event handler when the user submits the form
typeaheadpro.prototype._onsubmit = function(obj) {
  if (this.onsubmit) {
    var ret = this.onsubmit.call(this, obj);

    if (ret && this.obj.form) {
      if (!this.obj.form.onsubmit || this.obj.form.onsubmit()) {
        this.obj.form.submit();
      }
      return false;
    }
    return ret;
  } else {
    this.advance_focus();
    return false;
  }
}

// event handler when the user selects a suggestions
typeaheadpro.prototype._onselect = function(obj) {
  if (this.onselect) {
    this.onselect.call(this, obj);
  }
}

// event handler when obj gets focus
typeaheadpro.prototype._onfocus = function() {
  if (this.last_dropdown_mouse > (new Date()).getTime() - 10 || this.focused) {
    return;
  }
  this.focused = true;
  this.changed = false;
  this.clear_placeholder();
  this.results_text = '';
  this.set_class('');
  this.dirty_results();
  this.show();
  this.capture_submit();
  if (this.typeahead_icon) {
    show(this.typeahead_icon);
  }
}

// event handler when focus is lost
typeaheadpro.prototype._onblur = function(event) {
  if (!this.stop_hiding) {
    if (this.showing_icon_list) {
      this.toggle_icon_list(true);
    }
  } else {
    this.focus();
    return false;
  }

  if (this.last_dropdown_mouse && this.last_dropdown_mouse > (new Date()).getTime() - 10) {
    event_prevent(event);
    setTimeout(function() { this.focus() }.bind(this.obj), 0);
    return false;
  }

  this.focused = false;
  if (this.changed && !this.interactive) {
    this.dirty_results();
    this.changed = false;
    return;
  }

  if (!this.suggestions) {
    this._onselect(false);
  } else if (this.selectedindex >= 0) {
    this.select_suggestion(this.selectedindex);
  }
  this.hide();
  this.update_class();
  if (!this.get_value()) {
    var noinput = this.allow_placeholders ? '' : this.source.gen_noinput();
    this.set_value(noinput ? noinput : '');
    this.set_class('typeahead_placeholder')
  }

  if (this.poll_handle && this.deactivate_poll_on_blur) {
    clearInterval(this.poll_handle);
    this.poll_handle = null;
  }
}

typeaheadpro.prototype.typeahead_icon_onclick = function(event) {
  this.stop_hiding = true;
  this.focus();
  setTimeout(function() { this.toggle_icon_list(); }.bind(this), 50);
  event_abort(event);
  return false;
}

// this function exists because IE7 doesn't let us override mousedown events on the scrollbar
typeaheadpro.prototype.dropdown_onmousedown = function(event) {
  this.last_dropdown_mouse = (new Date()).getTime();
}

typeaheadpro.prototype.setup_typeahead_icon = function() {
  this.typeahead_parent = document.createElement('div');
  this.typeahead_parent.className = 'typeahead_parent';
  this.typeahead_parent.appendChild(this.typeahead_icon);
  this.obj.parentNode.insertBefore(this.typeahead_parent, this.obj);
}

// event handler for mousemove \ mouseout
typeaheadpro.prototype.mouse_set_suggestion = function(index) {
  if (!this.visible) {
    return;
  }
  if ((new Date()).getTime() - this.last_key_suggestion > 50) {
    this.set_suggestion(index);
  }
}

// steals the submit event of the parent form (if any). see should_check_missing_events
typeaheadpro.prototype.capture_submit = function() {
  if (!typeaheadpro.should_check_missing_events) return;

  if ((!this.captured_form || this.captured_substitute != this.captured_form.onsubmit) && this.obj.form) {

    this.captured_form = this.obj.form;
    this.captured_event = this.obj.form.onsubmit;
    this.captured_substitute = this.obj.form.onsubmit = function() {
      return ((this.key_down && this.key_down!=13 && this.key_down!=9) ? this.submit_keydown_return : (this.captured_event ? this.captured_event.apply(arguments, this.captured_form) : true)) ? true : false;
    }.bind(this);
  }
}

// sets the current selected suggestion. error checking is done here, so you can pass this pretty much anything.
typeaheadpro.prototype.set_suggestion = function(index) {
  this.stop_suggestion_select = false;
  if (!this.suggestions || this.suggestions.length <= index) { return }
  var old_node = this.get_suggestion_node(this.selectedindex);
  this.selectedindex = (index <= -1) ? -1 : index;
  var cur_node = this.get_suggestion_node(this.selectedindex);

  if (old_node) {
    old_node.className = old_node.className.replace(/\btypeahead_selected\b/, 'typeahead_not_selected');
  }
  if (cur_node) {
    cur_node.className = cur_node.className.replace(/\btypeahead_not_selected\b/, 'typeahead_selected');
  }
  this.recalc_scroll();

  this._onfound(this.get_current_selection());
}

// returns the list child node for a particular index
typeaheadpro.prototype.get_suggestion_node = function(index) {
  var nodes = this.list.childNodes;
  return index == -1 ? null : nodes[Math.floor(index / this.render_block_size)].childNodes[index % this.render_block_size];
}

// gets the current selection
typeaheadpro.prototype.get_current_selection = function() {
  return this.selectedindex == -1 ? false : this.suggestions[this.selectedindex];
}

// sets the class if we've found a suggestions
typeaheadpro.prototype.update_class = function() {
  if (this.suggestions && this.selectedindex!=-1 && typeahead_source.flatten_string(this.get_current_selection().t) == typeahead_source.flatten_string(this.get_value())) {
    this.set_class('typeahead_found');
  } else {
    this.set_class('');
  }
}

// selects this suggestion... it's a done deal
typeaheadpro.prototype.select_suggestion = function(index) {
  if (!this.stop_suggestion_select && this.current_selecting != index) {
    this.current_selecting = index;
    }
  if (!this.suggestions || index == undefined || index === false || this.suggestions.length <= index || index < 0) {
    this._onfound(false);
    this._onselect(false);
    this.selectedindex = -1;
    this.set_class('');
  } else {
    this.selectedindex = index;
    this.set_value(this.suggestions[index].t);
    this.set_class('typeahead_found');
    this._onfound(this.suggestions[this.selectedindex]);
    this._onselect(this.suggestions[this.selectedindex]);
  }
  if (!this.interactive) {
    this.hide();
    this.blur();
  }
  this.current_selecting = null;
  return true;
}

// sets the value of the input
typeaheadpro.prototype.set_value = function(value) {
  this.obj.value = value;
}

// gets the value of the input
typeaheadpro.prototype.get_value = function() {
  if (this.showing_icon_list && this.old_typeahead_value != this.obj.value) {
    // hide the icon list because the user is typing something
    this.toggle_icon_list();
  }
  if (this.want_icon_list) {
    return this.typeahead_icon_get_return;
  } else {
    if (this.showing_icon_list) {
      // hide
      this.toggle_icon_list();
    }
  }
  return this.obj.value;
}

// called by source in response to search_value
typeaheadpro.prototype.found_suggestions = function(suggestions, text, fake_data) {

  if (!suggestions) {
    suggestions = [];
  }

  // record the number of suggestions for use by subclasses
  this.suggestion_count = suggestions.length;

  if (!fake_data) {
    this.status = typeaheadpro.STATUS_IDLE;
    this.add_cache(text, suggestions);
  }
  this.clear_render_timeouts();

  // if this is a duplicate call we can skip it
  if (this.get_value() == this.results_text) {
    return;
  } else if (!fake_data) {
    this.results_text = typeahead_source.flatten_string(text);
    if (this.enumerate && trim(this.results_text) != '') {
      this.enumerate = false;
    }
  }

  // go through the new and old selections and figure out if the currently highlighted
  // suggestion is in the new results. if so, we highlight it after the update.
  var current_selection = -1;
  if (this.selectedindex != -1) {
    var selected_id = this.suggestions[this.selectedindex].i;
    for (var i = 0, l = suggestions.length; i < l; i++) {
      if (suggestions[i].i == selected_id) {
        current_selection = i;
        break;
      }
    }
  }
  if (current_selection == -1 && this.auto_select && suggestions.length) {
    current_selection = 0;
    this._onfound(suggestions[0]);
  }
  this.selectedindex = current_selection;
  this.suggestions = suggestions;
  if (!fake_data) {
    this.real_suggestions = suggestions;
  }

  if (suggestions.length) {
    var html = [],
        blocks = Math.ceil(suggestions.length / this.render_block_size),
        must_render = {},
        firstblock,
        samplenode = null;
    this.list.innerHTML = ''; // clear the old the suggestions
    for (var i = 0; i < blocks; i++) {
      this.list.appendChild(document.createElement('div'));
    }
    // figure out which blocks we need to render first
    if (current_selection > -1) {
      firstblock = Math.floor(current_selection / this.render_block_size);
      // always render the block the user is currently selecting
      must_render[firstblock] = true;
      // and the next closest one
      if (current_selection % this.render_block_size > this.render_block_size / 2) {
        must_render[firstblock + 1] = true;
      } else if (firstblock != 0) {
        must_render[firstblock - 1] = true;
      }
    } else {
      must_render[0] = true;
    }

    // render the blocks that the user might see
    for (var node in must_render) {
      this.render_block(node);
      sample = this.list.childNodes[node].firstChild;
    }
    this.show();

    // and schedule rendering of the other ones
    if (blocks) {
      var suggestion_height = sample.offsetHeight;
      this.render_timeouts = [];
      for (var i = 1; i < blocks; i++) {
        if (!must_render[i]) {
          this.list.childNodes[i].style.height = suggestion_height * Math.min(this.render_block_size, suggestions.length - i * this.render_block_size) + 'px';
          this.list.childNodes[i].style.width = '1px';
          this.render_timeouts.push(setTimeout(this.render_block.bind(this, i), 700 + i * 50)); // render blocks 750ms later
        }
      }
    }
  } else {
    this.selectedindex = -1;
    this.set_message(this.status == typeaheadpro.STATUS_IDLE ? this.source.gen_nomatch() : this.source.gen_loading());
    this._onfound(false);
  }
  this.recalc_scroll();

  if (!fake_data && this.results_text != typeahead_source.flatten_string(this.get_value())) {
    this.dirty_results();
  }
}

// render a block of suggestions into the list
typeaheadpro.prototype.render_block = function(block, stack) {
  var suggestions = this.suggestions,
      selectedindex = this.selectedindex,
      text = this.get_value(),
      instance = this.instance,
      html = [],
      node = this.list.childNodes[block];
  for (var i = block * this.render_block_size, l = Math.min(suggestions.length, (block + 1) * this.render_block_size); i < l; i++) {
    html.push('<div class="');
    if (selectedindex == i) {
      html.push('typeahead_suggestion typeahead_selected');
    } else {
      html.push('typeahead_suggestion typeahead_not_selected');
    }
    html.push('" onmouseover="typeaheadpro.instances[', instance, '].mouse_set_suggestion(', i, ')" ',
                'onmousedown="typeaheadpro.instances[', instance, '].select_suggestion(', i, '); event_abort(event);">',
              this.source.gen_html(suggestions[i], text), '</div>');
  }
  node.innerHTML = html.join('');
  node.style.height = 'auto';
  node.style.width = 'auto';
}

// if there's render timeouts still pending cancel them
typeaheadpro.prototype.clear_render_timeouts = function() {
  if (this.render_timeouts) {
    for (var i = 0; i < this.render_timeouts.length; i++) {
      clearTimeout(this.render_timeouts[i]);
    }
    this.render_timeouts = null;
  }
}

// shrink the typeahead list to make a scroll bar
typeaheadpro.prototype.recalc_scroll = function() {
  var cn = this.list.firstChild;
  if (!cn) {
    return;
  }

  if (cn.childNodes.length > this.max_display) { // this assumes that render_block_size is ALWAYS greater than max_display
    var last_child = cn.childNodes[this.max_display - 1];
    var height = last_child.offsetTop + last_child.offsetHeight;
    this.dropdown.style.height = height + 'px';
    var selected = this.get_suggestion_node(this.selectedindex);
    if (selected) {
      var scrollTop = this.dropdown.scrollTop;
      if (selected.offsetTop < scrollTop) {
        this.dropdown.scrollTop = selected.offsetTop;
      } else if (selected.offsetTop + selected.offsetHeight > height + scrollTop) {
        this.dropdown.scrollTop = selected.offsetTop + selected.offsetHeight - height;
      }
    }
    // Safari 3 has REALLY weird behavior with scrollbars, but overflowY seems to work almost cross-browser
    // I wanted to leave that note here, because at first glance style.overflowY seems less than optimal.
    // Also, Safari 2 doesn't respect style.overflow='auto' in Javascript, it seems (I could be wrong, I didn't
    // test this too much).
    // If you make any changes to overflow-related code in typeaheadpro, be sure to test well in Safari 2 and
    // Safari 3 -- this code is unreasonably sensitive.
    if (!typeaheadpro.should_use_overflow) {
      this.dropdown.style.overflowY = 'scroll';
      this.dropdown.style.overflowX = 'hidden';
    }
  } else {
    this.dropdown.style.height = 'auto';
    if (!typeaheadpro.should_use_overflow) {
      this.dropdown.style.overflowY = 'hidden';
    }
  }
}

// searches the local cache for the text
typeaheadpro.prototype.search_cache = function(text) {
  return this.cache[typeahead_source.flatten_string(text)];
}

// adds a value to the local cache
typeaheadpro.prototype.add_cache = function(text, results) {
  if (this.source.cache_results) {
    this.cache[typeahead_source.flatten_string(text)] = results;
  }
}

// called by source when it's done loading
typeaheadpro.prototype.update_status = function(status) {
  this.status = status;
  this.dirty_results();
}

// sets the class on the textbox while maintaining ones this object didn't fool around with
typeaheadpro.prototype.set_class = function(name) {
  this.obj.className = (this.obj.className.replace(/typeahead_[^\s]+/g, '') + ' ' + name).replace(/ {2,}/g, ' ');
}

// dirties the current results... fetches new results if need be
typeaheadpro.prototype.dirty_results = function() {

  if (!this.enumerate && trim(this.get_value()) == '') {
    this.results_text = '';
    this.set_message(this.source.gen_placeholder());
    this.suggestions = [];
    this.selectedindex = -1;
    return;
  } else if (this.results_text == typeahead_source.flatten_string(this.get_value())) {
    return; // just kidding! don't dirty!
  } else if (this.status == typeaheadpro.STATUS_BLOCK_ON_SOURCE_BOOTSTRAP) {
    this.set_message(this.source.gen_loading());
    return;
  }

  var time = (new Date).getTime();
  var updated = false;
  if (this.last_search <= (time - this.source.search_limit) && this.status == typeaheadpro.STATUS_IDLE) { // ready
    updated = this.perform_search();
  } else {
    if (this.status == typeaheadpro.STATUS_IDLE) {
      if (!this.search_timeout) {
        this.search_timeout = setTimeout(function() {
          this.search_timeout = false;
          if (this.status == typeaheadpro.STATUS_IDLE) {
            this.dirty_results();
          }
        }.bind(this), this.source.search_limit - (time - this.last_search));
      }
    }
  }

  // generate fake results from the last known results
  if (this.source.allow_fake_results && this.real_suggestions && !updated) {
    var ttext = typeahead_source.tokenize(this.get_value()).sort(typeahead_source._sort);
    var fake_results = [];
    for (var i = 0; i < this.real_suggestions.length; i++) {
      if (typeahead_source.check_match(ttext, this.real_suggestions[i].t + ' ' + this.real_suggestions[i].n)) {
        fake_results.push(this.real_suggestions[i]);
      }
    }
    if (fake_results.length) {
      this.found_suggestions(fake_results, this.get_value(), true);
    } else {
      this.selectedindex = -1;
      this.set_message(this.source.gen_loading());
    }
  }
}

// runs a search for the current search text
typeaheadpro.prototype.perform_search = function() {

  if (this.get_value() == this.results_text) {
    return true;
  }

  var results;
  if ((results = this.search_cache(this.get_value())) === undefined && // local cache
      !(results = this.source.search_value(this.get_value()))) {       // if this isn't going to return instantly
    this.status = typeaheadpro.STATUS_WAITING_ON_SOURCE;
    this.last_search = (new Date).getTime();
    return false;
  }
  this.found_suggestions(results, this.get_value(), false);
  return true;
}

// sets a message for the results
typeaheadpro.prototype.set_message = function(text) {
  this.clear_render_timeouts();
  if (text) {
    this.list.innerHTML = '<div class="typeahead_message">' + text + '</div>';
    this.reset_iframe();
  } else {
    this.hide();
  }
  this.recalc_scroll();
}

// moves the iframe to where it needs to be
typeaheadpro.prototype.reset_iframe = function() {
  if (!typeaheadpro.should_use_iframe) { return }
  if (this.should_use_absolute) {
    typeaheadpro.iframe.style.top = this.dropdown.style.top;
    typeaheadpro.iframe.style.left = this.dropdown.style.left;
  } else {
    typeaheadpro.iframe.style.top = elementY(this.dropdown)+'px';
    typeaheadpro.iframe.style.left = elementX(this.dropdown)+'px';
  }
  typeaheadpro.iframe.style.width = this.dropdown.offsetWidth+'px';
  typeaheadpro.iframe.style.height = this.dropdown.offsetHeight+'px';
  typeaheadpro.iframe.style.display = '';
}

// advances the form to the next available input
typeaheadpro.prototype.advance_focus = function() {
  var inputs=this.obj.form ? get_all_form_inputs(this.obj.form) : get_all_form_inputs();
  var next_inputs = false;
  for (var i=0; i<inputs.length; i++) {
    if (next_inputs) {
      if (inputs[i].type != 'hidden' && inputs[i].tabIndex != -1 && inputs[i].offsetParent) {
        next_inputs.push(inputs[i]);
      }
    } else if (inputs[i] == this.obj) {
      next_inputs = [];
    }
  }

  // omg this is so retarded. if you have an onblur event that destroys itself,
  // focus() gets all confused and just loses focus. so we do this with nested
  // timeouts to make damn sure the next element got focus
  setTimeout(function() {
    for (var i = 0; i < this.length; i++) {
      try {
        if (this[i].offsetParent) {
          this[i].focus();
          setTimeout(function() {
            try {
              this.focus();
            } catch(e) {}
          }.bind(this[i]), 0);
          return;
        }
      } catch(e) {}
    }
  }.bind(next_inputs ? next_inputs : []), 0);
}

// clears out the placeholder if need be
typeaheadpro.prototype.clear_placeholder = function() {
  if (this.obj.className.indexOf('typeahead_placeholder')!=-1) {
    this.set_value('');
    this.set_class('');
  }
}

// clear the input
typeaheadpro.prototype.clear = function() {
  this.set_value('');
  this.set_class('');
  this.selectedindex = -1;
  this.enumerate = false;
  this.dirty_results();
}

// hide the suggestions
typeaheadpro.prototype.hide = function() {
  if (this.stop_hiding) {
    return;
  }
  this.visible = false;
  if (this.should_use_absolute) {
    this.dropdown.style.display = 'none';
  } else {
    this.dropdown.style.visibility = 'hidden';
  }
  this.clear_render_timeouts();
  if (typeaheadpro.should_use_iframe) {
    typeaheadpro.iframe.style.display='none';
  }
}

// show the suggestions
typeaheadpro.prototype.show = function() {
  this.visible = true;
  if (this.focused) {
    if (this.should_use_absolute) {
      this.dropdown.style.top = elementY(this.anchor) + this.anchor.offsetHeight + 'px';
      this.dropdown.style.left = elementX(this.anchor) + 'px';
    }
    this.dropdown.style.width = (this.anchor.offsetWidth-2) + 'px'; // assumes a border of 2px
    this.dropdown.style[this.should_use_absolute ? 'display' : 'visibility'] = '';
    if (typeaheadpro.should_use_iframe) {
      typeaheadpro.iframe.style.display='';
      this.reset_iframe();
    }
  }
}

// toggle the list that shows up when you click the typeahead_icon
typeaheadpro.prototype.toggle_icon_list = function(no_focus) {
  if (this.showing_icon_list) {
    this.showing_icon_list = false;
    this.source.showing_icon_list = false;
    // hide
    if (!no_focus) {
      this.focus();
    }
    remove_css_class_name(this.typeahead_icon, 'on_selected');
    this.want_icon_list = false;
    this.showing_icon_list = false;
    this.stop_suggestion_select = true;
    if (this.obj) {
      this.dirty_results();
    }
  } else {
    this.source.showing_icon_list = true;
    this.old_typeahead_value = this.obj.value;
    this.stop_suggestion_select = true;
    this.want_icon_list = true;
    this.dirty_results();
    this.focus();
    add_css_class_name(this.typeahead_icon, 'on_selected');
    this.show();
    this.set_suggestion(-1);
    this.showing_icon_list = true;
  }
  // hacky because of IE event stuff
  setTimeout(function() { this.stop_hiding = false;}.bind(this), 100)
}

// focus the input
typeaheadpro.prototype.focus = function() {
    this.obj.focus();
}

typeaheadpro.prototype.blur = function() {
  this.obj.blur();
}

// kills an input's typeahead obj (if there is one)
/* static */ typeaheadpro.kill_typeahead = function(obj) {
  if (obj.typeahead) {
    if (!this.should_use_absolute && !this.anchor_block) {
      obj.parentNode.removeChild(obj.nextSibling); // <br />
    }
    obj.parentNode.removeChild(obj.nextSibling); // <div>
    if (obj.typeahead.source) {
      obj.typeahead.source =
      obj.typeahead.source.owner = null;
    }
    obj.onfocus =
    obj.onblur =
    obj.onkeypress =
    obj.onkeyup =
    obj.onkeydown =
    obj.typeahead = null;
  }
}

//
// the tokenizer, used on the compose pages
// =======================================================================================
function tokenizer(obj, typeahead_source, nofocus, max_selections, properties) {
  // hacks
  if (ua.safari() < 500) {
    tokenizer.valid_arrow_count = 0;
    tokenizer.valid_arrow_event = function() { return tokenizer.valid_arrow_count++ % 2 == 0 };
  } else {
    tokenizer.valid_arrow_event = function() { return true };
  }

  // setup the dom elements
  this.obj = obj;
  this.obj.tokenizer = this;
  this.typeahead_source = typeahead_source;

  while (!/\btokenizer\b/.test(this.obj.className)) {
    this.obj = this.obj.parentNode;
  }
  this.tab_stop = this.obj.getElementsByTagName('input')[0];

  // event hooks
  this.inputs = [];
  this.obj.onmousedown = function(event) {return this._onmousedown(event ? event : window.event)}.bind(this);
  this.tab_stop.onfocus = function(event) {return this._onfocus(event ? event : window.event)}.bind(this);
  this.tab_stop.onblur = function(event) {return this.tab_stop_onblur(event ? event : window.event)}.bind(this);
  this.tab_stop.onkeydown = function(event) {return this.tab_stop_onkeydown(event ? event : window.event)}.bind(this);
  if (!nofocus && elementY(this.obj) > 0 && this.obj.offsetWidth) {
    this._onfocus();
  }
  this.max_selections = max_selections;

  // copy over supplied parameters
  copy_properties(this, properties || {});
  // Store this list for tokenizer_input creation later
  this.properties = properties;
}

/* static */tokenizer.is_empty = function(obj) {
  if (has_css_class_name(obj, 'tokenizer_locked')) {
    return obj.getElementsByTagName('input').length == 0;
  } else {
    return (!obj.tokenizer || obj.tokenizer.count_names() == 0);
  }
}

tokenizer.prototype.get_token_values = function() {
  var r = [];
  var inputs = this.obj.getElementsByTagName('input');
  for (var i = 0; i < inputs.length; ++i) {
    if (inputs[i].name && inputs[i].value) {
      r.push(inputs[i].value);
    }
  }
  return r;
}

tokenizer.prototype.get_token_strings = function() {
  var r = [];
  var tokens = this.obj.getElementsByTagName('a');
  for (var i = 0; i < tokens.length; ++i) {
    if (typeof tokens[i].token != 'undefined') {
      r.push(tokens[i].token.text);
    }
  }
  return r;
}

tokenizer.prototype.clear = function() {
  var tokens = this.obj.getElementsByTagName('a');
  for (var i = tokens.length - 1; i >= 0; --i) {
    if (typeof tokens[i].token != 'undefined') {
      tokens[i].token.remove();
    }
  }
}

tokenizer.prototype._onmousedown = function(event) {
  // onmousedown is really onfocus, duh
  if (this.onfocus) {
    this.onfocus();
  }
  setTimeout(function() {
    if (!this.inputs.length) {
      if (this.max_selections > this.count_names()) {
        new tokenizer_input(this);
      } else {
        var tokens = this.obj.getElementsByTagName('a');
        for (var i=tokens.length-1; i>=0; i--) {
          if (typeof tokens[i].token != 'undefined') {
            tokens[i].token.select();
            break;
          }
        }
      }
    } else {
      this.inputs[0].focus();
    }
  }.bind(this),0);

  event ? event.cancelBubble = true : false;
  return false;
}

tokenizer.prototype._onfocus = function(event) {
  if (this.tab_stop_ignore_focus) {
    this.tab_stop_ignore_focus = false;
    return;
  }


  this._onmousedown();
}

tokenizer.prototype.tab_stop_onblur = function(event) {
  this.selected_token ? this.selected_token.deselect() : false;
}

tokenizer.prototype.tab_stop_onkeydown = function(event) {
  if (!event.keyCode || !this.selected_token) { return; }

  switch (event.keyCode) {
    case 8: // backspace
    case 46: // delete
      var tok = this.selected_token;
      var prev = tok.element.previousSibling;
      if (prev && prev.input) {
        prev.input.element.focus();
      } else {
        new tokenizer_input(this, tok.element);
      }
      tok.remove();
      return false;

    case 37: // left
      if (!tokenizer.valid_arrow_event()) { break; }
      var tok = this.selected_token;
      var prev = tok.element.previousSibling;
      if (prev && prev.input) {
        prev.input.element.focus();
      } else if (this.max_selections > this.count_names()) {
        new tokenizer_input(this, tok.element);
      } else {
        return false;
      }
      tok.deselect();
      return false;

    case 39: // right
      if (!tokenizer.valid_arrow_event()) { break; }
      var tok = this.selected_token;
      var next = tok.element.nextSibling;
      if (next && next.input) {
        next.input.focus();
      } else if (this.max_selections > this.count_names()) {
        new tokenizer_input(this, tok.element.nextSibling);
      } else {
        return false;
      }
      tok.deselect();
      return false;
  }

}// returns the number of unique people in this tokenizer
tokenizer.prototype.count_names = function(plus) {
  var inputs = this.obj.getElementsByTagName('input');
  var uniq = {};
  var count = 0;
  for (var i=0; i < inputs.length; i++) {
    if (inputs[i].type == 'hidden' &&
        !uniq[inputs[i].value]) {
      uniq[inputs[i].value] = true;
      ++count;
    }
  }
  if (plus) {
    for (var j = 0; j < plus.length; j++) {
      if (!uniq[plus[j]]) {
        uniq[plus[j]] = true;
        ++count;
      }
    }
  }
  return count;
}

// disables and locks the tokenizer. there's currently no reanble... so be careful :)
tokenizer.prototype.disable = function() {
  this.tab_stop.parentNode.removeChild(this.tab_stop);
  this.obj.className += ' tokenizer_locked';
}

  function tokenizer_input(tokenizer, caret) {
  if (!tokenizer_input.hacks) {
    // safari doesn't let you style input boxes much, so this is a hack with negative margins to hide their stupid styling
    tokenizer_input.should_use_borderless_hack = ua.safari();
    // internet explorer and opera are really silly about floats, which is unfortunate because safari and firefox behave differently.
    // we can do the resizing of these input fields almost automatically with CSS, but since the float behavior is wacky we need to
    // set style.width on every keystroke. we special case it out here so other browsers don't have to deal with the speed decrease.
    // this turns into a pretty decent speed boost for safari.
    tokenizer_input.should_use_shadow_hack = ua.ie() || ua.opera();
    tokenizer_input.hacks = true;
  }
  this.tokenizer = tokenizer;

  // Build obj... this is our <input> that the user types into
  this.obj = document.createElement('input');
  this.obj.input = this;
  this.obj.tabIndex = -1;
  this.obj.size = 1;
  this.obj.onmousedown = function(event) {(event ? event : window.event).cancelBubble=true}.bind(this);

  // Build the shadow. This is a hidden span element that streches out the parent div based on the input's contents
  this.shadow = document.createElement('span');
  this.shadow.className = 'tokenizer_input_shadow';

  // The parent for the whole thing
  this.element = document.createElement('div');
  this.element.className = 'tokenizer_input' + (tokenizer_input.should_use_borderless_hack ? ' tokenizer_input_borderless' : '');
  this.element.appendChild(document.createElement('div'));
  this.element.firstChild.appendChild(this.obj);
  (tokenizer_input.should_use_shadow_hack ? document.body : this.element.firstChild).appendChild(this.shadow);
  caret ? tokenizer.obj.insertBefore(this.element, caret) : tokenizer.obj.appendChild(this.element);
  this.tokenizer.tab_stop.disabled = true;
  this.update_shadow();
  this.update_shadow = this.update_shadow.bind(this); // always bind to this instance
  this.tokenizer.inputs.push(this);

  this.parent.construct(this, this.obj, this.tokenizer.typeahead_source);
  if (this.focused) {
    this.focus();
    this.obj.select();
  }

  // Copy the tokenizer properties into this object
  copy_properties(this, tokenizer.properties || {});

  // auto-resize even for copy/pasted email addresses
  setInterval(this.update_shadow.bind(this), 100);
}
tokenizer_input.extend(typeaheadpro);
tokenizer_input.prototype.gen_nomatch =
tokenizer_input.prototype.gen_loading =
tokenizer_input.prototype.gen_placeholder =
tokenizer_input.prototype.gen_noinput = '';
tokenizer_input.prototype.max_display = 8;

tokenizer_input.prototype.setup_anchor = function() {
  return this.tokenizer.obj;
}

tokenizer_input.prototype.update_shadow = function() {
  try {
    var val = this.obj.value;
  } catch(e) { return }; // this might be called after the input is dead
  if (this.shadow_input != val) {
    this.shadow.innerHTML = htmlspecialchars((this.shadow_input = val) + '^_^');
    if (tokenizer_input.should_use_shadow_hack) {
      this.obj.style.width = this.shadow.offsetWidth+'px';
      this.obj.value = val;
    }
  }
}

tokenizer_input.prototype._onblur = function() {
  if (this.parent._onblur() === false) {
    return false;
  }
  if (this.changed && !this.interactive) {
    this.dirty_results();
    this.changed = false;
    return;
  }
  if (this.changed || this.interactive) {
    this.select_suggestion(this.selectedindex);
  }
  setTimeout(function() {this.disabled=false}.bind(this.tokenizer.tab_stop), 1000);

  // Use a callback here to destroy ourselves.  Otherwise, on Firefox, the caret
  // won't end up where the user clicked.
  tokenizerToDestroy = this;
  setTimeout(function() {tokenizerToDestroy.destroy();}, 0);
}

tokenizer_input.prototype._onfocus = function() {
  this.tokenizer.tab_stop.disabled = true;
  this.parent._onfocus();
  return true;
}

tokenizer_input.prototype._onkeydown = function(event) {
  switch (event.keyCode) {
    case 13: // enter
      break;

    case 37: // left
    case 8: // backspace
      if (this.get_selection_start() !=0 || this.obj.value != '') {
        break;
      }
      var prev = this.element.previousSibling;
      if (prev && prev.token) {
        setTimeout(prev.token.select.bind(prev.token), 0);
      }
      break;

    case 39: // right
    case 46: // delete
      if (this.get_selection_start() != this.obj.value.length) {
        break;
      }
      var next = this.element.nextSibling;
      if (next && next.token) {
        setTimeout(next.token.select.bind(next.token), 0);
      }
      break;

    case 188: // comma
      this._onkeydown({keyCode:13});
      return false;

    case 9: // tab
      if (this.obj.value) {
        this.advance_focus();
        this._onkeydown({keyCode:13});
        return false;
      } else if (!event.shiftKey) {
        this.advance_focus();
        this.parent._onkeydown(event);
        return false;
      }
      break;
  }

  return this.parent._onkeydown(event);
}

tokenizer_input.prototype._onkeypress = function(event) {
  switch (event.keyCode) {
    case 9: // tab
      return false;
  }
  setTimeout(this.update_shadow, 0);
  return this.parent._onkeypress(event);
}

// override this to not fire if it's already entered
tokenizer_input.prototype.select_suggestion = function(index) {
  if (this.suggestions && index >= 0 && this.suggestions.length > index) {
    var inputs = this.tokenizer.obj.getElementsByTagName('input');
    var id = this.suggestions[index].i;
    for (i = 0; i < inputs.length; i++) {
      if (inputs[i].name == 'ids[]' && inputs[i].value == id) {
        return false;
      }
    }
  }
  return this.parent.select_suggestion(index);
}

// move this to base.js if needed
tokenizer_input.prototype.get_selection_start = function() {
  if (this.obj.selectionStart != undefined) {
    return this.obj.selectionStart;
  } else {
    return Math.abs(document.selection.createRange().moveStart('character', -1024));
  }
}

tokenizer_input.prototype.onselect = function(obj) {
  if (obj) {
    var inputs = this.tokenizer.obj.getElementsByTagName('input');
    for (i=0; i<inputs.length; i++) {
      if (inputs[i].name == 'ids[]' && inputs[i].value == obj.i) {
        return false;
      }
    }
    new token(obj, this.tokenizer, this.element);

    if (this.tokenizer.max_selections > this.tokenizer.count_names()) {
      this.clear();
    } else {
      this.destroy();
      this.hide = function() {}; // workaround because this gets called later on a destroy'd element
      return false;
    }
  }

  if (obj) {
    this.tokenizer._ontokenadded(obj);
  }

  this.tokenizer.typeahead_source.onselect_not_found.call(this);
  return false;
}

// event handler when the user adds a token
tokenizer.prototype._ontokenadded = function(obj) {
  if (this.ontokenadded) {
    this.ontokenadded.call(this, obj);
  }
}


// event handler when the user removes a token
tokenizer.prototype._ontokenremoved = function(obj) {
  if (this.ontokenremoved) {
    this.ontokenremoved.call(this, obj);
  }
}

// event handler when the user tries to add a token that isn't in the index
tokenizer.prototype._ontokennotfound = function(text) {
  if (this.ontokennotfound) {
    this.ontokennotfound.call(this, text);
  }
}

tokenizer_input.prototype._onsubmit = function() {
  return false;
}

// uneeded since we don't use submits with this guy
tokenizer_input.prototype.capture_submit = function() {
  return false;
}

tokenizer_input.prototype.clear = function() {
  this.parent.clear();
  this.update_shadow();
}

tokenizer_input.prototype.destroy = function() {
  if (tokenizer_input.should_use_shadow_hack) {
    this.shadow.parentNode.removeChild(this.shadow);
  }
  this.element.parentNode.removeChild(this.element);

  this.element = null;

  var index = this.tokenizer.inputs.indexOf(this);
  if (index != -1) {
    this.tokenizer.inputs.splice(index, 1);
  }
  this.tokenizer =
  this.element =
  this.shadow = null;

  this.parent.destroy();
  return null;
}


function token(obj, tokenizer, caret) {
  if (obj.is && (tokenizer.count_names(obj.is) > tokenizer.max_selections)) {
    (new contextual_dialog).set_context(tokenizer.obj).show_prompt(tx('ta12'), tx('ta13')).fade_out(500, 1500);
    return null;
  }
  this.tokenizer = tokenizer;
  this.element = document.createElement('a');
  this.element.className = 'token';
  this.element.href = '#';
  this.element.tabIndex = -1;
  this.element.onclick = function(event) {return this._onclick(event ? event : window.event)}.bind(this);
  this.element.onmousedown = function(event) {(event ? event : window.event).cancelBubble = true; return false};
  this.render_obj(obj);
  this.obj = obj;
  this.element.token = this;
  caret ? this.tokenizer.obj.insertBefore(this.element, caret) : this.tokenizer.obj.appendChild(this.element);
}

token.prototype.render_obj = function(obj) {
  var inputs = '';
  // note: unless they give us "np", add fb_protected="true" as an attribute so
  // we can verify on platform pages that these are actually typeahead
  // selectors.  not protecting with "np" is necessary for the case where the
  // app is prefilling the tokens so we don't want to treat them as valid
  // request recipients (for fb:request-forms).
  if (obj.np) {
    var fb_protected='';
  } else {
    var fb_protected='fb_protected="true" ';
  }
  if (obj.e) {
    inputs = ['<input type="hidden" ', fb_protected, 'name="emails[]" value="', obj.e, '" />'].join('');
  } else if (obj.i) {
    inputs = ['<input type="hidden" ', fb_protected, 'name="', this.tokenizer.obj.id, '[]" value="', obj.i, '" />'].join('');
  } else if (obj.is) {
    for (var i = 0, il = obj.is.length; i < il; i++) {
      inputs += ['<input type="hidden" ', fb_protected, 'name="', this.tokenizer.obj.id, '[]" value="', obj.is[i], '" />'].join('');
    }
    this.explodable = true;
    this.n = obj.n;
  }
  this.text = obj.t;

  this.element.innerHTML = ['<span><span><span><span>',
                            inputs,
                            htmlspecialchars(obj.t),
                            '<span onclick="this.parentNode.parentNode.parentNode.parentNode.parentNode.token.remove(true); event.cancelBubble=true; return false;" ',
                                  'onmouseover="this.className=\'x_hover\'" onmouseout="this.className=\'x\'" class="x">&nbsp;</span>',
                            '</span></span></span></span>'].join('');
}

token.prototype._onclick = function(event) {
  // Detect and process doubleclick on explodable things
  var this_select_time = (new Date()).getTime();
  if (this.explodable &&
      this.tokenizer.last_select_time &&
      (this_select_time - this.tokenizer.last_select_time < 1400)) {

    // Grab the list of things to add
    var to_add = this.n;
    this.remove();

    // Figure out what is already present
    var inputs = this.tokenizer.obj.getElementsByTagName('input');
    var already_ids = {};
    for (var i = 0; i < inputs.length; ++i) {
      if (inputs[i].name == 'ids[]') {
        already_ids[inputs[i].value] = true;
      }
    }
    for (var id in to_add) {
      if (!already_ids[id]) {
        new token({'t' : to_add[id], 'i' : id}, this.tokenizer);
      }
    }
  } else {
    this.select();
  }

  this.tokenizer.last_select_time = this_select_time;
  event.cancelBubble = true;
  return false;
}

token.prototype.select = function(again) {
  if (this.tokenizer.selected_token && !again) {
    this.tokenizer.selected_token.deselect();
  }
  this.element.className = trim(this.element.className.replace('token_selected', '')) + ' token_selected';
  this.tokenizer.tab_stop_ignore_focus = true;
  if (this.tokenizer.tab_stop.disabled) {
    this.tokenizer.tab_stop.disabled = false;
  }
  this.tokenizer.tab_stop.focus();
  this.tokenizer.selected_token = this;
  if (again !== true) {
    setTimeout(function() {this.select(true)}.bind(this), 0);
  } else {
    setTimeout(function() {this.tab_stop_ignore_focus = false}.bind(this.tokenizer), 0);
  }
}

token.prototype.remove = function(focus) {
  this.element.parentNode.removeChild(this.element);
  this.element.token = null;
  this.tokenizer.selected_token = null;
  if (focus) {
    this.tokenizer._onmousedown();
  }
  if (this.obj) {
    this.tokenizer._ontokenremoved(this.obj);
  }
}

token.prototype.deselect = function() {
  this.element.className = trim(this.element.className.replace('token_selected', ''));
  this.tokenizer.selected_token = null;
}


//
// typeahead source generic class
// =======================================================================================
function typeahead_source() {
}
typeahead_source.prototype.cache_results = false;      // may the owner cache results?
typeahead_source.prototype.enumerable = false;         // is it possible to get a full list of the options?
typeahead_source.prototype.allow_fake_results = false; // if the source is slow should typeaheadpro be allowed to generate fake data
                                                       //   to create the illusion of responsiveness?
typeahead_source.prototype.search_limit  = 10;         // how often can we run a query?

// basically a tokenized search
/* static */ typeahead_source.check_match = function(search, value) {
  value = typeahead_source.tokenize(value);
  for (var i = 0, il = search.length; i < il; i++) {
    if (search[i].length) { // do we want to count this piece as a search token?
      var found = false;
      for (var j = 0, jl = value.length; j < jl; j++) {
        if (value[j].length >= search[i].length && value[j].substring(0, search[i].length) == search[i]) {
          found = true;
          value[j]=''; // prevent this piece of the name from being matched again
          break;
        }
      }
      if (!found) {
        return false;
      }
    }
  }
  return true;
}

// takes a string and returns an array strings that should be used for searching
/* static */ typeahead_source.tokenize = function(text, capture, noflatten) {
  return (noflatten ? text : typeahead_source.flatten_string(text)).split(capture ? typeahead_source.normalizer_regex_capture : typeahead_source.normalizer_regex);
}
typeahead_source.normalizer_regex_str = '(?:(?:^| +)["\'.\\-]+ *)|(?: *[\'".\\-]+(?: +|$)|@| +)';
typeahead_source.normalizer_regex = new RegExp(typeahead_source.normalizer_regex_str, 'g');
typeahead_source.normalizer_regex_capture = new RegExp('('+typeahead_source.normalizer_regex_str+')', 'g');

// replaces accented characters with the non-accented version. also lower-case the strings.
/* static */ typeahead_source.flatten_string = function(text) {
  if (!typeahead_source.accents) {
    typeahead_source.accents = {
      a: /à|á|â|ã|ä|å/g,
      c: /ç/g,
      d: /ð/g,
      e: /è|é|ê|ë/g,
      i: /ì|í|î|ï/g,
      n: /ñ/g,
      o: /ø|ö|õ|ô|ó|ò/g,
      u: /ü|û|ú|ù/g,
      y: /ÿ|ý/g,
      ae: /æ/g,
      oe: /œ/g
    }
  }
  text = text.toLowerCase();
  for (var i in typeahead_source.accents) {
    text = text.replace(typeahead_source.accents[i], i);
  }
  return text;
}

// sets the owner (i.e. typeahead) of this source
typeahead_source.prototype.set_owner = function(obj) {
  this.owner = obj;
  if (this.is_ready) {
    this.owner.update_status(typeaheadpro.STATUS_IDLE);
  }
}

// this source is ready to search
typeahead_source.prototype.ready = function() {
  if (this.owner && !this.is_ready) {
    this.is_ready = true;
    this.owner.update_status(typeaheadpro.STATUS_IDLE);
  } else {
    this.is_ready = true;
  }
}

// highlights found text with searched text
/* static */ typeahead_source.highlight_found = function(result, search) {
  var html = [];
  resultv = typeahead_source.tokenize(result, true, true);
  result = typeahead_source.tokenize(result, true);
  search = typeahead_source.tokenize(search);
  search.sort(typeahead_source._sort); // do this to make sure the larger piece gets matched first
  for (var i = 0, il = resultv.length; i < il; i++) {
    var found = false;
    for (var j = 0, jl = search.length; j < jl; j++) {
      if (search[j] && result[i].lastIndexOf(search[j], 0) != -1) { // does this result[i] start with search[j]
        html.push('<em>', htmlspecialchars(resultv[i].substring(0, search[j].length)), '</em>', htmlspecialchars(resultv[i].substring(search[j].length, resultv[i].length)));
        found = true;
        break;
      }
    }
    if (!found) {
      html.push(htmlspecialchars(resultv[i]));
    }
  }

  return html.join('');
}

// helper function for sorting tokens
/* static */ typeahead_source._sort = function(a, b) {
  return b.length - a.length;
}

// returns error text for when nothing was found
typeahead_source.prototype.gen_nomatch = function() {
  return this.text_nomatch != null ? this.text_nomatch : tx('ta01');
}

// returns message in case the selector is still loading
typeahead_source.prototype.gen_loading = function() {
  return this.text_loading != null ? this.text_loading : tx('ta02');
}

// returns filler text for when the user hasn't typed anything in
typeahead_source.prototype.gen_placeholder = function() {
  return this.text_placeholder != null ? this.text_placeholder : tx('ta03');
}

// returns filler text for when the user hasn't typed anything in
typeahead_source.prototype.gen_noinput = function() {
  return this.text_noinput != null ? this.text_noinput : tx('ta03');
}

typeahead_source.prototype.onselect_not_found = function() {
  if (typeof this.tokenizer._ontokennotfound != 'undefined') {
    this.tokenizer._ontokennotfound(this.obj.value);
  }

  if (typeof this.tokenizer.onselect != 'undefined') {
    return this.tokenizer.onselect();
  }
}

//
// static source base class. use this if you have a set list of this to search for that can be handled totally on the client-side
// =======================================================================================
function static_source() {
  this.values = null;
  this.index = null;
  this.index_includes_hints = false;
  this.exclude_ids = {};
  this.parent.construct(this);
}
static_source.extend(typeahead_source);
static_source.prototype.enumerable = true;

// builds a sorted index for us to use in a binary search
static_source.prototype.build_index = function(no_defer) {
  var index = [];
  var values = this.values;
  var gen_id = values.length && typeof values[0].i == 'undefined'; // generate our own ids for these
  for (var i = 0, il = values.length; i < il; i++) {
    var tokens = typeahead_source.tokenize(values[i].t);
    for (var j = 0, jl = tokens.length; j < jl; j++) {
      index.push({t:tokens[j], o:values[i]});
    }
    // also include the sub-tag label in the index
    if (this.index_includes_hints && values[i].s) {
      var tokens = typeahead_source.tokenize(values[i].s);
      for (var j = 0, jl = tokens.length; j < jl; j++) {
        index.push({t:tokens[j], o:values[i]});
      }
    }


    if (gen_id) {
      values[i].i = i;
    }
  }

  // This can take some time, let's defer it
  var index_sort_and_ready = function () {
    index.sort(function(a,b) {return (a.t == b.t) ? 0 : (a.t < b.t ? -1 : 1)});
    this.index = index;
    this.ready();
  }.bind(this);
  if (no_defer) {
    index_sort_and_ready();
  } else {
    index_sort_and_ready.defer();
  }
}

// we want email addresses to always be displayed at the
// bottom of the list, to keep the friend selector
// relatively clean
static_source.prototype._sort_text_obj = function(a, b) {
  if (a.e && !b.e) {
    return 1;
  }
  if (!a.e && b.e) {
    return -1;
  }
  if (a.t == b.t) {
    return 0;
  }
  return a.t < b.t ? -1 : 1
}

// searches the values list for some text and returns those to the typeahead
static_source.prototype.search_value = function(text) {
  if (!this.is_ready) {
    return;
  }

  var results;
  if (text == '') {
    results = this.values;
  } else {
    var ttext = typeahead_source.tokenize(text).sort(typeahead_source._sort);
    var index = this.index;
    var lo = 0;
    var hi = this.index.length - 1;
    var p  = Math.floor(hi / 2);

    // first we go through and set our cursor to the start of the most restrictive match in the index
    while (lo <= hi) {
      if (index[p].t >= ttext[0]) {
        hi = p - 1;
      } else {
        lo = p + 1;
      }
      p = Math.floor(lo + ((hi-lo) / 2));
    }

    // now match the rest of the tokens
    // note: it would be nice if we could break this loop after we get search_limit results, but we can't.
    // since they're going to be in the order of the index, names will look scattered and unorganized to the
    // user. instead we just grab all the names that match, and then sort them later.
    var results = [];
    var stale_keys = {};
    var check_ignore = typeof _ignoreList != 'undefined';
    for (var i=lo; i<index.length && index[i].t.lastIndexOf(ttext[0], 0) != -1; i++) {
      var elem_id = index[i].o.flid ? index[i].o.flid : index[i].o.i;
      if (typeof stale_keys[elem_id] != 'undefined') {
        continue;
      } else {
        stale_keys[elem_id] = true;
      }
      if ((!check_ignore || !_ignoreList[elem_id])
          && !this.exclude_ids[elem_id]
          && (ttext.length == 1 || typeahead_source.check_match(ttext, index[i].o.t))) {
        results.push(index[i].o);
      }
    }
  }

  // sort and pull the top n results
  results.sort(this._sort_text_obj);
  if (this.owner.max_results) {
    results = results.slice(0, this.owner.max_results);
  }

  return results;
}

static_source.prototype.set_exclude_ids = function(ids) {
  this.exclude_ids = ids;
}

//
// friend source for typeaheads
// =======================================================================================
function friend_source(get_param) {
  this.parent.construct(this);

  if (friend_source.friends[get_param]) {
    this.values = friend_source.friends[get_param];
    this.index = friend_source.friends_index[get_param];
    this.ready();
  } else {
    new AsyncRequest()
      .setMethod('GET')
      .setReadOnly(true)
      .setURI('/ajax/typeahead_friends.php?' + get_param)
      .setHandler(function(response) {
                    friend_source.friends[get_param]
                      = this.values
                      = response.getPayload().friends;
                    this.build_index();
                    friend_source.friends_index[get_param] = this.index;
                  }.bind(this))
      .send();
  }
}
friend_source.extend(static_source);
friend_source.prototype.text_noinput =
friend_source.prototype.text_placeholder = tx('ta04');
friend_source.friends = {};
friend_source.friends_index = {};
friend_source.prototype.cache_results = true;

// generates html for this friend's typeahead
friend_source.prototype.gen_html = function(friend, highlight) {
  var text = friend.n;
  if (friend.n === false) {
    // empty friend list
    text = tx('ta16');
  } else if (typeof(friend.n) == "object") {
    var names = [];
    for (var k in friend.n) {
      names.push(friend.n[k]);
    }
    if (names.length > 3) {
      text = tx('ta15', {name1: names[0],
                         name2: names[1],
                         count: names.length - 2});
    } else if (names.length) {
      text = names.join(', ');
    } else {
      text = tx('ta16');
    }
  }
  return ['<div>', typeahead_source.highlight_found(friend.t, highlight), '</div><div><small>', text, '</small></div>'].join('');
}

// searches the friends list for some text and returns those to the typeahead
friend_source.prototype.search_value = function(text) {
  if (text == '\x5e\x5f\x5e') { // early sentinel value
    return [{t:text,n:'\x6b\x65\x6b\x65',i:10,it:'http://static.ak.facebook.com/pics/t_default.jpg'}];
  }
  return this.parent.search_value(text);
}

function friendlist_source(get_param) {
  this.parent.construct(this, get_param);
}
friendlist_source.extend(friend_source);

friendlist_source.prototype.friend_lists = false;
friendlist_source.prototype.text_placeholder = tx('ta18');

friendlist_source.prototype.return_friend_lists = function() {
  if (!this.friend_lists || (this.friend_lists && this.friend_lists.length == 0)) {
    this.friend_lists = [];
    var index = this.index;
    var results = [];
    var pushed = [];
    if (!index.length || !(index.length >= 1)) {
      return;
    }
    for (var i=0; i < index.length; i++) {
      if (index[i].o.flid && !pushed[index[i].o.flid]) {
        pushed[index[i].o.flid] = true;
        results.push(index[i].o);
      }
    }

    // sort results
    var results_sorted = results.sort(function(a, b) { if (a.t > b.t) return 1; else if (a.t < b.t) return -1; else return 0; });

    this.friend_lists = results_sorted;
  }
  return this.friend_lists;
}

friendlist_source.prototype.search_value = function(text) {
  if (text == '**FRIENDLISTS**') {
    return this.return_friend_lists();
  }
  return this.parent.search_value(text);
}

friendlist_source.prototype.gen_nomatch = function() {
  if (this.showing_icon_list) {
    return tx('ta17');
  } else {
    return this.parent.gen_nomatch();
  }
}

//
// friend and email source
// acts as a friend-finder, with additional ability to accept email addresses as well
// =======================================================================================
function friend_and_email_source(get_param) {
    get_param = get_param ? get_param + '&include_emails=1' : '';
    this.parent.construct(this, get_param);
}
friend_and_email_source.extend(friend_source);
friend_and_email_source.prototype.text_noinput =
friend_and_email_source.prototype.text_placeholder = tx('ta05');
friend_and_email_source.prototype.text_nomatch = tx('ta06');

friend_and_email_source.prototype.onselect_not_found = function() {

  // the loop catches the case where someone copy/pastes a bunch of emails in at once
  emails = this.results_text.split(/[,; ]/);

  for (var i = 0; i < emails.length; i++) {

    // only execute if it looks like an email. it's okay if this email_regex
    // doesn't handle every possible case .. if an invalid email is entered,
    // then the handling form will reject it on submission and display an error in the prefill
    var text = emails[i].replace(/^\s+|\s+$/g, '');
    var email_regex = /.*\@.*\.[a-z]+$/;

    if (!email_regex.test(text)) {
      continue;
    }

    var email_entry = {t:text, e:text};
    var new_token = new token(email_entry, this.tokenizer, this.element);

    // the ajax call is executed in the context of the token. this is necessary
    // because the tokenizer_input might be destroyed by the time the
    // call returns, so it needs something that will reliably be there

    var async_params = { email : text };
      new AsyncRequest()
        .setMethod('GET')
        .setReadOnly(true)
        .setURI('/ajax/typeahead_email.php')
        .setData(async_params)
        .setHandler(function(response) {
                      if (response.getPayload()) {
                        this.render_obj(response.getPayload().token);
                      }
                    }.bind(new_token))
        .send();
  }
  this.clear();
}

//
// network source for networks and stuff... when needed this should be further abstracted to ajax_source -> network_source
// =======================================================================================
function network_source(get_selected_type) {
  this.get_selected_type = get_selected_type;
  this.parent.construct(this);
  this.ready();
}
network_source.extend(typeahead_source);
network_source.prototype.cache_results = true;
network_source.prototype.search_limit = 200;   // how often can we run a query?
network_source.prototype.text_placeholder=network_source.prototype.text_noinput=tx('ta07');
network_source.prototype.base_uri='';
network_source.prototype.allow_fake_results = true;

// sends a query to look for the network. the owner won't call this until we respond with found_suggestions, so we don't have to implement any kind of throttling here.
network_source.prototype.search_value = function(text) {
  this.search_text = text;
  var async_params = { q : text };

  // type is settable by both 'get_selected_type' and 't'
  if ((type = typeof(this.get_selected_type)) != 'undefined') {
    async_params['t'] = (type != 'string')?JSON.encode(this.get_selected_type):this.get_selected_type;
  }
  if ((type = typeof(this.t)) != 'undefined') {
    async_params['t'] = (type != 'string')?JSON.encode(this.t):this.t;
  }

  // show_email and show_network_type can be switched on
  if (this.show_email) {
    async_params['show_email'] = 1;
  }
  if (this.show_network_type) {
    async_params['show_network_type'] = 1;
  }
  if (this.disable_school_status) {
    async_params['disable_school_status'] = 1;
  }

  new AsyncRequest()
  .setReadOnly(true)
  .setMethod('GET')
  .setURI('/ajax/typeahead_networks.php')
  .setData(async_params)
  .setHandler(function(response) {
                this.owner.found_suggestions(response.getPayload(), this.search_text);
              }.bind(this))
  .setErrorHandler(function(response) {
                     this.owner.found_suggestions(false, this.search_text);
                   }.bind(this))
  .send();
}

// generates html for this result
network_source.prototype.gen_html = function(result, highlight) {
  return ['<div>',
            typeahead_source.highlight_found(result.t, highlight),
          '</div><div><small>',
            typeahead_source.highlight_found(result.l, highlight),
          '</small></div>'].join('');
}

//
// custom source -- pass it an array of stuff and it'll autocomplete from the list
function custom_source(options) {
  this.parent.construct(this);

  //  If the caller passed an array of strings, convert them to canonical
  //  typeahead format: objects with a (t)oken and (i)ndex field.
  if (options.length && typeof(options[0]) == "string") {
    for (var ii = 0; ii < options.length; ii++) {
      options[ii] = {t: options[ii], i: options[ii]};
    }
  }

  this.values = options;
  this.build_index();
}
custom_source.extend(static_source);
custom_source.prototype.text_placeholder =
custom_source.prototype.text_noinput = false;

// generates html for this result
custom_source.prototype.gen_html = function(result, highlight) {
  var html = ['<div>', typeahead_source.highlight_found(result.t, highlight), '</div>'];
  if (result.s) {
    html.push('<div><small>', htmlspecialchars(result.s), '</small></div>');
  }
  return html.join('');
}

//
// concentration source, for college majors\minors. this one is kind of interesting because we will probably have more than one from the same college on the page at once.
// =======================================================================================
function concentration_source(get_network) {
  this.parent.construct(this, []);
  this.network=get_network;

  // perhaps we already have these concentrations in static...
  if (!concentration_source.networks) {
    concentration_source.networks = [];
  } else {
    for (var i = 0, il = concentration_source.networks.length; i < il; i++) {
      if (concentration_source.networks[i].n == this.network) {
        this.values = concentration_source.networks[i].v;
        this.index = concentration_source.networks[i].i;
        this.ready();
        return;
      }
    }
  }

  // couldn't find the concentrations, get them from ajax
  new AsyncRequest()
    .setURI('/ajax/typeahead_concentrations.php?n=' + this.network)
    .setHandler(function(response) {
      this.values = response.getPayload();
      this.build_index();
      concentration_source.networks.push({n:this.network, v:this.values, i:this.index});
      this.ready();
    }.bind(this))
    .send();
}
concentration_source.extend(custom_source);
concentration_source.prototype.noinput = false;
concentration_source.prototype.text_placeholder = tx('ta08');
concentration_source.prototype.allow_fake_results = true;


function language_source() {
  this.parent.construct(this, []);

  // perhaps we already have these languages in static...
  if (!language_source.languages) {
    language_source.languages = [];
  } else {
    for (var i = 0, il = language_source.languages.length; i < il; i++) {
      this.values = language_source.languages[i].v;
      this.index = language_source.languages[i].i;
      this.ready();
      return;
    }
  }

  // couldn't find the concentrations, get them from ajax
  new AsyncRequest()
    .setURI('/ajax/typeahead_languages.php')
    .setHandler(function(response) {
      this.values = response.getPayload();
      this.build_index();
      language_source.languages.push({v:this.values, i:this.index});
      this.ready();
    }.bind(this))
    .send();
}
language_source.extend(custom_source);
language_source.prototype.noinput = false;
language_source.prototype.text_placeholder = tx('ta14');
language_source.prototype.allow_fake_results = false;

//
// Targeting keyword source.
// =======================================================================================
function keyword_source(get_category) {
  this.parent.construct(this, []);
  this.category = get_category;

  if (!keyword_source.categories) {
    keyword_source.categories = [];
  } else {
    for (var i = 0, il = keyword_source.categories.length; i < il; i++) {
      if (keyword_source.categories[i].c == this.category) {
        this.values = keyword_source.categories[i].v;
        this.index = keyword_source.categories[i].i;
        this.ready();
        return;
      }
    }
  }

  new AsyncRequest()
    .setURI('/ajax/typeahead_keywords.php')
    .setData({ c : this.category })
    .setMethod('GET')
    .setReadOnly(true)
    .setHandler(function(response) {
                  this.values = response.getPayload();
                  this.build_index();
                  keyword_source.categories.push({c:this.category, v:this.values, i:this.index});
                  this.ready();
                }.bind(this))
    .send();
}
keyword_source.extend(custom_source);
keyword_source.prototype.noinput = false;
keyword_source.prototype.text_placeholder = tx('ta09');

//
// Targeting regions source
// =======================================================================================
function regions_source(get_iso2) {
  this.parent.construct(this, []);
  this.country = get_iso2;
  this.reload();
}
regions_source.extend(custom_source);
regions_source.prototype.noinput = false;
regions_source.prototype.text_placeholder = tx('ta10');
regions_source.prototype.reload = function() {
  new AsyncRequest()
  .setMethod('GET')
  .setReadOnly(true)
  .setURI('/ajax/typeahead_regions.php')
  .setData({c : this.country})
  .setHandler(function(response) {
                this.values = response.getPayload();
                this.build_index();
                this.ready();
              }.bind(this))
  .send();
}

//
// Time selector for date selector pro
// To be re-written to not use ajax hopefully
// =======================================================================================
function time_source() {
  this.status=0;
  this.parent.construct(this);
}
time_source.extend(typeahead_source);
time_source.prototype.cache_results = true;
time_source.prototype.text_placeholder=time_source.prototype.text_noinput=tx('ta11');
time_source.prototype.base_uri='';

// sends a query to look for the network. the owner won't call this until we respond with found_suggestions, so we don't have to implement any kind of throttling here.
time_source.prototype.search_value=function(text) {
  this.search_text=text;
  var async_params = { q : text };
  new AsyncRequest()
  .setURI('/ajax/typeahead_time.php')
  .setMethod('GET')
  .setReadOnly(true)
  .setData(async_params)
  .setHandler(function(response) {
                this.owner.found_suggestions(response.getPayload(), this.search_text);
              }.bind(this))
  .setErrorHandler(function(response) {
                     this.owner.found_suggestions(false, this.search_text);
                   }.bind(this))
  .send();
}

// generates html for this result
time_source.prototype.gen_html=function(result, highlight) {
  return ['<div>', typeahead_source.highlight_found(result.t, highlight), '</div>'].join('');
}

function dynamic_custom_source(async_url) {
  this.async_url = async_url;
  this.parent.construct(this);
}
dynamic_custom_source.extend(typeahead_source);
dynamic_custom_source.cache_results = true;

dynamic_custom_source.prototype.search_value = function(text) {
  this.search_text = text;
  var async_params = { q : text };
  var r = new AsyncRequest()
    .setURI(this.async_url)
    .setData(async_params)
    .setHandler(bind(this, function(r) {
      this.owner.found_suggestions(r.getPayload(), this.search_text, false);
    }))
    .setErrorHandler(bind(this, function(r) {
      this.owner.found_suggestions(false, this.search_text, false);
    }))
    .setReadOnly(true)
    .send()
}

dynamic_custom_source.prototype.gen_html=function(result, highlight) {
  var html = ['<div>', this.highlight_found(result.t, highlight), '</div>'];
  if (result.s) {
    html.push('<div class="sub_result"><small>', result.s, '</small></div>');
  }

  return html.join('');
}

dynamic_custom_source.prototype.highlight_found = function(result, search) {
  return typeahead_source.highlight_found(result, search);
}


// Ad targting cluster source
//=================================================
function ad_targeting_cluster_source(act) {
  this.parent.construct(this, []);

  // See if clusters are already cached in this browser instance
  if (!ad_targeting_cluster_source.clusters) {
    ad_targeting_cluster_source.clusters = [];
  } else {
    for (var i = 0, il = ad_targeting_cluster_source.clusters.length; i < il; i++) {
      this.values = ad_targeting_cluster_source.clusters[i].v;
      this.index  = ad_targeting_cluster_source.clusters[i].i;
      this.ready();
      return;
    }
  }

  // Couldn't find the clusters, get them from ajax
  new AsyncRequest()
    .setURI('/ads/ajax/typeahead_clusters.php')
    .setData({'act' : act})
    .setHandler(function(response) {
        this.values = response.getPayload();
        this.build_index();
        ad_targeting_cluster_source.clusters.push({v:this.values, i:this.index});
        this.ready();
        }.bind(this))
  .send();
}

ad_targeting_cluster_source.extend(custom_source);
