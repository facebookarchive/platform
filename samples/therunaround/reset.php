<?php

include_once 'lib/core.php';
include_once 'lib/user.php';
include_once 'lib/display.php';

$raw_users = array(
    /*    username        name           email                           password */
    array('ccheever',     'Charlie C.',  'ccheever@gmail.com',           'run'),
    array('julie',        'Julie Z.',    'jzhuo@cs.stanford.edu',        'run'),
    array('luke',         'Luke S.',     'lshepard@alumni.uchicago.edu', 'run'),
    array('ruchi',        'Ruchi S.',    'ruchi@facebook.com',           'run'),
    array('pete',         'Pete B.',     'pbratach@facebook.com',        'run'),
    array('wei',          'Wei Z.',      'wzhu@facebook.com',            'run'),
    array('james',        'James L.',    'jleszcze@facebook.com',        'run'),
    );

$errors = array();
$created = array();

// delete any existing accounts
foreach ($raw_users as $raw_user) {
  $user = User::getByUsername($raw_user[0]);
  if ($user) {
    if (!$user->delete()) {
      $errors[] = 'Could not delete user '. $raw_user[0].'.';
    }
  }
}

// register new accounts
foreach ($raw_users as $raw_user) {
  $user_params = array('username' => $raw_user[0],
                       'name'     => $raw_user[1],
                       'email'    => $raw_user[2],
                       'password' => $raw_user[3]);
  $user = new User($user_params);
  if ($user->saveAndRegister()) {
    $created[] = $raw_user[0];
    $users[] = $user;
  } else {
    $errors[] = 'Could not create user '.$raw_user[0].'.';
  }
}

// populate with some fake data

$routes = array('Stanford campus' => 2,
                'Palo Alto hills' => 5,
                'Golden Gate Bridge' => 3,
                'Big Sur Marathon' => 26,
                'Santa Cruz' => 90,
                'Berkeley' => 10,
                'Up and down Folsom' => 5);

$route_names = array_keys($routes);

foreach ($users as $user) {
  // pick two to five random routes

  $num = (mt_rand() / getrandmax()) * 3 + 2;
  for ($i = 0; $i < $num; ++$i) {
    $date = time() - $i * 5 * 86400;

    $route = next($route_names);
    if (!$route) {
      $route = reset($route_names);
    }

    $run = new Run($user, array('date' => $date,
                                'route' => $route,
                                'miles' => $routes[$route]));

    $run->save();
  }
}

echo render_header();

echo '<div class="bluebox">';
echo '<h2>';
echo (count($errors) == 0) ? 'Success!' : 'Completed (w/ Warnings)';
echo '</h2>';
echo '1. Created '.count($created).' Accounts:';
echo '<ul>';
foreach ($created as $user) {
  echo '<li>'.$user.'</li>';
}
echo '</ul>';
echo '</div>';

if (!empty($errors)) {
  echo '<div class="errors">';
  foreach ($errors as $error) {
    echo $error . '<br />';
  }
  echo '</div>';
}

echo render_footer();
