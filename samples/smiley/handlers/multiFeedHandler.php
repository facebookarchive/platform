<?php
  /*
   * multiFeedHandler.php - Posting to other's feed form handler
   *
   */

include_once '../constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';
include_once LIB_PATH.'feed.php';

$picked = $_POST['picked'];

$moods  = get_other_moods();

if ($picked != -1) {
  $feed = array('template_id' =>  FEED_STORY_2,
                'template_data' => array('emote'=> $moods[$picked][1],
                                         'emoteaction'=> $moods[$picked][0]));

  $data = array('method'=> 'multiFeedStory',
                'content' => array( 'feed'    => $feed,
                                    'next'    => 'http://apps.srush2.devrs006.facebook.com/mysmiley/'
                                    ));

} else {
  $data = array('errorCode'=> 1,
              'errorTitle'=> 'No mood selected',
              'errorMessage'=>'Please select a smiley.');
}

echo json_encode($data);
