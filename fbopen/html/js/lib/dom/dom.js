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
 *  @author   epriestley, marcel
 *
 *  @requires function-extensions array-extensions list ua dom-misc util
 *            dom-html
 *  @provides dom dom-core
 */

var DOM = {

  tryElement : function(id) {
    if (typeof(id) == 'undefined') {
      Util.error('Tried to get "undefined" element!');
      return null;
    }

    var obj;
    if (typeof(id) == 'string') {
      obj = document.getElementById(id);

      if (!(ua.ie() >= 7)) {
        return obj;
      }

      // Workaround for a horrible bug in IE7
      // http://remysharp.com/2007/02/10/ie-7-breaks-getelementbyid/
      if (!obj) {
        return null;
      // In the case where `obj' is a form element with an input that has
      // the name `id', obj.id is an input instead of the actual id.
      } else if (typeof(obj.id) == 'string' && obj.id == id) {
        return obj;
      } else {

        var candidates = document.getElementsByName(id);
        if (!candidates || !candidates.length) {
          return null;
        }

        var maybe = [];
        for (var ii = 0; ii < candidates.length; ii++) {
          var c = candidates[ii];

          //  If we have no `id', this can't possibly be the real element; skip
          //  it -- unless "id" is "0" or empty or something?
          if (!c.id && id) {
            continue;
          }

          //  If we have an `id' and it's a string but it's wrong, skip it.
          if (typeof(c.id) == 'string' && c.id != id) {
            continue;
          }

          //  We're left with forms with the correct ID that is obscured by an
          //  input named `id' and maybe some edge cases where multiple elements
          //  have the same ID.

          maybe.push(candidates[ii]);
        }

        if (!maybe.length) {
          return null;
        }

        return maybe[0];
      }
    }

    return id;
  },

  getElement : function(id) {
    var el = DOM.tryElement.apply(null, arguments);
    if (!el) {
      Util.warn(
        'Tried to get element %q, but it is not present in the page. (Use '    +
        'ge() to test for the presence of an element.)',
        arguments[0]);
    }
    return el;
  },

  setText : function(el, text) {
    if (ua.firefox()) {
      el.textContent = text;
    } else {
      el.innerText = text;
    }
  },

  getText : function(el) {
    if (ua.firefox()) {
      return el.textContent;
    } else {
      return el.innerText;
    }
  },

  setContent : function(el, content) {

    //  This is a horrible browser-specific discography hack. I have no idea
    //  what is going on here.

    if (ua.ie()) {
      for (var ii = el.childNodes.length - 1; ii >= 0; --ii) {
        DOM.remove(el.childNodes[ii]);
      }
    } else {
      el.innerHTML = '';
    }

    if (content instanceof HTML) {
      set_inner_html(el, content.toString());
    } else if (is_scalar(content)) {
      content = document.createTextNode(content);
      el.appendChild(content);
    } else if (is_node(content)) {
      el.appendChild(content);
    } else if (content instanceof Array) {
      for (var ii = 0; ii < content.length; ii++) {
        var node = content[ii];
        if (!is_node(node)) {
          node = document.createTextNode(node);
        }
        el.appendChild(node);
      }
    } else {
      Util.error(
        'No way to set content %q.', content);
    }
  },

  remove : function(element) {
    element = $(element);
    if (element.removeNode) {
      element.removeNode(true);
    } else {
      for (var ii = element.childNodes.length-1; ii >=0; --ii) {
        DOM.remove(element.childNodes[ii]);
      }
      element.parentNode.removeChild(element);
    }
  },

  create : function(element, attributes, children) {
    element = document.createElement(element);

    if (attributes) {
      attributes = copy_properties({}, attributes);
      if (attributes.style) {
        copy_properties(element.style, attributes.style);
        delete attributes.style;
      }
      copy_properties(element, attributes);
    }

    if (children != undefined) {
      DOM.setContent(element, children);
    }

    return element;
  },

  scry : function(element, pattern) {
    pattern = pattern.split('.');
    var tag = pattern[0] || null;
    if (!tag) {
      return [];
    }
    var cls = pattern[1] || null;

    var candidates = element.getElementsByTagName(tag);
    if (cls !== null) {
      var satisfy = [];
      for (var ii = 0; ii < candidates.length; ii++) {
        if (CSS.hasClass(candidates[ii], cls)) {
          satisfy.push(candidates[ii]);
        }
      }
      candidates = satisfy;
    }

    return candidates;
  },

  prependChild : function(parent, child) {
    parent = $(parent);
    if (parent.firstChild) {
      parent.insertBefore(child, parent.firstChild);
    } else {
      parent.appendChild(child);
    }
  },

  getCaretPosition : function(element) {
    element = $(element);

    if (!is_node(element, ['input', 'textarea'])) {
      return {start: undefined, end: undefined};
    }

    if (!document.selection) {
      return {start: element.selectionStart, end: element.selectionEnd};
    }

    if (is_node(element, 'input')) {
      var range = document.selection.createRange();
      return {start: -range.moveStart('character', -element.value.length),
                end: -range.moveEnd('character', -element.value.length)};
    } else {
      var range = document.selection.createRange();
      var range2 = range.duplicate();
      range2.moveToElementText(element);
      range2.setEndPoint('StartToEnd', range);
      var end = element.value.length - range2.text.length;
      range2.setEndPoint('StartToStart', range);
      return {start: element.value.length - range2.text.length, end: end};
    }
  },

  addEvent : function(element, type, func, name_hash) {
    return addEventBase(element, type, func, name_hash);
  }

};

var $N = DOM.create;
var ge = DOM.tryElement;

var $$ = function _$$(rules) {
  //  Avoid calling bind() at interpretation time because of concurrency issues
  //  with Bootloader.
  var args = [document].concat(Array.prototype.slice.apply(arguments));
  return DOM.scry.apply(null, args);
}

var  $ = DOM.getElement;

var remove_node         = DOM.remove;
var prependChild        = DOM.prependChild;
var get_caret_position  = DOM.getCaretPosition;



function is_node(o, of_type) {

  if (typeof(Node) == 'undefined') {
    Node = null;
  }

  try {
    if (!o || !((Node != undefined && o instanceof Node) || o.nodeName)) {
      return false;
    }
  } catch(ignored) {
    return false;
  }

  if (typeof(of_type) !== "undefined") {

    if (!(of_type instanceof Array)) {
      of_type = [of_type];
    }

    var name;
    try {
      name = new String(o.nodeName).toUpperCase();
    } catch (ignored) {
      return false;
    }

    for (var ii = 0; ii < of_type.length; ii++) {
      try {
        if (name == of_type[ii].toUpperCase()) {
          return true;
        }
      } catch (ignored) {
      }
    }

    return false;
  }

  return true;
}


/* determines whether or not a base_obj is a descendent of the target_id obj */
function is_descendent(base_obj, target_id) {
  var target_obj = ge(target_id);
  if (base_obj == null) return;
  while (base_obj != target_obj) {
    if (base_obj.parentNode) {
      base_obj = base_obj.parentNode;
    } else {
      return false;
    }
  }
  return true;
}


// From Corinis; available in the public domain per author
function iterTraverseDom(root, visitCb) {
  var c = root, n = null;
  var it = 0;
  do {
    n = c.firstChild;
    if (!n) {
      if (visitCb(c) == false)
        return;
      n = c.nextSibling;
    }

    if (!n) {
      var tmp = c;
      do {
        n = tmp.parentNode;
        if (n == root)
          break;

        if (visitCb(n) == false)
          return;

        tmp = n;
        n = n.nextSibling;
      }
      while (!n);
    }

    c = n;
  }
  while (c != root);
}



function insertAfter(parent, child, elem) {
  if (parent != child.parentNode) {
    Util.error('child is not really a child of parent - wtf, seriously.');
  }
  if (child.nextSibling) {
    var ret = parent.insertBefore(elem, child.nextSibling);
  } else {
    var ret = parent.appendChild(elem);
  }
  if (!ret) {
    return null;
  }
  return elem;
}


// sets the caret position of a textarea or input. end is optional and will default to start
function set_caret_position(obj, start, end) {
  if (document.selection) {
    // IE is inconsistent about character offsets when it comes to carriage returns, so we need to manually take them into account
    if (obj.tagName == 'TEXTAREA') {
      var i = obj.value.indexOf("\r", 0);
      while (i != -1 && i < end) {
        end--;
        if (i < start) {
          start--;
        }
        i = obj.value.indexOf("\r", i + 1);
      }
    }
    var range = obj.createTextRange();
    range.collapse(true);
    range.moveStart('character', start);
    if (end != undefined) {
      range.moveEnd('character', end - start);
    }
    range.select();
  } else {
    obj.selectionStart = start;
    var sel_end = end == undefined ? start : end;
    obj.selectionEnd = Math.min(sel_end, obj.value.length);
    obj.focus();
  }
}