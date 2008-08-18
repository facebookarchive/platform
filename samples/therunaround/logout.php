<?php

  /*
   * Logs the user out of the app. Does NOT delete the session or disconnect the user.
   * Basically, this just deletes the cookie.
   *
   */

include  'lib/core.php';
$user = User::getLoggedIn();
if ($user) {
  $user->logOut();
}
go_home();
