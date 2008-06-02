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
 *  "Bootload" resources into a page dynamically.
 *
 *  @author   epriestley
 *  @provides bootloader, copy-properties
 */

if (!window.Bootloader) {

  /**
   *  Copy properties from one object into another. This is a shallow copy.
   *
   *  Note: This is a `core' function which may be used when first evaluating
   *  other JS files -- in other words, it may be called in global scope.
   *  Eventually we may want to define a core.js defining this and other
   *  functions that are guaranteed to be available before other files are
   *  evaluated.
   *
   *  @author epriestley
   */
  window.copy_properties = function(u, v) {
    for (var k in v) {
      u[k] = v[k];
    }

    //  IE ignores `toString' in object iteration; make sure it gets copied if
    //  it exists. See:
    //    http://webreflection.blogspot.com/2007/07/
    //      quick-fix-internet-explorer-and.html

    //  Avoid a `ua' dependency since we can slip through by just doing
    //  capability detection.
    if (v.hasOwnProperty && v.hasOwnProperty('toString') &&
        (v.toString !== undefined) && (u.toString !== v.toString)) {
      u.toString = v.toString;
    }

    return u;
  }

  /**
   *  Bootload external resources programatically. Bootloader is tightly
   *  integrated with Haste and AsyncRequest.
   */
  window.Bootloader = {


    /**
     *  Load an external CSS or JS resource into the document. This function
     *  takes an object as an argument, which needs `type' and `src' properties
     *  at a minimum:
     *
     *    Bootloader.loadResource({type:'js-ext', src:'/js/meow.js'});
     *
     *  You may also provide a `name' property; if a resource is named, it will
     *  not be loaded if a resource of the same name has already been loaded.
     *
     *  Loading resources is NOT synchronous! You need to use Bootloader.wait()
     *  to register a callback if you are loading resources that are required to
     *  continue execution.
     *
     *  You alo can't wait() on arbitrary resources. You can never wait() on CSS
     *  and can only wait on Javascript if it calls Bootloader.done() to notify
     *  Bootloader that loading has completed. All Javascript loaded through
     *  `rsrc.php' will be properly annotated, but random Javascript "in the
     *  wild" won't work. The reason for this restriction is that Safari 2
     *  doesn't offer any automatic mechanism to detect that a script has
     *  loaded (which is unfortunate, because everything else does).
     *
     *  In general, Bootloader is automatically called by higher-level
     *  abstractions like Haste and AsyncResponse and you should not need to
     *  call it directly unless your use case is unusual. An example of an
     *  unusual but legitimate use case is reCAPTCHA, which does transport via a
     *  JSONP mechanism. You can't wait() for such a resource, but you can
     *  loadResource() it and any callbacks it executes will end up running.
     *
     *  Bootloader can load three types of resources: `js' (Facebook Javascript
     *  served through rsrc.php that calls done() and can be wait()'ed on),
     *  `js-ext' (external Javascript that does not call done() and thus can not
     *  be wait()'ed on; most likely the only use case for this is JSONP), and
     *  `css' (Facebook or external CSS, which can never be wait()'ed on).
     *
     *  @param    obj   Dictionary of type, source, and (optionally) name.
     *  @returns  void
     *
     *  @author   epriestley
     */
    loadResource : function(rsrc) {
      //  We're a bit paranoid about making sure we reference the master
      //  Bootloader on `window'; this isn't really necessary but we stick
      //  Bootloader into some unusual scopes.

      var b = window.Bootloader;

      if (rsrc.name) {
        if (b._loaded[rsrc.name]) {
          return;
        }
        b.markResourcesAsLoaded([rsrc.name]);
      }

      var tgt = b._getHardpoint();

      switch (rsrc.type) {
        case 'js':
          ++b._pending;
        case 'js-ext':
          var script = document.createElement('script');
            script.src  = rsrc.src;
            script.type = 'text/javascript';
          tgt.appendChild(script);
          break;

        case 'css':
          var link  = document.createElement('link');
            link.rel    = "stylesheet";
            link.type   = "text/css";
            link.media  = "all"
            link.href   = rsrc.src;
          tgt.appendChild(link);
          break;
      }
    },


    /**
     *  Register a callback for invocation when resources load. If there are
     *  no pending resources, the callback will be invoked immediately. See
     *  loadResource() for more discussion about the capabilities and
     *  limitations of this mechanism.
     *
     *  @param    function    Callback to invoke when all pending Facebook
     *                        Javascript resources finish loading.
     *  @returns  void
     *
     *  @author   epriestley
     */
    wait : function(wait_fn) {
      var b = window.Bootloader;
      if (b._pending > 0) {
        b._wait.push(wait_fn);
      } else {
        if (b._pending < 0 && window.Util) {
            Util.error('Bootloader- there are supposedly ' + b._pending + ' resources pending.');
        }
        wait_fn();
      }
    },


    /**
     *  Notify Bootloader that a script has loaded. You should probably never
     *  call this directly.
     *
     *  @param    int         Number of scripts which have loaded. Normally,
     *                        this number is 1, but may be larger if invoked by
     *                        a JIT package.
     *  @returns  void
     *
     *  @author   epriestley
     */
    done : function(num) {
      num = num || 1;
      var b = window.Bootloader;
      if (!b._ready) {
        return;
      }

      b._pending -= num;

      if (b._pending <= 0) {
        if (b._pending < 0 && window.Util) {
          Util.error('Bootloader- there are supposedly ' + b._pending + ' resources pending.');
        }
        var wait = b._wait;
        b._wait = [];
        for (var ii = 0; ii < wait.length; ii++) {
          wait[ii]();
        }
      }
    },


    /**
     *  Marks resources as already loaded (for instance, because they are in
     *  "script" tags in the page's source). If you pull in a resource without
     *  using Bootloader but do not mark it as "loaded" and Bootloader is later
     *  instructed to load it, Bootloader won't be able to detect that the
     *  resource is already loaded and the load event will never fire, so
     *  Bootloader will wait for it forever. You should probably never call
     *  this directly, it is invoked automatically by Haste.
     *
     *  @param    array   List of resource names to consider loaded.
     *  @returns  void
     *
     *  @author   epriestley
     */
    markResourcesAsLoaded : function(resources) {
      var b = window.Bootloader;
      for (var ii = 0; ii < resources.length; ii++) {
        b._loaded[resources[ii]] = true;
      }
      b._ready = true;
    },


/* -(  Implementation  )----------------------------------------------------- */


    _getHardpoint : function() {
      var b = window.Bootloader;

      if (!b._hardpoint) {
        var n, heads = document.getElementsByTagName('head');
        if (heads.length) {
          n = heads[0];
        } else {
          n = document.body;
        }
        b._hardpoint = n;
      }

      return b._hardpoint;
    },

    _loaded     : {},
    _pending    : 0,
    _hardpoint  : null,
    _wait       : [],
    _ready      : false

  };
}
