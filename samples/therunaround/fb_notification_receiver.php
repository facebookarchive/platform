<?php

  /*
   * When you register a user on Facebook using an email hash, you also register an "account id".
   * If that user responds to the request and connects their accounts, then you can be notified at
   * the "post-authorize-url" as configured in your developer account settings.
   *
   * This is that post-authorize-url.
   */

include_once 'lib/core.php';

$account_ids = idx($_POST, 'fb_sig_linked_account_ids');
$fb_uid = facebook_client()->get_loggedin_user();

if (!($account_ids && $fb_uid)) {
  exit;
}

// Theoretically possible for a single facebook user to have multiple accounts on this system,
// Since they could have multiple email addresses. so account for that
foreach ($account_ids as $account_id) {
    $user = User::getByUsername($account_id);
    $user->connectWithFacebookUID($fb_uid);
    $user->save();
    error_log("Connected user $account_id with facebook user id $fb_uid");
}
