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
 *  @provides ua
 */

/**
 *  User Agent and OS detection. Usage is straightforward:
 *
 *    if (ua.ie( )) {
 *      //  IE
 *    }
 *
 *  You can also do version checks:
 *
 *    if (ua.ie( ) >= 7) {
 *      //  IE7 or better
 *    }
 *
 *  The browser functions will return NaN if the browser does not match, so
 *  you can also do version compares the other way:
 *
 *    if (ua.ie( ) < 7) {
 *      //  IE6 or worse
 *    }
 *
 *  Note that the version is a float and may include a minor version number,
 *  so you should always use range operators to perform comparisons, not
 *  strict equality.
 *
 *  NOTE: You should also STRONGLY prefer capability detection to browser
 *  version detection where it's reasonable:
 *  http://www.quirksmode.org/js/support.html
 *
 *  Further, we have a large number of mature wrapper functions and classes
 *  which abstract away many browser irregularities. Check the docs or grep
 *  things before writing yet another copy of "event || window.event".
 *
 *  @task browser   Determining the User Agent
 *  @task os        Determining the User's Operating System
 *  @task internal  Internal methods
 *
 *  @author marcel, epriestley
 */
var ua = {

  /**
   *  Check if the UA is Internet Explorer.
   *
   *  @task browser
   *  @access public
   *
   *  @return float|NaN Version number (if match) or NaN.
   *  @author marcel
   */
  ie: function() {
    return this._ie;
  },


  /**
   *  Check if the UA is Firefox.
   *
   *  @task browser
   *  @access public
   *
   *  @return float|NaN Version number (if match) or NaN.
   *  @author marcel
   */
  firefox: function() {
    return this._firefox;
  },


  /**
   *  Check if the UA is Opera.
   *
   *  @task browser
   *  @access public
   *
   *  @return float|NaN Version number (if match) or NaN.
   *  @author marcel
   */
  opera: function() {
    return this._opera;
  },


  /**
   *  Check if the UA is Safari.
   *
   *  @task browser
   *  @access public
   *
   *  @return float|NaN Version number (if match) or NaN.
   *  @author marcel
   */
  safari: function() {
    return this._safari;
  },


  /**
   *  Check if the user is running Windows.
   *
   *  @task os
   *  @return bool `true' if the user's OS is Windows.
   *  @author marcel
   */
  windows: function() {
    return this._windows;
  },


  /**
   *  Check if the user is running Mac OS X.
   *
   *  @task os
   *  @return bool `true' if the user's OS is Mac OS X.
   *  @author marcel
   */
  osx: function() {
    return this._osx;
  },


  /**
   *  Populate the UA and OS information.
   *
   *  @access public
   *  @task internal
   *
   *  @return void
   *
   *  @author marcel
   */
  populate : function() {

    var agent = /(?:MSIE.(\d+\.\d+))|(?:(?:Firefox|GranParadiso|Iceweasel).(\d+\.\d+))|(?:Opera.(\d+\.\d+))|(?:AppleWebKit.(\d+(?:\.\d+)?))/.exec(navigator.userAgent);
    var os    = /(Mac OS X;)|(Windows;)/.exec(navigator.userAgent);

    if (agent) {
      ua._ie      = agent[1] ? parseFloat(agent[1]) : NaN;
      ua._firefox = agent[2] ? parseFloat(agent[2]) : NaN;
      ua._opera   = agent[3] ? parseFloat(agent[3]) : NaN;
      ua._safari  = agent[4] ? parseFloat(agent[4]) : NaN;
    } else {
      ua._ie      =
      ua._firefox =
      ua._opera   =
      ua._safari  = NaN;
    }

    if (os) {
      ua._osx     = !!os[1];
      ua._windows = !!os[2];
    } else {
      ua._osx     =
      ua._windows = false;
    }
  }
};
