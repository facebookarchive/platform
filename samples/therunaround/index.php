<?php

define(MAIN_PATH, realpath('.'));
include_once MAIN_PATH.'/init.php';

echo render_header();

$user = User::getLoggedIn();

if (!$user) {
  echo render_logged_out_index();
  echo render_footer();
  exit;
}

$params = parse_http_args($_POST, array('time',
                                        'miles',
                                        'route',
                                        'date_month',
                                        'date_day',
                                        'date_year',
                                        'publish_to_facebook'));

// If the user has added a run, then handle the form post

if (!empty($params['miles'])) {
  $run = new Run($user, $params);
  if (!$run->save()) {
    echo render_error('Something went wrong while saving the run.');
  } else {
    $success = 'Added a new run!';

    // This will only be true if the checkbox on the previous page was checked
    // The feed_loading div will be killed by JS that runs once the feed form
    // is generated  (since it can sometimes take a second or two)

    if ($params['publish_to_facebook']) {
      register_feed_form_js($run);
      $success .= '<div id="feed_loading">Publishing to Facebook... '
        .'<img src="http://static.ak.fbcdn.net/images/upload_progress.gif?0:25923" /></div>';
    }
    echo render_success($success);
  }
}

// Show the basic add run form on the home page

echo '<div class="clearfix">';
echo '<div class="bluebox">';
echo render_add_run_table($user);
echo '</div>';

// Show any runs the user has already added

$runs = $user->getRuns();
if ($runs) {
  echo '<div id="showruns" class="bluebox">';
  $miles = 0;
  foreach ($runs as $run) {
    $miles += $run->miles;
  }

  echo '<h3>Recent Runs</h3>';
  echo '<table>'
    .'<tr>'
    .'<th>Date</th>'
    .'<th>Miles</th>'
    .'<th>Route</th>'
    .'</tr>';
  foreach ($runs as $run) {
    echo render_run($run);
  }
  echo '</table>';

  if ($miles > 0) {
    echo '<div style="padding: 5px; font-weight: bold;">You ran ' . $miles . ' miles recently!</div>';
  }

  echo '</div>';
}

// If the user has connected, then show info about their friends

if ($user->is_facebook_user()) {
  echo '<div class="bluebox friends_box">';
  echo '<h3>Friends</h3>';

  $friends = get_connected_friends($user);
  if (is_array($friends) && !empty($friends)) {
    echo render_friends_table($friends);
  } else {
    echo 'You don\'t have any friends yet!<br />';
  }

  echo '<div class="connect_invites">';
  echo render_connect_invite_link(/* has_existing_friends */ (count($friends) > 0));
  echo '</div>';

  echo '</div>';
}

echo '</div>';
echo render_footer();
