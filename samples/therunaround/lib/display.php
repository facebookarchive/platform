<?php

/*
 * Show the header that goes at the top of each page.
 */
function render_header() {
  // Might want to serve this out of a canvas sometimes to test
  // out fbml, so if so then don't serve the JS stuff
  if (isset($_POST['fb_sig_in_canvas'])) {
    return;
  }

  prevent_cache_headers();

  $html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
   <title>The Run Around</title>
    <link type="text/css" rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="base.js"></script>
  ';

  $html .='
  </head>
  <body>';

  if (is_fbconnect_enabled()) {
    ensure_loaded_on_correct_url();
  }

  $html .='

  <div id="header">
  <div class="header_content">
  <a href="index.php" class="title"><img src="./images/runaround_logo.gif" /></a>';

  // Show either "Welcome User Name" or "Welcome Guest"

  $user = User::getLoggedIn();
  if ($user) {
    if ($user->is_facebook_user()) {
      $html .= '<div id="header-profilepic">';
      $html .= $user->getProfilePic(true);
      $html .= '</div>';
    }

    $html .= '<div id="header-account">';
    $html .= '<b>Welcome, '.$user->getName().'</b>';
    $html .= '<div class="account_links">';
    $html .= '<a href="account.php">Account Settings</a> | ';
    if ($user->is_facebook_user()) {
      $html .= sprintf('<a href="#" onclick="FB.Connect.logout(function() { refresh_page(); })">'
                       .'Logout of Facebook'
                       //.'<img src="images/fbconnect_logout.png">'
                       .'</a>',
                       $_SERVER['REQUEST_URI']);
    } else {
      $html .= '<a href="logout.php">Logout</a>';
    }
    $html .= '<br /></div>';
    $html .= '</div>';
  } else {
    $html .= '<div class="account">';
    $html .= 'Hello Guest | ';
    $html .= '<a href="./register.php">Register for an account</a>';
    $html .= '</div>';
  }

  $html .= '</div></div>'; // header & header_content
  $html .= '<div class="body_content">';


  // Catch misconfiguration errors.
  if (!is_config_setup()) {
    $html .= render_error('Your configuration is not complete. '
                          .'Follow the directions in <tt><b>lib/config.php.sample</b></tt> to get set up');
    $html .= '</body>';
    echo $html;
    exit;
  }

  return $html;
}


/*
 * Prevent caching of pages. When the Javascript needs to refresh a page,
 * it wants to actually refresh it, so we want to prevent the browser from
 * caching them.
 */
function prevent_cache_headers() {
  header('Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
  header('Pragma: no-cache');
}

/*
 * Register a bit of javascript to be executed on page load.
 *
 * This is printed in render_footer(), so make sure to include that on all pages.
 */
function onloadRegister($js) {
  global $onload_js;
  $onload_js .= $js;
}

/*
 * Print the unified footer for all pages. Includes all onloadRegister'ed Javascript for the page.
 *
 */
function render_footer() {
  global $onload_js;
  $html = '</div>' .
    '<div class="footer_stuff">This is an awesome running app.</div>';

  // the init js needs to be at the bottom of the document, within the </body> tag
  // this is so that any xfbml elements are already rendered by the time the xfbml
  // rendering takes over. otherwise, it might miss some elements in the doc.
  if (is_fbconnect_enabled()) {
    $html .= render_fbconnect_init_js();
  }

  // Print out all onload function calls
  if ($onload_js) {
    $html .= '<script type="text/javascript">'
      .'window.onload = function() { ' . $onload_js . ' };'
      .'</script>';
  }

  $html .= '</body></html>';

  return $html;
}

/*
 * Default index for logged out users.
 *
 */
function render_logged_out_index() {

  $html = '<img src="http://www.somethingtoputhere.com/therunaround/images/runaround_image.jpg" class="welcome_img" />';

  $html .= '<div class="welcome_dialog">';
  $html .= '<h3>Welcome to the Run Around</h3>';

  $html .= '<p>This is a simple site where you can log your runs and chart progress on your '
    .'workout routine.</p>';


  $html .= '<div class="clearfix"><form action="login.php" method="post">'
    . '<div class="login_sector">'
    . '<div class="login_prompt"><b>Login</b>:</div>'
    .'<div class="clearfix"><label>Username:</label><input name="username" class="inputtext" type="text" size="20" value="" /></div> '
      .'<div class="clearfix"><label>Password:</label><input name="password" class="inputtext" type="password" size="20" value=""/></div> '
      .'<input id="submit" class="inputsubmit" value="Login" name="submit" type="submit" />'
    . '</div>';

  if (is_fbconnect_enabled()) {
    $html .= '<div class="login_sector_fb">';
    $html .= '<div class="login_prompt">Or <b>login</b> with Facebook:</div>';
    $html .= render_fbconnect_button('medium');
    $html .= '</div>';
  }

  $html .= '</form></div>';


  $html .= '<div class="signup_container"> '
    .'Don\'t have an account? <a href="register.php">Register Now!</a> ';

  $html .= '</div></div>';

  return $html;
}

function render_add_run_table($user) {
  $html  = '<h3>Where did you run recently?</h3>';
  $html .= '<form action="index.php" method="post">';
  $html .= render_add_run_table_fields();
  if ($user->is_facebook_user()) {
    $style = '';
  } else {
    $style = 'visibility:hidden';
    onloadRegister('facebook_show_feed_checkbox();');
  }
  $html .= '<p id="publish_fb_checkbox" style="'.$style.'" >'
      .'<img src="http://static.ak.fbcdn.net/images/icons/favicon.gif" /> '
      .'<input type="checkbox" name="publish_to_facebook" checked /> '
      .'Publish this run to Facebook'
      .'</p>';
  $html .= render_input_button('Add Run', 'submit');
  $html .= '</form>';

  return $html;
}

/*
 * Renders input fields for adding run.  Used by both index.php and
 * handlers/self_publisher.php.
 */
function render_add_run_table_fields() {
  $html  = '<table class="add_run_table">';
  $html .= render_text_editor_row('route', 'Where did you go?');
  $html .= render_text_editor_row('miles', 'Number of Miles');
  $html .= '<tr><td class="editor_key"><label>Date (MM/DD/YYYY)</label></td>'
    .'<td class="editor_value">'
    .'<input id="date_month" class="inputtext datefield" name="date_month" type="text" size="2" maxlength="2" /> '
    .'/<input id="date_day" class="inputtext datefield" name="date_day" type="text" size="2" maxlength="2" /> '
    .'/<input id="date_year" class="inputtext datefield" name="date_year" type="text" size="4" maxlength="4" /> '
    . ' | ' . render_populate_date_link('Today')
    . ' | ' . render_populate_date_link('Yesterday')
    .'</td>'
    .'</tr>';
  $html .= '</table>';
  return $html;
}

/*
 * Form for editing the user's account info.
 */
function render_edit_user_table ($user) {
  $html .= '<form action="account.php" method="post">';
  $html .= '<input type="hidden" name="username" value="'.$user->username.'" >';
  $html .= '<table class="editor">';

  if ($user->is_facebook_user()) {
    $name = $user->getName() . ' &nbsp;<img src="http://static.ak.fbcdn.net/images/icons/favicon.gif" />';
    $email = '<b>Contact via Facebook</b>';
  } else {
    $name = '<input id="name" class="inputtext" type="text" size="20" value="'.$user->getName().'" name="name">';
    $email = '<input id="email" class="inputtext" type="text" size="20" value="'.$user->getEmail().'" name="email">';
  }

  $html .= '<tr>'
    .'<td><label id="label_name" for="name">Name</label></td>'
    .'<td>'.$name.'</td>'
    .'</tr>'
    .'<tr>'
    .'<td><label id="label_email" for="email">Email</label></td>'
    .'<td>'.$email.'</td>'
    .'</tr>'
    .'<tr>'
    .'<td><label id="label_email_settings" for="email_settings">Email Settings</label></td>'
    .'<td>'
    .'<a href="#" onclick="facebook_prompt_permission(\'email\'); return false;">Receive Email Updates</a>'
    .'</td>'
    .'</tr>';

  if ($user->hasPassword()) {
    $html .= '<tr>'
      .'<td><label id="label_password" for="password">Password</label></td>'
      .'<td><input id="password" class="inputtext" type="password" size="20" value="'.PASSWORD_PLACEHOLDER.'" name="password">'
      .'</tr>';
  }

  $html .= '</table>';
  $html .= render_input_button('Save Changes', 'submit');
  $html .= '</form>';
  return $html;
}

function render_populate_date_link($datestr) {
  $time = strtotime($datestr);
  $month = date('m', $time);
  $day = date('d', $time);
  $year = date('Y', $time);
  return '<a onclick="populate_date(\''.$month.'\', \''.$day.'\', \''.$year.'\'); return false;">'.$datestr.'</a>';
}

function render_text_editor_row($id, $label, $value='', $size=20, $after_input='') {
  return '<tr><td class="editor_key">'
    .'<label id="label_'.$id.'" for="'.$id.'">'.$label.'</label>'
    .'</td><td class="editor_value">'
    .'<input id="'.$id.'" class="inputtext" type="text" size="'.$size.'" value="'.$value.'" name="'.$id.'"/>'
    .$after_input
    .'</td></tr>';
}

function render_input_button($label, $name) {
  return '<input class="inputsubmit" type="submit" name="'.$name.'" value="'.$label.'"/>';
}

function render_error($msg) {
  return '<div class="error">'.$msg.'</div>';
}
function render_success($msg) {
  return '<div class="success">'.$msg.'</div>';
}

function render_run($run) {
  return '<tr><td>'
    .date('m/d/Y', $run->date)
    .'</td><td>'
    .$run->miles . ' miles'
    .'</td><td>'
    .$run->route
    .'</td></tr>';
}

function render_friends_table($friends) {

  if (empty($friends)) {
    return '';
  }

  $html = '';
  $html .= '<table class="friends_table">';

  foreach ($friends as $friend) {
    $friend_as_user = User::getFacebookUser($friend);
    if ($friend_as_user) {
      $html .= '<tr class="friend_row">';
      $html .= '<td class="profilepic">' . $friend_as_user->getProfilePic() . '</td>';
      $html .= '<td class="user">';
      $html .= '<div class="name">'.$friend_as_user->getName().'</div>';
      $html .= '<div class="status">'.$friend_as_user->getStatus().'</div>';
      $html .= '</td>';
      $html .= '<td class="lastrun">';
      $runs = $friend_as_user->getRuns();
      if (!empty($runs)) {
        $html .= $runs[0]->miles.' miles on '.strftime('%m/%d/%Y', $runs[0]->date);
      } else {
        $html .= 'No runs ... yet.';
      }
      $html .= '</td>';
      $html .= '</tr>';
    }
  }

  $html .= '</table>';
  return $html;
}

function render_connect_invite_link($has_existing_friends = false) {
  $more = $has_existing_friends ? ' more' : '';
  $num = '<fb:unconnected-friends-count></fb:unconnected-friends-count>';

  $one_friend_text = 'You have one'.$more.' Facebook friend that also uses The Run Around. ';
  $multiple_friends_text = 'You have '.$num.$more.' Facebook friends that also use The Run Around. ';
  $invite_link = '<a onclick="FB.Connect.inviteConnectUsers(); return false;">Invite them to Connect.</a>';

  $html = '';
  $html .= '<fb:container class="HideUntilElementReady" condition="FB.XFBML.Operator.equals(FB.XFBML.Context.singleton.get_unconnectedFriendsCount(), 1)" >';
  $html .= '<span>'.$one_friend_text.' '.$invite_link.'</span>';
  $html .= '</fb:container>';
  $html .= '<fb:container class="HideUntilElementReady" condition="FB.XFBML.Operator.greaterThan(FB.XFBML.Context.singleton.get_unconnectedFriendsCount(), 1)" >';
  $html .= '<span>'.$multiple_friends_text.' '.$invite_link.'</span>';
  $html .= '</fb:container>';
  return $html;
}
