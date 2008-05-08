<?php
//
// +---------------------------------------------------------------------------+
// | Facebook Platform PHP4 client                                             |
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

include_once 'facebookapi_php4_restlib.php';

class Facebook {
  var $api_client;

  var $api_key;
  var $secret;
  var $generate_session_secret;
  var $session_expires;

  var $fb_params;
  var $user;

  var $ec;

  function Facebook($api_key, $secret, $generate_session_secret=false) {
    $this->api_key                 = $api_key;
    $this->secret                  = $secret;
    $this->generate_session_secret = $generate_session_secret;

    $this->api_client = new FacebookRestClient($api_key, $secret, $this);
    $this->ec         = new FacebookAPIErrorCodes();

    $this->validate_fb_params();

    if (isset($this->fb_params['friends'])) {
      $this->api_client->friends_list = explode(',', $this->fb_params['friends']);
    }
    if (isset($this->fb_params['added'])) {
      $this->api_client->added = $this->fb_params['added'];
    }
  }

  function validate_fb_params($resolve_auth_token=true) {
    $this->fb_params = $this->get_valid_fb_params($_POST, 48*3600, 'fb_sig');
    if (!$this->fb_params) {
      $this->fb_params = $this->get_valid_fb_params($_GET, 48*3600, 'fb_sig');
    }
    if ($this->fb_params) {
      // If we got any fb_params passed in at all, then either:
      //  - they included an fb_user / fb_session_key, which we should assume to be correct
      //  - they didn't include an fb_user / fb_session_key, which means the user doesn't have a
      //    valid session and if we want to get one we'll need to use require_login().  (Calling
      //    set_user with null values for user/session_key will work properly.)
      // Note that we should *not* use our cookies in this scenario, since they may be referring to
      // the wrong user.
      $user        = isset($this->fb_params['user'])        ? $this->fb_params['user'] : null;
      $session_key = isset($this->fb_params['session_key']) ? $this->fb_params['session_key'] : null;
      $expires     = isset($this->fb_params['expires'])     ? $this->fb_params['expires'] : null;
      $this->set_user($user, $session_key, $expires);
    } else if (!empty($_COOKIE) && $cookies = $this->get_valid_fb_params($_COOKIE, null, $this->api_key)) {
      // use $api_key . '_' as a prefix for the cookies in case there are
      // multiple facebook clients on the same domain.
      $expires = isset($cookies['expires']) ? $cookies['expires'] : null;
      $this->set_user($cookies['user'], $cookies['session_key'], $expires);
    } else if (isset($_GET['auth_token']) && $resolve_auth_token &&
               $session = $this->do_get_session($_GET['auth_token'])) {
      $session_secret = ($this->generate_session_secret && !empty($session['secret'])) ? $session['secret'] : null;
      $this->set_user($session['uid'], $session['session_key'], $session['expires'], $session_secret);
    }

    return !empty($this->fb_params);
  }

  // Store a temporary session secret for the current session
  // for use with the JS client library
  function promote_session() {
    $session_secret = $this->api_client->auth_promoteSession();
    if (!$this->in_fb_canvas()) {
      $this->set_cookies($this->user, $this->api_client->session_key, $this->session_expires, $session_secret);
    }
    return $session_secret;
  }

  function do_get_session($auth_token) {
    $res = $this->api_client->auth_getSession($auth_token, $this->generate_session_secret);
    if (is_array($res)) {
      return $res;
    }
    return false;
  }

  // Invalidate the session currently being used, and clear any state associated with it
  function expire_session() {
    if ($this->api_client->auth_expireSession()) {
      // To clear the state, essentially perform the opposite of set_user
      if (!$this->in_fb_canvas() && (isset($_COOKIE[$this->api_key . '_user']))) {
        $cookies = array('user', 'session_key', 'expires', 'ss');
        foreach ($cookies as $name) {
          setcookie($this->api_key . '_' . $name, false, time() - 3600);
          unset($_COOKIE[$this->api_key . '_' . $name]);
        }
        setcookie($this->api_key, false, time() - 3600);
        unset($_COOKIE[$this->api_key]);
      }

      // now, clear the rest of the stored state
      $this->user = 0;
      $this->api_client->session_key = 0;
      return true;
    } else {
      return false;
    }
  }

  function redirect($url) {
    if ($this->in_fb_canvas()) {
      echo '<fb:redirect url="' . $url . '"/>';
    } else if (preg_match('/^https?:\/\/([^\/]*\.)?facebook\.com(:\d+)?/i', $url)) {
      // make sure facebook.com url's load in the full frame so that we don't
      // get a frame within a frame.
      echo "<script type=\"text/javascript\">\ntop.location.href = \"$url\";\n</script>";
    } else {
      http_header('Location', $url);
    }
    exit;
  }

  function in_frame() {
    return isset($this->fb_params['in_canvas']) || isset($this->fb_params['in_iframe']);
  }
  function in_fb_canvas() {
    return isset($this->fb_params['in_canvas']);
  }

  function get_loggedin_user() {
    return $this->user;
  }

  function current_url() {
    return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  function require_login() {
    if ($user = $this->get_loggedin_user()) {
      return $user;
    }
    $this->redirect($this->get_login_url($this->current_url(), $this->in_frame()));
  }

  function require_install() {
    // this was renamed, keeping for compatibility's sake
    return $this->require_add();
  }

  function require_add() {
    if ($user = $this->get_loggedin_user()) {
      if ($this->fb_params['added']) {
        return $user;
      }
    }
    $this->redirect($this->get_add_url($this->current_url()));
  }

  function require_frame() {
    if (!$this->in_frame()) {
      $this->redirect($this->get_login_url($this->current_url(), true));
    }
  }

  function get_facebook_url($subdomain='www') {
    return 'http://' . $subdomain . '.facebook.com';
  }

  function get_install_url($next=null) {
    // this was renamed, keeping for compatibility's sake
    return $this->get_add_url($next);
  }

  function get_add_url($next=null) {
    return $this->get_facebook_url().'/add.php?api_key='.$this->api_key .
      ($next ? '&next=' . urlencode($next) : '');
  }

  function get_login_url($next, $canvas) {
    return $this->get_facebook_url().'/login.php?v=1.0&api_key=' . $this->api_key .
      ($next ? '&next=' . urlencode($next)  : '') .
      ($canvas ? '&canvas' : '');
  }

  function generate_sig($params_array, $secret) {
    $str = '';

    ksort($params_array);
    // Note: make sure that the signature parameter is not already included in
    //       $params_array.
    foreach ($params_array as $k=>$v) {
      $str .= "$k=$v";
    }
    $str .= $secret;

    return md5($str);
  }

  function set_user($user, $session_key, $expires=null, $session_secret=null) {
    if (!$this->in_fb_canvas() && (!isset($_COOKIE[$this->api_key . '_user'])
                                   || $_COOKIE[$this->api_key . '_user'] != $user)) {
      $this->set_cookies($user, $session_key, $expires, $session_secret);
    }
    $this->user = $user;
    $this->api_client->session_key = $session_key;
    $this->session_expires = $expires;
  }

  function set_cookies($user, $session_key, $expires=null, $session_secret=null) {
    $cookies = array();
    $cookies['user'] = $user;
    $cookies['session_key'] = $session_key;
    if ($expires != null) {
      $cookies['expires'] = $expires;
    }
    if ($session_secret != null) {
      $cookies['ss'] = $session_secret;
    }
    foreach ($cookies as $name => $val) {
      setcookie($this->api_key . '_' . $name, $val, (int)$expires);
      $_COOKIE[$this->api_key . '_' . $name] = $val;
    }
    $sig = $this->generate_sig($cookies, $this->secret);
    setcookie($this->api_key, $sig, (int)$expires);
    $_COOKIE[$this->api_key] = $sig;
  }

  /**
   * Tries to undo the badness of magic quotes as best we can
   * @param     string   $val   Should come directly from $_GET, $_POST, etc.
   * @return    string   val without added slashes
   */
  function no_magic_quotes($val) {
    if (get_magic_quotes_gpc()) {
      return stripslashes($val);
    } else {
      return $val;
    }
  }

  function get_valid_fb_params($params, $timeout=null, $namespace='fb_sig') {
    $prefix = $namespace . '_';
    $prefix_len = strlen($prefix);
    $fb_params = array();
    foreach ($params as $name => $val) {
      if (strpos($name, $prefix) === 0) {
        $fb_params[substr($name, $prefix_len)] = $this->no_magic_quotes($val);
      }
    }
    if ($timeout && (!isset($fb_params['time']) || time() - $fb_params['time'] > $timeout)) {
      return array();
    }
    if (!isset($params[$namespace]) || !$this->verify_signature($fb_params, $params[$namespace])) {
      return array();
    }
    return $fb_params;
  }

  function verify_signature($fb_params, $expected_sig) {
    return $this->generate_sig($fb_params, $this->secret) == $expected_sig;
  }
}

