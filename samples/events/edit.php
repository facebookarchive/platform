<?php
include_once 'common.php';

if (!isset($_GET['eid'])) {
  echo "Which event???";
  return;
}
$eid=$_GET['eid'];
if (isset($_POST['name'])) {
  $_POST['start_time']=mktime($_POST['start_time_hour']+($_POST['start_time_ampm']==="pm"?12:0),$_POST['start_time_min'],0,$_POST['start_time_month'],$_POST['start_time_day']);
  $_POST['end_time']=mktime($_POST['end_time_hour']+($_POST['end_time_ampm']==="pm"?12:0),$_POST['end_time_min'],0,$_POST['end_time_month'],$_POST['end_time_day']);
  try {
    if ($facebook->api_client->events_edit($eid,json_encode($_POST))) {
      echo "Event successfully edited.";
    } else {
      echo "Event edit failed.";
    }
  } catch (Exception $e) {
    handle_exception($e);
    return;
  }
}

render_edit($eid);
?>