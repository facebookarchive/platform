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
 *  @provides dom-misc
 *  @requires dom ua-adjust
 */
function show() {
  for (var i = 0; i < arguments.length; i++) {
    var element = ge(arguments[i]);
    if (element && element.style) element.style.display = '';
  }
  return false;
}

function hide() {
  for (var i = 0; i < arguments.length; i++) {
    var element = ge(arguments[i]);
    if (element && element.style) element.style.display = 'none';
  }
  return false;
}

function shown(el) {
    el = ge(el);
    return (el.style.display != 'none' && !(el.style.display=='' && el.offsetWidth==0));
}

function toggle() {
  for (var i = 0; i < arguments.length; i++) {
    var element = $(arguments[i]);
    element.style.display = get_style(element, "display") == 'block' ? 'none' : 'block';
  }
  return false;
}

/**
 * Sets innerHTML and executes JS that may be embedded.
 *
 * @param defer_js_execution  Wait until after this thread is done executing
 *                            to execute the JS.  This is a good idea if
 *                            you're setting a large amount of HTML, and want
 *                            to make the browser render the HTML before
 *                            starting on potentially-expensive JS evaluation.
 */
function set_inner_html(obj, html, defer_js_execution /* = false */) {

  // fix ridiculous IE bug: without some text before these tags, they get
  // stripped out when we set the innerHTML in a dialogpro.
  var dummy = '<span style="display:none">&nbsp</span>';
  html = html.replace('<style', dummy+'<style');
  html = html.replace('<STYLE', dummy+'<STYLE');
  html = html.replace('<script', dummy+'<script');
  html = html.replace('<SCRIPT', dummy+'<SCRIPT');

  obj.innerHTML = html;

  if (defer_js_execution) {
    eval_inner_js.bind(null, obj).defer();
  } else {
    eval_inner_js(obj);
  }

  addSafariLabelSupport(obj);
  (function() {
    LinkController.bindLinks(obj);
  }).defer();
}

// Executes JS that may be embedded in an element
function eval_inner_js(obj) {
  var scripts = obj.getElementsByTagName('script');
  for (var i=0; i<scripts.length; i++) {
    if (scripts[i].src) {
      var script = document.createElement('script');
      script.type = 'text/javascript';
      script.src = scripts[i].src;
      document.body.appendChild(script);
    } else {
      try {
        eval_global(scripts[i].innerHTML);
      } catch (e) {
        if (typeof console != 'undefined') {
          console.error(e);
        }
      }
    }
  }
}

// Evaluates JS in the global scope
// This seems really fragile but it works in Safari, Firefox, IE6, IE7, and even Opera.
// It even blocks properly so alert(1);eval_global('alert(2)');alert(3); will alert in order
function eval_global(js) {
  var obj = document.createElement('script');
  obj.type = 'text/javascript';

  try {
    obj.innerHTML = js;
  } catch(e) {
    obj.text = js;
  }


  document.body.appendChild(obj);
}
