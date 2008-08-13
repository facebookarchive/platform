<?php
include_once 'common.php';


try {
  $event_info=$facebook->api_client->events_get();

  if (empty($event_info)) {
    echo $event_info."<br />";
  } else {
    foreach ($event_info as $index=>$event) {
      echo '<div><a href="edit.php?eid='.$event['eid'].'">'.$event['name'].'</a></div>';
    }
  }
} catch (Exception $e) {
  echo $e->getMessage();
  return;
}

?>
<a href="create.php">Create Event</a>