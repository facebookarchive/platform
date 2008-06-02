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
 *  @requires ua onload
 *  @provides ua-adjust
 */

/**
 *  Function for UA-specific global behavior adjustments. This is basically
 *  the very definition of a giant pile of hacks. This is automatically called
 *  in base.js.
 *
 *  @access public
 *  @task   internal
 *
 *  @return void
 *
 *  @author epriestley
 */
function adjustUABehaviors( ) {
  onloadRegister(addSafariLabelSupport);

  //  This fixes an IE6 behavior where it doesn't cache background images.
  //  However, the fix breaks certain flavors of IE6 -- apparently anything
  //  without SP1, which includes some standalone versions? The forensics
  //  on this problem are a bit incomplete, but we were doing this in a
  //  CSS expression before so this is at least one degree less bad. See:
  //
  //    http://evil.che.lu/2006/9/25/no-more-ie6-background-flicker
  //    http://misterpixel.blogspot.com/2006/09/note-on-
  //      backgroundimagecache-command.html

  if (ua.ie() < 7) {
    try {
      document.execCommand('BackgroundImageCache', false, true);
    } catch (ignored) {
      //  Ignore, we're in some IE6 without SP1 and it didn't take.
    }
  }
}


/**
 *  Safari 2 doesn't have complete label support, but this fixes that.
 *
 *  @author rgrover
 */
function addSafariLabelSupport(base) {
  if (ua.safari() < 500) {
    var labels = (base || document.body).getElementsByTagName("label");
    for (i = 0; i < labels.length; i++) {
      labels[i].addEventListener('click', addLabelAction, true);
    }
  }
}

/**
 *  Support function for addSafariLabelSupport
 *  This is what gets called when clicking a label
 *  to make sure the right radio/checkbox gets chosen.
 *
 *  @author rgrover
 */
function addLabelAction(event) {
  var id = this.getAttribute('for');
  var item = null;
  if (id) {
    item = document.getElementById(id);
  } else {
    item = this.getElementsByTagName('input')[0];
  }
  if (!item || event.srcElement == item) {
    return;
  }
  if (item.type == 'checkbox') {
    item.checked = !item.checked;
  } else if (item.type == 'radio') {
    var radios = document.getElementsByTagName('input');
    for (i = 0; i < radios.length; i++) {
      if (radios[i].name == item.name && radios[i].form == item.form) {
        radios.checked = false;
      }
    }
    item.checked = true;
  } else {
    // sometimes focusing a checkbox has a weird side-effect (like on the
    // condensed multi-friend-selector)
    item.focus();
  }
  if (item.onclick) {
    item.onclick(event); // make sure events attached to this guy get triggered
  }
}
