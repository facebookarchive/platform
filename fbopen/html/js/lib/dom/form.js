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
 *  @provides dom-form
 */

function getRadioFormValue(obj) {
  for (i = 0; i < obj.length; i++) {
   if (obj[i].checked) {
     return obj[i].value;
   }
  }
  return null;
}

// === Forms ===

// http://www.quirksmode.org/dom/getElementsByTagNames.html
function getElementsByTagNames(list,obj) {
  if (!obj) var obj = document;
  var tagNames = list.split(',');
  var resultArray = new Array();
  for (var i=0;i<tagNames.length;i++) {
    var tags = obj.getElementsByTagName(tagNames[i]);
    for (var j=0;j<tags.length;j++) {
      resultArray.push(tags[j]);
    }
  }
  var testNode = resultArray[0];
  if (!testNode) return [];
  if (testNode.sourceIndex) {
    resultArray.sort(function (a,b) {
      return a.sourceIndex - b.sourceIndex;
    });
  }
  else if (testNode.compareDocumentPosition) {
    resultArray.sort(function (a,b) {
      return 3 - (a.compareDocumentPosition(b) & 6);
    });
  }
  return resultArray;
}

function get_all_form_inputs(root_element) {
  if (!root_element) {
    root_element = document;
  }
  return getElementsByTagNames('input,select,textarea,button', root_element);
}

function get_form_select_value(select) {
  return select.options[select.selectedIndex].value;
}

function set_form_select_value(select, value) {
  for (var i = 0; i < select.options.length; ++i) {
    if (select.options[i].value == value) {
      select.selectedIndex = i;
      break;
    }
  }
}

// if you want to find an attribute of a <form> node, doing form.attr_name or
// in IE6 even form.getAttribute('attr_name') won't work in the event that the
// form has an input node named "attr_name".  so use this function instead.
// (see http://bugs.developers.facebook.com/show_bug.cgi?id=251 )
function get_form_attr(form, attr) {
  var val = form[attr];
  if (typeof val == 'object' && val.tagName == 'INPUT') {
    var pn = val.parentNode, ns = val.nextSibling, node = val;
    pn.removeChild(node);
    val = form[attr];
    ns ? pn.insertBefore(node, ns) : pn.appendChild(node);
  }
  return val;
}

function serialize_form_helper(data, name, value) {
  var match = /([^\]]+)\[([^\]]*)\](.*)/.exec(name);
  if (match) {
    data[match[1]] = data[match[1]] || {};
    if (match[2] == '') {
      var i = 0;
      while (data[match[1]][i] != undefined) {
        i++;
      }
    } else {
      i = match[2];
    }
    if (match[3] == '') {
      data[match[1]][i] = value;
    } else {
      serialize_form_helper(data[match[1]], i.concat(match[3]), value);
    }
  } else {
    data[name] = value;
  }
}

// turns stuff like {0: 'foo', 1: 'bar'} into ['foo', 'bar']
function serialize_form_fix(data) {
  var keys = [];
  for (var i in data) {
    if (data instanceof Object) {
      data[i] = serialize_form_fix(data[i]);
    }
    keys.push(i);
  }
  var j = 0, is_array = true;
  keys.sort().each(function(i) {
    if (i != j++) {
      is_array = false;
    }
  });
  if (is_array) {
    var ret = {};
    keys.each(function(i) {
      ret[i] = data[i];
    });
    return ret;
  } else {
    return data;
  }
}

function serialize_form(obj) {
  var data = {};
  var elements = obj.tagName == 'FORM' ? obj.elements : get_all_form_inputs(obj);
  for (var i = elements.length - 1; i >= 0; i--) {
    if (elements[i].name && !elements[i].disabled) {
      // Serialize If
      // 1) unrecognizable type
      // 2) radio buttons or checkboxes that are checked
      // 3) type is in (text,password,hidden)
      // 4) tag is in (textarea,select)
      if (!elements[i].type ||
          ((elements[i].type == 'radio' || elements[i].type == 'checkbox') &&
            elements[i].checked) ||
          elements[i].type == 'text' ||
          elements[i].type == 'password' ||
          elements[i].type == 'hidden' ||
          elements[i].tagName == 'TEXTAREA' ||
          elements[i].tagName == 'SELECT') {
        serialize_form_helper(data, elements[i].name, elements[i].value);
      }
    }
  }
  return serialize_form_fix(data);
}

function is_button(element) {
  var tagName = element.tagName.toUpperCase();
  if (tagName == 'BUTTON') {
    return true;
  }
  if (tagName == 'INPUT' && element.type) {
    var type = element.type.toUpperCase();
    return type == 'BUTTON' || type == 'SUBMIT';
  }
  return false;
}





// This little guy takes a get style request except it does it as a POST
function do_post(url) {
  var pieces=/(^([^?])+)\??(.*)$/.exec(url);
  var form=document.createElement('form');
  form.action=pieces[1];
  form.method='post';
  form.style.display='none';
  var sparam=/([\w]+)(?:=([^&]+)|&|$)/g;
  var param=null;
  if (ge('post_form_id'))
    pieces[3]+='&post_form_id='+$('post_form_id').value;
  while (param=sparam.exec(pieces[3])) {
    var input=document.createElement('input');
    input.type='hidden';
    input.name=decodeURIComponent(param[1]);
    input.value=decodeURIComponent(param[2]);
    form.appendChild(input);
  }
  document.body.appendChild(form);
  form.submit();
  return false;
}

// This does a POST of the variables in params
function dynamic_post(url, params) {
  var form=document.createElement('form');
  form.action=url;
  form.method='POST';
  form.style.display='none';
  if (ge('post_form_id')) {
    params['post_form_id'] = $('post_form_id').value;
  }
  for (var param in params) {
    var input=document.createElement('input');
    input.type='hidden';
    input.name=param;
    input.value=params[param];
    form.appendChild(input);
  }
  document.body.appendChild(form);
  form.submit();
  return false;
}
