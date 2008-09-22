<?php

define(MAIN_PATH, realpath('.'));
include_once MAIN_PATH.'/init.php';

echo render_header();

if ($user = User::getLoggedIn()) {
  // do nothing
} else if (!empty($_POST)) {
  $params = parse_http_args($_POST,
      array('save', 'username', 'name', 'email', 'password'));

  if ($params['save']) {
    $user_params = array('username' => $params['username'],
                         'name'     => $params['name'],
                         'email'    => $params['email'],
                         'password' => $params['password']);

    $user = new User($user_params);

    // Don't register with facebook in this case
    if (!($user->saveAndRegister())) {
      $user = null;
      $error = 'Error: Could not register account with Facebook.';
    }
  }
}

if ($user) {
  $user->logIn();
  go_home();
}

if ($error) {
  echo '<div class="error">'.$error.'</div>';
}

?>


<!-- Eventually do CSSification -->
<div class="register">
<div class="login_sector">
<h2>Do you use Facebook?</h2>
 Use Facebook to register for The Run Around:<br/><br />

<?php
echo render_fbconnect_button('large');
?>
</div>
<div class="login_sector_fb">
<h2>Register</h2>
<form action="register.php" method="post">
<table>
  <tr>
    <td>
      <label id="label_username" for="username">Username:</input>
    </td>
    <td>
      <input id="username" class="inputtext" type="text" size="20" value="" name="username"/>
    </td>
  </tr>
  <tr>
    <td>
      <label id="label_password" for="password">Password:</label>
    </td>
    <td>
      <input id="password" class="inputtext" type="password" size="20" value="" name="password"/>
    </td>
    </tr>
    <tr>
      <td>
        <label id="label_email" for="email">Email:</label>
      </td>
      <td>
        <input id="email" class="inputtext" type="text" size="20" value="" name="email"/>
      </td>
    </tr>
    <tr>
      <td>
        <label id="label_name" for="name">Name:</label>
      </td>
      <td>
        <input id="name" class="inputtext" type="text" size="20" value="" name="name"/>
      </td>
  </tr>
</table>
<input type="hidden" name="save" value="1">
<input type="submit" class="inputsubmit" value="Register" style="margin-left: 80px">
</form>
</div>
</div>
<?php

echo render_footer();
