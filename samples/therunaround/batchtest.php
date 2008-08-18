<?php

include_once 'lib/core.php';

$fb = facebook_client();
$fb->require_login();

$fb->api_client->begin_batch();

$friends =& $fb->api_client->friends_get();
$notifications =& $fb->api_client->notifications_get();

$fb->api_client->end_batch();

echo '<h1>'.count($friends).' Friends</h1>';
