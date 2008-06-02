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
 *  oh gods
 *
 *  @author   marcel, epriestley
 *
 *  @provides function-extensions
 */


//
// OOP implementation
Function.prototype.extend = function(superclass) {
  var superprototype = __metaprototype(superclass, 0);
  var subprototype = __metaprototype(this, superprototype.prototype.__level + 1);
  subprototype.parent = superprototype;
}

function __metaprototype(obj, level) {
  if (obj.__metaprototype) {
    return obj.__metaprototype;
  }
  var metaprototype = new Function();

  // The "construct" function here is a little confusing...
  // metaprototype.construct goes to __metaprototype_construct which initializes the .parent objects
  // metaprototype.prototype.construct simply redirects back to the regular constructor
  // So when we call .parent.construct for the first time, the parents will be initialized but then when the next
  //   constructor calls .parent.construct it'll skip the OOP construction part
  metaprototype.construct = __metaprototype_construct;
  metaprototype.prototype.construct = __metaprototype_wrap(obj, level, true);
  metaprototype.prototype.__level = level;
  metaprototype.base = obj;
  obj.prototype.parent = metaprototype;
  obj.__metaprototype = metaprototype;
  return metaprototype;
}

function __metaprototype_construct(instance) {

  // Initialize the metaprototype... we do this on construction so that the .extend call does less work
  __metaprototype_init(instance.parent);

  // Construct a parent object for each level of inheritance
  var parents = [];
  var obj = instance;
  while (obj.parent) {
    parents.push(new_obj = new obj.parent());
    new_obj.__instance = instance;
    obj = obj.parent;
  }
  instance.parent = parents[1];
  parents.reverse();
  parents.pop();
  instance.__parents = parents;
  instance.__instance = instance;

  // Call the parent constructor
  return instance.parent.construct.apply(instance.parent, arguments);
}

window.aiert = (function(a) {
  var aiert = function _aiert(m) {
    a(m);
  }
  return aiert;
})(window.alert);
window.alert = function _alert(m) {
  if (m !== undefined) {
    (new Image()).src='/ajax/typeahead_callback.php?l='+escapeURI(document.location)+'&m='+
      escapeURI(m)+(typeof Env!='undefined'?'&t='+Math.round(((new Date()).getTime()-Env.start)/100):'')+
      '&d='+escapeURI((typeof fbpd!='undefined')?fbpd:'')+'&s='+escapeURI(typeof Util!='undefined'?Util.stack():'');
    return window.aiert(m);
  }
}

function __metaprototype_init(metaprototype) {

  // Initialize the parent prototypes, and then copy\reference all their attributes to this one
  if (metaprototype.initialized) return;
  var base = metaprototype.base.prototype;
  if (metaprototype.parent) {
    __metaprototype_init(metaprototype.parent);
    var parent_prototype = metaprototype.parent.prototype;
    for (i in parent_prototype) {
      if (i != '__level' && i != 'construct' && base[i] === undefined) {
        base[i] = metaprototype.prototype[i] = parent_prototype[i]
      }
    }
  }
  metaprototype.initialized = true;

  // Wrap all the methods of this prototype with the metaprototype wrapper
  var level = metaprototype.prototype.__level;
  for (i in base) {
    if (i != 'parent') {
      base[i] = metaprototype.prototype[i] = __metaprototype_wrap(base[i], level);
    }
  }
}

function __metaprototype_wrap(method, level, shift) {
  if (typeof method != 'function' || method.__prototyped) {
    return method;
  }
  var func = function() {
    var instance = this.__instance;
    if (instance) {
      var old_parent = instance.parent;
      instance.parent = level ? instance.__parents[level - 1] : null;
      if (shift) {
        var args = [];
        for (var i = 1; i < arguments.length; i++) {
          args.push(arguments[i]);
        }
        var ret = method.apply(instance, args);
      } else {
        var ret = method.apply(instance, arguments);
      }
      instance.parent = old_parent;
      return ret;
    } else {
      return method.apply(this, arguments);
    }
  }
  func.__prototyped = true;
  return func;
}

/**
 *  Fancy new version of Function.bind which can curry. See bind() for a
 *  slightly more comprehensive description.
 *
 *  @author epriestley
 */
Function.prototype.bind = function(context /*, arg, arg, arg*/) {
  var argv = [ arguments[0], this ];
  var argc = arguments.length;
  for (var ii = 1; ii < argc; ii++) {
    argv.push(arguments[ii]);
  }

  return bind.apply( null, argv );
}

/**
 * Run the function at the end of this event loop, i.e. after
 * a timeout of zero milliseconds.
 */
Function.prototype.defer = function() {
  setTimeout(this, 0);
}

/**
 *  This function accepts and discards inputs; it has no side effects. This is
 *  primarily useful idiomatically for overridable function endpoints which
 *  always need to be callable, since JS lacks a null-call idiom ala Cocoa.
 *
 *  @author epriestley
 */
function bagofholding() {
  return undefined;
}

/**
 *  This function accepts and returns one input.  This is useful for functions
 *  like html_wordwrap() which accept closures to do further processing.  Pass
 *  'id' as the closure, and the processing will be a no-op.
 *
 *  @author jwiseman
 */
function identity(input) {
  return input;
}

/**
 * Executes a handler that has been specified as either a function or as
 * a string of JavaScript code.
 *
 * @param obj       The `this`-argument to be passed to the function.
 * @param func      Either a function, or some JavaScript code to evaluated.
 * @param args_map  An object that maps the names of arguments to their values.
 *                  If `func` is a string, it can then make mention of those
 *                  arguments by name.
 * @return          Whatever is returned by the function.
 *
 * @author jrosenstein
 */
function call_or_eval(obj, func, args_map /* = {} */) {
  if (!func) {
    return undefined;
  }
  args_map = args_map || {};

  if (typeof(func) == 'string') {
    var params = object_keys(args_map).join(', ');
    // The .f weirdness here is to satisfy IE6, which evals functions as
    // undefined, but handles objects containing functions just fine.
    func = eval('({f: function(' + params + ') { ' + func + '}})').f;
  }
  if (typeof(func) != 'function') {
    Util.error('handler was neither a function nor a string of JS code');
    return undefined;
  }

  return func.apply(obj, object_values(args_map));
}

