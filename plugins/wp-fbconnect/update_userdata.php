<?php

/* Optional external script to update cached Facebook user data.  Per
the API caching policy user data may only be cached for 24 hours.
Normally this happens in the first HTTP request on the 24th hour, but
for large sites this may slow down that request or even cause it to
timeout.

NOTE: This script is not required.  It is only intended for use on
sites with a large number of Facebook users.

This script is intended to be run from cron daily.

*/
include_once 'fbconnect.php';

echo "Updating user data...";

$res = fbc_update_facebook_data($force=true);

if ($res === -1) {
  echo "Error!\n";
  exit(1);
} else {
  echo "$res facebook users done.\n";
  exit(0);
}


