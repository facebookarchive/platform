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
 *  @provides legacy-base sound
 *  @requires control-textinput control-textarea control-dom dom css link-controller
 */

//  Really primitive shield against double-inclusion. This could be much more
//  sophisticated, but the problem is fairly rare; we mostly just want to be
//  less mysterious about it than having string.split recurse indefinitely.
try {
  if (window.fbJavascriptLibrariesHaveLoaded) {
    Util.error(
      'You have double-included base.js and possibly other Javascript files; ' +
      'it may be in a package. This will cause you great unhappiness. Each '   +
      'file should be included at most once.');
  }
  window.fbJavascriptLibrariesHaveLoaded = true;
} catch(ignored) { }

function gen_unique() {
  return ++gen_unique._counter;
}
gen_unique._counter = 0;

function close_more_list() {
  var list_expander = ge('expandable_more');
  if (list_expander) {
    list_expander.style.display = 'none';
    removeEventBase(document, 'click', list_expander.offclick, list_expander.id);
  }

  var sponsor = ge('ssponsor');
  if (sponsor) {
    sponsor.style.position = '';
  }

  var link_obj= ge('more_link');
  if (link_obj) {
    link_obj.innerHTML = tx('base01');
    link_obj.className = 'expand_link more_apps';
  }
}


function expand_more_list() {

  var list_expander = ge('expandable_more');

  // remove highlight if there is one
  var more_link = ge('more_section');
  if (more_link) {
    remove_css_class_name(more_link, 'highlight_more_link');
  }

  if (list_expander) {
    list_expander.style.display = 'block';
    list_expander.offclick = function(e) {
      if (!is_descendent(event_get_target(e), 'sidebar_content')) {
        close_more_list();
      }
    }.bind(list_expander);

    addEventBase(document, 'click', list_expander.offclick, list_expander.id);
  }

  var sponsor =  ge('ssponsor');
  if (sponsor) {
    sponsor.style.position = 'static';
  }

  var link_obj= ge('more_link');
  if (link_obj) {
    link_obj.innerHTML = tx('base02');
    link_obj.className = 'expand_link less_apps';
  }
}


function create_hidden_input(name, value) {
  return $N('input', {name: name, id: name, value: value, type: 'hidden'});
}


// === Event Info Access ===

var KEYS = { BACKSPACE: 8,
             TAB:       9,
             RETURN:   13,
             ESC:      27,
             SPACE:    32,
             LEFT:     37,
             UP:       38,
             RIGHT:    39,
             DOWN:     40,
             DELETE:   46 };

var KeyCodes = {
  Up : 63232,
  Down: 63233,
  Left : 63234,
  Right : 63235
};



// === Dropdown Menus ===

/* functionality for an optional drop down menu (example: drop downs in the
nav.) It consists of a link, an arrow, and a menu which appears when the
arrow is clicked. Pass this function an arrow, link, and menu element

arrow_class and arrow_old_class and offset_el is optional
*/
function optional_drop_down_menu(arrow, link, menu, event, arrow_class, arrow_old_class, on_click_callback, off_click_callback, offset_el, offset_info)
{
  if (menu.style.display=='none') {
    menu.style.display='block';
    // if we need to move this menu for z-index reasons, do so.
    if (offset_el && offset_info) {
      for (prop in offset_info) {
        switch(prop) {
          case 'top':
            menu.style.top = (offset_el.offsetTop
                              + offset_info[prop])
                           + 'px';
            break;
          case 'left':
            menu.style.left = (offset_el.offsetLeft
                               + offset_info[prop])
                            + 'px';
            break;
          case 'right':
            menu.style.left = (offset_el.offsetLeft
                               + offset_el.offsetWidth
                               - offset_info[prop]
                               - menu.offsetWidth)
                            +'px';
            break;
          case 'bottom':
            menu.style.top = (offset_el.offsetTop
                              + offset_el.offsetHeight
                              - offset_info[prop]
                              - menu.offsetHeight)
                           + 'px';
            break;
        }
      }
    }

    if (arrow) {
      var old_arrow_classname = arrow_old_class ? arrow_old_class : arrow.className;
    }

    // Lock In Button Pressed State
    if (link) {
      link.className = 'active';
    }

    if (arrow) {
      arrow.className = arrow_class ? arrow_class : 'global_menu_arrow_active';
    }

    var justChanged = true;

    // prevent selects from showing through menu in ie6
    var shim = ge(menu.id + '_iframe');
    if (shim) {
      shim.style.top = menu.style.top;
      shim.style.right = menu.style.right;
      shim.style.display = 'block';
      shim.style.width = (menu.offsetWidth +2) + 'px';
      shim.style.height = (menu.offsetHeight +2) + 'px';
    }

    menu.offclick = function(e) {
      if (!justChanged) {
        // Hide dropdown
        hide(this);

        // Restore Normal link and hover class
        if (link) {
          link.className = '';
        }
        if (arrow) {
          arrow.className = old_arrow_classname;
        }

        var shim = ge(menu.id + '_iframe');
        if (shim) {
          shim.style.display = 'none';
          shim.style.width = menu.offsetWidth + 'px';
          shim.style.height = menu.offsetHeight + 'px';
        }
        if (off_click_callback) { off_click_callback(e); }
        removeEventBase(document, 'click', this.offclick, menu.id);
      } else {
        justChanged = false;
      }
    }.bind(menu);
    if (on_click_callback) { on_click_callback(); }
    addEventBase(document, 'click', menu.offclick, menu.id);
    onunloadRegister(menu.offclick, true);
  }
  return false;
}


/* special case for the app_switcher mneu, we need to set its position since it's right-aligned */
function position_app_switcher() {
  var switcher = ge('app_switcher');
  var menu = ge('app_switcher_menu');
  menu.style.top = (switcher.offsetHeight - 1) + 'px';
  menu.style.right = '0px';
}



// EXPERIMENTAL: generic tooltip class
function hover_tooltip(object, hover_link, hover_class, offsetX, offsetY) {

  if (object.tooltip) {
    var tooltip = object.previousSibling;
    tooltip.style.display = 'block';
    return;
  } else {

    object.parentNode.style.position = "relative";
    var tooltip = document.createElement('div');
    tooltip.className = "tooltip_pro " + hover_class;
    tooltip.style.left=-9999 + 'px';
    tooltip.style.display = 'block';
    tooltip.innerHTML = '<div class="tooltip_text"><span>' + hover_link + '</span></div>' +
      '<div class="tooltip_pointer"></div>';

    object.parentNode.insertBefore(tooltip, object);

    while (tooltip.firstChild.firstChild.offsetWidth <= 0) {
      1;
    }

    var TOOLTIP_PADDING = 16;
    var offsetWidth = tooltip.firstChild.firstChild.offsetWidth + TOOLTIP_PADDING;

    tooltip.style.width = offsetWidth + 'px'; //We need to set the width because of stupid IE

    tooltip.style.display = 'none';

    // calculate where it should go before we make it visible so there's no jerky motion
    tooltip.style.left = offsetX + object.offsetLeft - ((offsetWidth -6 - object.offsetWidth) / 2) + 'px';
    tooltip.style.top = offsetY + 'px';
    tooltip.style.display = 'block';

    object.tooltip = true;

    object.onmouseout = function(e) { hover_clear_tooltip(object) };
  }
}


function hover_clear_tooltip(object) {
  var tooltip = object.previousSibling;
  tooltip.style.display = 'none';
}

function goURI(href) {
  window.location.href = href;
}


function getTableRowShownDisplayProperty() {
  if (ua.ie()) {
    return  'inline';
  } else {
    return 'table-row';
  }
}

function showTableRow()
{
  for ( var i = 0; i < arguments.length; i++ ) {
    var element = ge(arguments[i]);
    if (element && element.style) element.style.display =
        getTableRowShownDisplayProperty();
  }
  return false;
}

function getParentRow(el) {
    el = ge(el);
    while (el.tagName && el.tagName != "TR") {
        el = el.parentNode;
    }
    return el;
}

function show_standard_status(status) {
  s = ge('standard_status');
  if (s) {
    var header = s.firstChild;
    header.innerHTML = status;
    show('standard_status');
  }
}

function hide_standard_status() {
  s = ge('standard_status');
  if (s) {
    hide('standard_status');
  }
}

function adjustImage(obj, stop_word, max) {
  var block = obj.parentNode;
  while (get_style(block, 'display') != 'block' && block.parentNode) {
    block = block.parentNode;
  }

  var width = block.offsetWidth;
  if (obj.offsetWidth > width) {
    try {
      // Internet Explorer's image scaling (as of IE7) looks like poo poo. So what we do to make these look better is pull out the <img />
      // and instead use a <div /> with progid:DXImageTransform, which looks a lot smoother.
      if (ua.ie()) {
          var img_div = document.createElement('div');
          img_div.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' + obj.src.replace('"', '%22') + '", sizingMethod="scale")';
          img_div.style.width = width + 'px';
          img_div.style.height = Math.floor(((width / obj.offsetWidth) * obj.offsetHeight))+'px';
          if (obj.parentNode.tagName == 'A') {
            img_div.style.cursor = 'pointer';
          }
          obj.parentNode.insertBefore(img_div, obj);
          obj.parentNode.removeChild(obj);
      } else {
        throw 1;
      }
    } catch (e) {
      obj.style.width = width + 'px';
    }
  }
  remove_css_class_name(obj, 'img_loading');
}

function imageConstrainSize(src, maxX, maxY, placeholderid) {
  var image = new Image();
  image.onload = function() {
    if (image.width > 0 && image.height > 0) {
      var width = image.width;
      var height = image.height;
      if (width > maxX || height > maxY) {
        var desired_ratio = maxY/maxX;
        var actual_ratio = height/width;
        if (actual_ratio > desired_ratio) {
          width = width * (maxY / height);
          height = maxY;
        } else {
          height = height * (maxX / width);
          width = maxX;
        }
      }
      var placeholder = ge(placeholderid);
      var newimage = document.createElement('img');
      newimage.src = src;
      newimage.width = width;
      newimage.height = height;
      placeholder.parentNode.insertBefore(newimage, placeholder);
      placeholder.parentNode.removeChild(placeholder);
    }
  }
  image.src = src;
}

function login_form_change() {
  var persistent = ge('persistent');
  if (persistent) {
    persistent.checked = false;
  }
}

// Note: this is SAFE to call from non-secure pages because it uses fun img\ssl hackery
function require_password_confirmation(onsuccess, oncancel) {
  if ((!getCookie('sid') || getCookie('sid') == '0') || getCookie('pk')) {
    onsuccess();
    return;
  }
  require_password_confirmation.onsuccess = onsuccess;
  require_password_confirmation.oncancel = oncancel;
  (new pop_dialog()).show_ajax_dialog('/ajax/password_check_dialog.php');
}

function search_validate(search_input_id) {
  var search_input = $(search_input_id);

  if (search_input.value != "" &&
      search_input.value != search_input.getAttribute('placeholder')) {
    return true;
  } else {
    //  TODO: Provide a dropdown suggestion that reads
    //  "Please enter a search term" or something to that effect;
    //  for now, we'll just focus the search field, ala Google
    search_input.focus();
    return false;
  }
}

function abTest(data, inline)
{
  AsyncRequest.pingURI('/ajax/abtest.php', {data: data, "post_form_id": null}, true);
  if (!inline) {
    return true;
  }
}

function ac(metadata)
{
  AsyncRequest.pingURI('/ajax/ac.php', {'meta':metadata}, true);
  return true;
}


function alc(metadata)
{
  AsyncRequest.pingURI('/ajax/alc.php', {'meta':metadata}, true);
  return true;
}

function scribe_log(category, message) {
  AsyncRequest.pingURI('/ajax/scribe_log.php', {'category':category, 'message':message, 'post_form_id': null}, true);
}

function play_sound(path, loop) {
  loop = loop||false;

  var s = ge('sound');
  if (!s) {
    s = document.createElement('span');
    s.setAttribute('id', 'sound');
    document.body.appendChild(s);
  }
  s.innerHTML = '<embed src="'+path+'" autostart="true" hidden="true" '+
                'loop="'+(loop?"true":"false")+'" />';
}

// Returns true if an img object has loaded
function image_has_loaded(obj) {

  try {
    if (
      (obj.mimeType!=null && obj.complete && obj.mimeType!='') ||       // ie && safari 3
      (obj.naturalHeight!=null && obj.complete && obj.naturalHeight!=0) // ff
     ) {
      return true;
    } else if (ua.safari() < 3) {
      // workaround for safari 2... complete property only shows up when images are created through JS
      var new_image = new Image();
      new_image.src = obj.src;
      if (new_image.complete == true) {
        return true;
      }
      delete new_image;
    }

  } catch (exception) {

    //  IE7 is throwing an "unspecified error" when you try to look at
    //  properties of `obj' and this fixes it and alert() changes the behavior
    //  and I don't know why it's so upset at the image and this is "unbreak
    //  now!" so this is the high level of quality you get out of me. See
    //  Trac #6956.

    return true;
  }

}

// returns true if an img object has failed to load
function image_has_failed(obj) {
  if (
  (obj.complete==null && obj.width==20 && obj.height==20) ||        // safari - failed images are 20x20
  (obj.mimeType!=null && obj.complete && obj.mimeType=='') ||       // ie - failed images have no mime type
  (obj.naturalHeight!=null && obj.complete && obj.naturalHeight==0) // firefox - failed images have 0 naturalheight
 ) {                                                                               // opera - falls into one of these categories and simply works
   return true;
 }
}

function cavalry_log(cohort, server_time) {

  if (!window.Env) {
    return;
  }

  window.scrollBy(0,1);

  var t = [
    server_time,
    ___tcss,
    ___tjs + ___tcss,
    ___thtml + ___tcss + ___tjs,
    parseInt(Env.t_domcontent - Env.start, 10),
    parseInt(Env.t_onload - Env.start, 10),
    parseInt(Env.t_layout - Env.start, 10),
    parseInt(((new Date()).getTime()) - Env.start, 10),
    parseInt(Env.t_doneonloadhooks - Env.t_willonloadhooks, 10)
  ];

  (new Image()).src = "/common/instrument_endpoint.php?"
    + "g="+cohort
    +"&uri="+encodeURIComponent(window.location)
    +"&t="+t.join(',')
    +"&"+parseInt(Math.random()*10000, 10);
}

/**
 * When the user clicks on the name/picture of someone who they can only see
 * the search profile of, bring it up in a dialog box, rather than sending
 * them to s.php.
 */
function show_search_profile(user_id) {
  var async = new AsyncRequest()
    .setURI('/ajax/search_profile.php')
    .setData({id: user_id})
    .setMethod('GET')
    .setReadOnly(true);
  new Dialog()
    .setAsync(async)
    .setButtons(Dialog.CLOSE)
    .setContentWidth(490)
    .show();
}
function _search_profile_link_handler(link) {
  // Look for links that were generated by the get_search_profile_url PHP
  // function, e.g. facebook.com/s.php?k=100000080&id=500011067, and make
  // it so if the user clicks one, we show them the equivalent content
  // in a dialog box instead.
  var uri = new URI(link.href);
  if (uri.getPath() == '/s.php') {
    var query = uri.getQueryData();
    if (query.k == 100000080 /* KEY_USERID */) {
      show_search_profile(query.id);
      return false;
    }
  }
}
onloadRegister(function() {
  LinkController.registerHandler(_search_profile_link_handler);
});

/**
 * Makes it so that, if the user edits the given form, and then tries to
 * navigate away from the page without submitting the form, s/he will first
 * get prompted with a dialog box to confirm leaving.
 *
 * See render_start_form_with_unsaved_warning.
 */
function warn_if_unsaved(form_id) {
  var form = ge(form_id);

  if (!form) {
    Util.error("warn_if_unsaved couldn't find form in order to save its "
             + "original state.  This is probably because you called "
             + "render_start_form_with_unsaved_warning to render a form, "
             + "but then didn't echo it into page.  To get around this, you "
             + "can call render_start_form, and then call warn_if_unsaved "
             + "yourself once you've caused the form to appear.");
    return;
  }

  if (!_unsaved_forms_to_check_for) {
    // Means it's the first time we're calling warn_if_unsaved.
    _unsaved_forms_to_check_for = {};
    LinkController.registerHandler(_check_for_unsaved_forms);
  }

  form.original_state = serialize_form(form);
  _unsaved_forms_to_check_for[form_id] = true;
}
function _check_for_unsaved_forms(link) {
  for (var form_id in _unsaved_forms_to_check_for) {
    var form = ge(form_id);
    if (form && form.original_state &&
        !are_equal(form.original_state, serialize_form(form))) {
      var href = link.href;
      // TODO: someday this will have to play more nicely
      // with Quickling / other onclick handlers.

      var submit = _find_first_submit_button(form);
      var buttons = [];
      if (submit) {
        buttons.push({ name: 'save', label: tx('sh:save-button'),
                       handler: bind(submit, 'click') });
      }
      buttons.push({ name: 'dont_save', label: tx('uw:dont-save'),
                     handler: function() { window.location.href = href; } });
      buttons.push(Dialog.CANCEL);

      new Dialog()
        .setTitle(tx('uw:title'))
        .setBody(tx('uw:body'))
        .setButtons(buttons)
        .setModal()
        .show();
      return false;
    }
  }
}
function _find_first_submit_button(root_element) {
  var inputs = root_element.getElementsByTagName('input');
  for (var i = 0; i < inputs.length; ++i) {
    if (inputs[i].type.toUpperCase() == 'SUBMIT') {
      return inputs[i];
    }
  }
  return null;
}
_unsaved_forms_to_check_for = undefined;


/* -( Bootstrap )------------------------------------------------------------ */

  //  This section contains code which runs implicitly when this file is
  //  included. Please put implicitly-running non-definition code here so we can
  //  keep track of what's going on.

        ua.populate();
        _bootstrapEventHandlers();
        adjustUABehaviors();

        // Lower the page domain.  This allows our iframes to communicate with
        // their parent window even if they were served by another subdomain.
        // If you write an iframe that needs to use "window.parent", make sure
        // you either include base.js, or run this line manually.
        // Also, NEVER use navigator.userAgent in your own code. The reason it is
        // used here instead of the ua object is for consistency on pages that
        // don't use base.js.
        if (navigator && navigator.userAgent && !(parseInt((/Gecko\/([0-9]+)/.exec(navigator.userAgent) || []).pop()) <= 20060508)) {
          document.domain = 'facebook.com';
        }


/* -( End )------------------------------------------------------------------ */
