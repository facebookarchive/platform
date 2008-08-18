<?php

define('USER_COOKIE_NAME', 'rb_current_user');
define('RECENT_RUN_WINDOW', 7 * 24 * 60 * 60); // "Recent" runs means last 7 days

/*
 * This is a pretty generic user object.
 *
 * Logging in / logging out is done by setting a simple
 * cookie with the username. As it is trivial to spoof users in this
 * system, a real production app will need a more secure means of storing
 * state. Connect is intended to fit into an existing login system,
 * so you would use whatever your site already uses for this.
 *
 *
 */

class User {
  public $username;
  public $name;
  public $email;
  public $fb_uid;
  public $password; // should be protected, temp hack
  protected $runs = null;

  static function getLoggedIn() {
    $username = $_COOKIE[USER_COOKIE_NAME];
    $user = ($username && $username != 'deleted') ? User::getByUsername($username) : null;

    // If a user's session expires or is lost, then the XFBML stuff won't work anymore.
    // If they were a user before Facebook, then their name will still work, and this will succeed,
    // but if it's a facebook-only user (they registered via Facebook)
    if ($user && $user->is_facebook_user()) {
      if (!$user->getName()) {
        // name fetch failed. user is not logged in.
        return null;
      }
    }

    return $user;
  }

  static function getByUsername($username) {
    static $users_by_username = array();
    if (isset($users_by_username[$username])) {
      return $users_by_username[$username];
    }

    $ret = queryf("SELECT * FROM users WHERE username = %s", $username);

    if (!$ret) {
      error_log("Could not find username ". $username);
      return null;
    }

    $row = mysql_fetch_assoc($ret);

    if (!$row) {
      error_log("Could not find username ". $username);
      return null;
    }

    return $users_by_username[$username] = new User($row);
  }

  /*
   * Look up a user by their Facebook user ID.
   *
   * @return null on failure or if not found, User otherwise
   */
  static function getByFacebookUID($fb_uid) {
    if (!$fb_uid) {
      return null;
    }

    $ret = queryf("SELECT * FROM users WHERE fb_uid = %s", $fb_uid);

    if (!$ret) {
      error_log('Could not fetch from db for fb_uid ' . $fb_uid);
      return null;
    }

    $row = mysql_fetch_assoc($ret);

    if ($row) {
      return new User($row);
    }
  }

  static function getFacebookUser($info) {
    $fb_uid = $info['uid'];
    $user = self::getByFacebookUID($fb_uid);
    if ($user) {
      return $user;
    }

    return self::getByFacebookEmailHashes($info);
  }

  /*
   * Return any existing users that match the email hashes given.
   *
   * This is used to see if a Facebook user already has an account
   * on the system.
   */
  static function getByFacebookEmailHashes($info) {
    $email_hashes = $info['email_hashes'];
    if (!is_array($email_hashes) || count($email_hashes) == 0) {
      return null;
    }

    $ret = queryf("SELECT * FROM users WHERE email_hash IN (%s)",
        implode(',', $email_hashes));

    if (!$ret) {
      error_log('Could not fetch from db for fb_uid ' . $fb_uid);
      return null;
    }

    // NOTE: if a user has multiple emails on their facebook account,
    // and more than one is registered on the site, then we will
    // only return the first one.

    $row = mysql_fetch_assoc($ret);
    if ($row) {
      // Since we we looked up by email_hash, save the fb_uid
      // so we can look up directly next time
      $user = new User($row);
      $user->fb_uid = $info['uid'];
      $user->save();
      return $user;
    }
  }

  /*
   * If a Facebook user already has an account with this site, then
   * their email hash will be returned.
   *
   * This only works because the site calls facebook.connect.registerUsers
   * on every registration.
   */
  static function getFacebookUserEmailHashes($fb_uid) {
    $query = 'SELECT email_hashes FROM user WHERE uid=\''.$fb_uid.'\'';
    try {
      $rows = facebook_client()->api_client->fql_query($query);
    } catch (Exception $e) {
      // probably an expired session
      return null;
    }

    if (is_array($rows) && (count($rows) == 1) && is_array($rows[0]['email_hashes'])) {
      return $rows[0]['email_hashes'];
    } else {
      return null;
    }
  }

  /*
   * Create an account for the user based on Facebook UID.
   *
   * Note that none of the Facebook data is actually stored in the DB - basically
   * we store only the UID and generate all other info (name, pic, etc) on the fly.
   */
  static function createFromFacebookUID($fb_uid) {
    // first, check if there's a local account we should be stealing
    $email_hashes = self::getFacebookUserEmailHashes($fb_uid);
    $user = self::getByFacebookEmailHashes($email_hashes);

    // if we found a user, just add a uid; otherwise create a new one
    if ($user) {
      $user->fb_uid = $fb_uid;
    } else {
      $user_params = array();
      $user_params['fb_uid'] = $fb_uid;

      // We could allow the user to choose their own username if we want to
      // In this site, the username doesn't show up anywhere, so there's not much
      // point, but for a site where the username is a publicly-visible concept,
      // you may want to allow them to choose one.
      $user_params['username'] = 'FacebookUser_' . $fb_uid;

      // Don't add a password for the user. The whole point is that they log in
      // via Facebook. You could choose to let them add a password if you want
      // to give them an alternate means of login, but it's not recommended.
      $user_params['password'] = '';

      $user = new User($user_params);
    }

    // write to the db
    if (!$user->save()) {
      return null;
    }

    return $user;
  }

  function __construct($data) {
    $this->username = idx($data,'username',null);
    $this->name = idx($data,'name','');
    $this->email = idx($data,'email','');
    $this->password = idx($data,'password','');
    $this->fb_uid = idx($data,'fb_uid',0);
  }

  function is_facebook_user() {
    return ($this->fb_uid > 0);
  }

  function getName() {
    // override the user's name if it is a facebook user, regardless
    if ($this->is_facebook_user()) {
      $info = facebook_get_fields($this->fb_uid, array('name'));
      if (!empty($info)) {
        return $info['name'];
      }
    }
    return $this->name;
  }

  function getEmail() {
    // user can override themselves if they want to
    return ($this->is_facebook_user() && !$this->email) ?
      ''
      : $this->email;
  }

  function getFacebookBadge() {
    return ($this->is_facebook_user()) ?
      '<img src="http://static.ak.fbcdn.net/images/icons/favicon.gif" />'
      : '&nbsp;';
  }

  function getProfilePic($show_logo=false) {
    return ($this->is_facebook_user()) ?
      ('<fb:profile-pic uid="'.$this->fb_uid.'" size="square" ' . ($show_logo ? ' facebook-logo="true"' : '') . '></fb:profile-pic>')
      : '<img src="http://static.ak.fbcdn.net/pics/q_default.gif" />';
  }

  function getStatus() {
    return ($this->is_facebook_user()) ?
      '<fb:user-status uid="'.$this->fb_uid.'" ></fb:user-status>'
      : '';
  }

  /*
   * Logging in and out is accomplished by simply setting a cookie. In a real
   * production application, this would probably have an additional layer of
   * security to protect from spoofing users, but in the sample app it's not
   * necessary.
   *
   */
  function logIn($password = null) {
    // In a real app, you would probably store the password hash,
    // not the raw password. But since this is a demo app, just store
    // natively for security
    if ($password !== null &&
      $this->password != $password) {
     error_log("Attempt to log in for user "
               .$user->username
               ." with password '"
               . $password
               . "' . Real password is '"
               .$this->password
               ."'");
      return false;
    }

    setcookie(USER_COOKIE_NAME, $this->username);
    return true;
  }

  /*
   * Deletes the login cookie.
   *
   */
  function logOut() {
    setcookie(USER_COOKIE_NAME, 'deleted');
    if ($this->is_facebook_user()) {
      try {
        // it's important to expire the session, or else the user
        // may find themselves logged back in when they come back
        // This is NOT the same as disconnecting from the app - you
        // can get a new session at any time with auth.getSession.
        facebook_client()->expire_session();
      } catch (Exception $e) {
        // nothing, probably an expired session
      }
    }
  }

  /*
   * Associate this user account with the given Facebook user.
   */
  function connectWithFacebookUID($fb_uid) {
   if (!$fb_uid) {
      return false;
   }
   $this->fb_uid = $fb_uid;
   return $this->save();
  }

  /*
   * Restore the account to its original self, removing all Facebook info.
   */
  function disconnectFromFacebook() {
    if ($this->email) {
      facebook_unregisterUsers(array(email_get_public_hash($this->email)));
    }

    $this->fb_uid = 0;
    return $this->save();
  }

  function save() {
    $email_hash = email_get_public_hash($this->email);
    // if there's already a row out there
    $ret = queryf("INSERT INTO users (username, name, password, email, fb_uid, email_hash) VALUES (%s, %s, %s, %s, %d, %s) "
                  ."ON DUPLICATE KEY UPDATE name = %s, password = %s, email = %s, fb_uid = %s, email_hash = %s",
                  $this->username,
                  $this->name, $this->password, $this->email, (int) $this->fb_uid, $email_hash,
                  $this->name, $this->password, $this->email, (int) $this->fb_uid, $email_hash);

    return (bool) $ret;
  }

  function saveAndRegister() {
    if (!$this->save()) {
      return false;
    }

    // If that was successful, register the user with Facebook
    $email_hash = email_get_public_hash($this->email);
    $accounts = array(array('email_hash' => $email_hash, 'account_id' => $this->username));
    return facebook_registerUsers($accounts);
  }

  function delete() {
    // Delete the account from the db
    $ret = queryf('DELETE FROM users WHERE username = %s', $this->username);
    if (!$ret) {
      error_log("Could not delete account ($this->username)");
      return false;
    }

    // Unregister the account from fb
    return facebook_unregisterUsers(array(email_get_public_hash($this->email)));
  }

  /*
   * Tells whether the account has a password set.
   *
   * If the account has no password, then it cannot be disconnected, only deleted outright (otherwise how will
   * the user log in?)
   */
  function hasPassword() {
    return (bool) $this->password;
  }

  function getRuns() {
    if ($this->runs === null) {
      $ret = queryf('SELECT * FROM runs WHERE username = %s AND date > %d ORDER BY date DESC',
                    $this->username, time() - RECENT_RUN_WINDOW);
      if (!$ret) {
        return null;
      }

      $runs = array();
      while ($row = mysql_fetch_assoc($ret)) {
        $run = new Run($this, $row);
        $runs[] = $run;
      }
    }

    $this->runs = $runs;
    return $this->runs;
  }
}
