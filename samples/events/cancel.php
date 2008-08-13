<?php
include_once 'common.php';

if (!isset($_GET['eid'])) {
  echo "Which event???";
  return;
}
$eid=$_GET['eid'];
if (isset($_POST['cancel_eid'])) {
  try {
    if ($facebook->api_client->events_cancel($_POST['cancel_eid'],$_POST['cancel_message'])) {
      echo "Event successfully cancelled.";
    } else {
      echo "Event cancellation failed.";
    }
  } catch (Exception $e) {
    handle_exception($e);
    return;
  }
} else {
?>
<form method="post">
<input type="hidden" name="cancel_eid" value="<?php echo $eid ?>" />
<input type="hidden" value="cancel" name="cancel" /><input type="text" name="cancel_message" />
<input type="submit" value="cancel" />
</form>
<?php
}
 ?>
<a href="index.php">Manage events</a>