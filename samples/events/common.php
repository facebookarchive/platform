<?php
include_once './client/facebook.php';

// Get these from http://developers.facebook.com
$api_key = '';
$secret  = '';

$facebook = new Facebook($api_key, $secret);
$facebook->api_client->server_addr="http://api.new.facebook.com/restserver.php";
//$facebook->require_frame();
//$user = $facebook->require_login();

foreach ($_POST as $key => $value) {
  if (!strncmp($key,"fb_",3)) {
    unset($_POST[$key]);
  }
}


function handle_exception($e) {
  if ($e->getCode()===FacebookAPIErrorCodes::API_EC_PERMISSION_EVENT) {
    $facebook->redirect('http://www.new.facebook.com/authorize.php?api_key='.$api_key.'&v=1.0&ext_perm=create_event');
  } else {
    echo $e->getMessage();
  }
}

function render_create() {
  render_event_helper(0);
}
function render_edit($eid) {
  render_event_helper($eid);
}
function render_event_helper($eid) {
  global $facebook;
  $dformat = 'm/d/Y h:i:a';
  $format = '%m/%d/%Y %I:%M:%p';
  $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "December");
  if (!empty($eid)) {
    echo "<h3>EID: ".$eid."</h3>";
    try {
      $event_info=$facebook->api_client->events_get($user,$eid);
      if (empty($event_info)) {
        echo $event_info."<br />";
      } else {
        $event_info=$event_info[0];
        foreach($event_info as $key=>$value) {
          echo '<div>'.$key.': '.$value.'</div>';
        }
      }
      echo '<a href="cancel.php?eid=' . $eid . '">Cancel</a> | <a href="./">Manage Events</a>';
    } catch (Exception $e) {
      echo $e->getMessage();
      return;
    }
    $event_info['city'] = $event_info['venue']['city'];
    unset($event_info['venue']);
    $event_info['category'] = 1;unset($event_info['event_type']);
    $event_info['subcategory'] = 1;unset($event_info['event_subtype']);
    $event_info['start_time'] = strptime(date($dformat, $event_info['start_time']), $format);
    $event_info['end_time'] = strptime(date($dformat, $event_info['end_time']), $format);
    unset($event_info['creator']);unset($event_info['update_time']);unset($event_info['eid']);
  } else {
    $event_info = array('name'         => 'name',
                        'category'     => '1', 
                        'subcategory'  => '1',
                        'host'         => 'host',
                        'location'     => 'location',
                        'city'        => 'Palo Alto',
                        'start_time'   => strptime(date($dformat), $format),
                        'end_time'     => strptime(date($dformat), $format));
  }
  echo '<style type="text/css">label { display:block; }</style><form method="post">';
  if (!empty($eid)) echo '<input type="hidden" name="eid" value="'.$eid.'" />';
  foreach ($event_info as $key => $value) {
    if (is_array($value)) {
      echo '<label>'.ucfirst($key);
      echo '<select autocomplete="off"  id="'.$key.'_month" name="'.$key.'_month">';
      foreach ($months as $index => $month) {
        echo '<option value="'.$index.'"'.($index===$value['tm_mon']?' selected="selected"':'').'>'.$month.'</option>';
      }
      echo '</select><select autocomplete="off" id="'.$key.'_day" name="'.$key.'_day">';
      for ($i=1; $i<32; $i++) {
        echo '<option value="'.$i.'"'.($i===$value['tm_mday']?' selected="selected"':'').'>'.$i.'</option>';
      }
      echo '</select><span> at </span> <select id="'.$key.'_hour" name="'.$key.'_hour">';
      for ($i=0; $i<12; $i++) {
        echo '<option value="'.$i.'"'.($i===($value['tm_hour']%12)?' selected="selected"':'').'>'.$i.'</option>';
      }
      echo '</select>: <select id="'.$key.'_min" name="'.$key.'_min">';
      for ($i=0; $i<60; $i++) {
        echo '<option value="'.$i.'"'.($i===$value['tm_min']?' selected="selected"':'').'>'.$i.'</option>';
      }
      echo '</select> <select id="'.$key.'_ampm" name="'.$key.'_ampm"><option value="am"'.(floor($value['tm_hour']/12)==0?' selected="selected"':'').'>am</option><option value="pm"'.(floor($value['tm_hour']/12)==1?' selected="selected"':'').'>pm</option></select></label>'."\n\n";
    } else {
      echo '<label>'.ucfirst($key).' <input type="text" name="'.$key.'" id="'.$key.'" value="'.$value.'" /></label>'."\n\n";
    }
  }
  echo '<input type="submit" value="'.(empty($eid)?'Create Event':'Edit Event').'" /></form>';
}

?>
