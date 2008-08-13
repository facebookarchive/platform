<?php
include_once 'common.php';

if (isset($_POST['name'])) {
  $_POST['start_time']=mktime($_POST['start_time_hour']+($_POST['start_time_ampm']==="pm"?12:0),$_POST['start_time_min'],0,$_POST['start_time_month'],$_POST['start_time_day']);
  $_POST['end_time']=mktime($_POST['end_time_hour']+($_POST['end_time_ampm']==="pm"?12:0),$_POST['end_time_min'],0,$_POST['end_time_month'],$_POST['end_time_day']);
  try {
    $eid=$facebook->api_client->events_create(json_encode($_POST));
    $facebook->redirect('edit.php?eid=' . $eid);
  } catch (Exception $e) {
    handle_exception($e);
    return;
  }
}

render_create();
?>