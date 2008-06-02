<?php

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


// Facebook Copyright 2006 - 2008

/**
 * Here's a further refinement of FBMLFullFlavor that might
 * get used to render profile boxes.  Profile boxes don't
 * allow everything, but they allow most things, and that's
 * why subclassing FBMLFullFlavor makes sense.
 */

class FBMLProfileBoxFlavor extends FBMLFullFlavor {

  public function allows_iframes() { return false; }
  public function allows_fql() { return false; }
  public function allows_redirect() { return false; }
  public function allows_script_onload() { return false; }
  public function allows_flash_autoplay() { return false; }

  public function allows_relative_urls() { return false; }
  public function allows_comments_macro() { return false; }
  public function allows_board() { return false; }
  public function allows_google_analytics() { return false; }
  public function allows_password_inputs() { return false; }
  public function allows_intl() { return false; }

  public function allows_requires() { return false; }
  public function allows_headers() { return false; }
  public function allows_cssincludes() { return false; }
  public function allows_visible_to() { return true; }
  public function allows_visible_to_bgcolor() { return true; }
}

?>
