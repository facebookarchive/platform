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

//
// +---------------------------------------------------------------------------+
// | Facebook Platform PHP5 client                                             |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007 Facebook, Inc.                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | 1. Redistributions of source code must retain the above copyright         |
// |    notice, this list of conditions and the following disclaimer.          |
// | 2. Redistributions in binary form must reproduce the above copyright      |
// |    notice, this list of conditions and the following disclaimer in the    |
// |    documentation and/or other materials provided with the distribution.   |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR      |
// | IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES |
// | OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.   |
// | IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT  |
// | NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF  |
// | THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.         |
// +---------------------------------------------------------------------------+
// | For help with this library, contact developers-help@facebook.com          |
// +---------------------------------------------------------------------------+
//

/**
 *  This class extends and modifies the "Facebook" class to better
 *  suit desktop apps.
 */
class FacebookDesktop extends Facebook {
  // the application secret, which differs from the session secret
  public $app_secret;
  public $verify_sig;

  public function __construct($api_key, $secret) {
    $this->app_secret = $secret;
    $this->verify_sig = false;
    parent::__construct($api_key, $secret);
  }

  public function do_get_session($auth_token) {
    $this->api_client->secret = $this->app_secret;
    $session_info = parent::do_get_session($auth_token);
    if (isset($session_info['secret']) && $session_info['secret']) {
      // store the session secret
      $this->set_session_secret($session_info['secret']);
    }
    return $session_info;
  }

  public function set_session_secret($session_secret) {
    $this->secret = $session_secret;
    $this->api_client->secret = $session_secret;
  }

  public function require_login() {
    if ($this->get_loggedin_user()) {
      try {
        // try a session-based API call to ensure that we have the correct
        // session secret
        $user = $this->api_client->users_getLoggedInUser();

        // now that we have a valid session secret, verify the signature
        $this->verify_sig = true;
        if ($this->validate_fb_params()) {
          return $user;
        } else {
          // validation failed
          return null;
        }
      } catch (FacebookRestClientException $ex) {
        if (isset($_GET['auth_token'])) {
          // if we have an auth_token, use it to establish a session
          $session_info = $this->do_get_session($_GET['auth_token']);
          if ($session_info) {
            return $session_info['uid'];
          }
        }
      }
    }
    // if we get here, we need to redirect the user to log in
    $this->redirect($this->get_login_url(self::current_url(), $this->in_fb_canvas()));
  }

  public function verify_signature($fb_params, $expected_sig) {
    // we don't want to verify the signature until we have a valid
    // session secret
    if ($this->verify_sig) {
      return parent::verify_signature($fb_params, $expected_sig);
    } else {
      return true;
    }
  }
}
