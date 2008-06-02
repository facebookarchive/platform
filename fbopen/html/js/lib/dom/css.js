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
 *  @author epriestley, marcel
 *
 *  @requires string dom util ua
 *  @provides css
 */


var CSS = {

  hasClass : function(element, className) {
    if (element && className && element.className) {
      return new RegExp('\\b'+trim(className)+'\\b').test(element.className);
    }
    return false;
  },

  addClass : function(element, className) {
    if (element && className) {
      if (!CSS.hasClass(element, className)) {
        if (element.className) {
          element.className += ' ' + trim(className);
        } else {
          element.className  = trim(className);
        }
      }
    }

    return this;
  },

  removeClass : function(element, className) {
    if (element && className && element.className) {
      className = trim(className);

      var regexp = new RegExp('\\b'+className+'\\b', 'g');
      element.className = element.className.replace(regexp, '');
    }

    return this;
  },

  conditionClass : function(element, className, shouldShow) {
    if (shouldShow) {
      CSS.addClass(element, className);
    } else {
      CSS.removeClass(element, className);
    }
  },

  setClass : function(element, className) {
    element.className = className;

    return this;
  },

  toggleClass : function(element, className) {
    if (CSS.hasClass(element, className)) {
      return CSS.removeClass(element, className);
    } else {
      return CSS.addClass(element, className);
    }
  },

  /**
   * Return a style element for the specified object.  Will
   * return the computed style element if available, otherwise
   * returns the in-line style definition.
   *
   * IMPORTANT: THERE ARE VERY FEW VALID USE CASES FOR THIS FUNCTION! Only use this function if you are
   *            100% sure that you need to. And even then be sure to ask Evan or Marcel about it first.
   */
  getStyle : function(element, property) {
    element = $(element);

    function hyphenate(property) {
      // Convert to hyphenated property
      return property.replace(/[A-Z]/g, function(match) {
        return '-' + match.toLowerCase();
      });
    }

    // Preferred W3C method
    if (window.getComputedStyle) {
      return window.getComputedStyle(element, null).getPropertyValue(hyphenate(property));
    }

    // Safari
    if (document.defaultView && document.defaultView.getComputedStyle) {
      var computedStyle = document.defaultView.getComputedStyle(element, null);
      // Safari returns null from computed style if the display of the element is none.
      // This is a bug in Safari. If object's display is none here, we just return
      // "none" if the user is asking for the "display" property, or we error otherwise.
      // It's probably possible to implement this correctly, but there are many details
      // you need to get right. See http://dev.mootools.net/ticket/51
      if (computedStyle)
        return computedStyle.getPropertyValue(hyphenate(property));
      if (property == "display")
        return "none";
      Util.error("Can't retrieve requested style %q due to a bug in Safari", property);
    }

    // IE and derivatives
    if (element.currentStyle) {
      return element.currentStyle[property];
    }

    // Crappy in-line only lookup
    return element.style[property];
  },

  setOpacity : function(element, opacity) {
    var opaque = (opacity == 1);

    try {
      element.style.opacity = (opaque ? '' : ''+opacity);
    } catch (ignored) {}

    try {
      element.style.filter  = (opaque ? '' : 'alpha(opacity='+(opacity*100)+')');
    } catch (ignored) {}
  },

  getOpacity : function(element) {
    var opacity = get_style(element, 'filter');
    var val = null;
    if (opacity && (val = /(\d+(?:\.\d+)?)/.exec(opacity))) {
      return parseFloat(val.pop()) / 100;
    } else if (opacity = get_style(element, 'opacity')) {
      return parseFloat(opacity);
    } else {
      return 1.0;
    }
  },

  Cursor : {

    kGrabbable : 'grabbable',
    kGrabbing  : 'grabbing',
    kEditable  : 'editable',

    set : function(element, name) {

      element = element || document.body;

      switch (name) {
        case CSS.Cursor.kEditable:
          name = 'text';
          break;
        case CSS.Cursor.kGrabbable:
          if (ua.firefox()) {
            name = '-moz-grab';
          } else {
            name = 'move';
          }
          break;
        case CSS.Cursor.kGrabbing:
          if (ua.firefox()) {
            name = '-moz-grabbing';
          } else {
            name = 'move';
          }
          break;
      }

      element.style.cursor = name;
    }
  }
};

var has_css_class_name    = CSS.hasClass;
var add_css_class_name    = CSS.addClass;
var remove_css_class_name = CSS.removeClass;
var toggle_css_class_name = CSS.toggleClass;
var get_style             = CSS.getStyle;
var set_opacity           = CSS.setOpacity;
var get_opacity           = CSS.getOpacity;

