<?php
  /*
   * feedHandler.php - Feed form handler
   *
   */
include_once '../constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$fb     = get_fb();

// The smiley that was chosen
$picked = $_POST['picked'];
foreach ($_POST as $key=> $val) {
  error_log($key.'-'.$val);
}
$moods  = get_moods();


if ($picked != -1) {
  // Set data for this option. Use preferences for simplicity
  $old = $fb->api_client->data_getUserPreference(0);
  $fb->api_client->data_setUserPreference(0, '' . $picked . $old);

  $image = IMAGE_LOCATION . '/smile'.$picked.'.jpg';

  $images = array(array('src' => $image,
                        'href' => 'http://apps.facebook.com/mysmiley'));
  error_log($moods[$picked][0]);
  error_log($picked);
  $feed = array('template_id' => FEED_STORY_1,
                'template_data' => array('mood' => $moods[$picked][0],
                                         'images' => $images,
                                         'mood_src' => $image)
                );

  $data = array('method'=> 'feedStory',
                'content' => array( 'feed'    => $feed,
                                    'next'    => 'http://apps.facebook.com/mysmiley/index.php'));

} else {
  $data = array('errorCode'=> VALIDATION_ERROR,
                'errorTitle'=> 'No smiley selected',
                'errorMessage'=>'Please select a smiley.');
}

echo json_encode($data);

  /*  $content = '<style>.box2 {
  padding: 10px;
  width : 100px;
  float : left;
  text-align: center;
  border: black 1px;
  margin-right: 10px;
  margin-left: 10px;
  cursor: pointer;
  border: black solid 2px;
  background: orange;
  margin-left: 32px;
  margin-top: 30px;
}
h2 {
text-align: center;
font-size: 11px;
color:#3B5998;

}

.box2 .smiley {
  font-size: 35pt;
  font-weight: bold;
  padding: 20px;
}
</style>
<div class="box2"><div class="smiley">'.$moods[$picked][1].'</div><div >'.$moods[$picked][0].'</div></div>';*/
