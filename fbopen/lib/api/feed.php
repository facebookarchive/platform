<?php

/******BEGIN LICENSE BLOCK*******
* 
* Common Public Attribution License Version 1.0.
*
* The contents of this file are subject to the Common Public Attribution 
* License Version 1.0 (the "License") you may not use this file except in 
* compliance with the License. You may obtain a copy of the License at
* http://developers.facebook.com/fbopen/cpal.html. The License is based 
* on the Mozilla Public License Version 1.1 but Sections 14 and 15 have 
* been added to cover use of software over a computer network and provide 
* for limited attribution for the Original Developer. In addition, Exhibit A 
* has been modified to be consistent with Exhibit B.
* Software distributed under the License is distributed on an "AS IS" basis, 
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License 
* for the specific language governing rights and limitations under the License.
* The Original Code is Facebook Open Platform.
* The Original Developer is the Initial Developer.
* The Initial Developer of the Original Code is Facebook, Inc.  All portions 
* of the code written by Facebook, Inc are 
* Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
*
*
********END LICENSE BLOCK*********/


// Facebook Copyright 2006 - 2008

include_once $_SERVER['PHP_ROOT'] . '/lib/grammar/grammar.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/wrapper.php';
include_once $_SERVER['PHP_ROOT'].'/lib/fbml/flavors/canvas_page_flavor.php';

/**
 * Adds a platform-published story to a user's feed
 *
 * @param   int    $app_id
 * @param   int    $user_id
 * @param   array  $feed_story
 * @return  bool   true on success
 */
// FBOPEN:NOTE - Insert into your DB here.
function application_publish_story_to_user($app_id, $user_id, $feed_story) {
  return true;
}

/**
 * Publishes the action of a user
 *
 * @param   int    $app_id
 * @param   int    $user_id
 * @param   array  $feed_story
 * @return  bool   true on success
 */
// FBOPEN:NOTE - Insert into your DB here.  Note that this store is intended
//  to surface in friends' feeds, according to your rules of relevance,
//  and add to your 'mini-feed' should you create one.
function application_publish_action_of_user($app_id, $user_id, $feed_story) {
    return true;
}


/**
 * Gets the number of available feed points for an application to use on a user
 *
 * @param   int    $app_id
 * @param   int    $user_id
 * @return  int    number of feed points
 */
 // FBOPEN:NOTE - Part of an implementation-specific feed gating system.
function application_get_available_feed_points($app_id, $user_id) {
  return 100;
}

/**
 * Add an entry to the feed counter table to the log of action calls on behalf
 * of the calling app.  Returns false if no more action calls can be made, or if there
 * is an error.
 *
 * @param   int    $app_id
 * @param   int    $user_id
 * @return  bool   true if application call was recorded
 */
 // FBOPEN:NOTE - If you wish to record the number of feed publish action
 //  calls for limiting purposes, do so here.
function application_add_publish_action_call($app_id, $user_id) {
  return true;
}

/**
 * This function logs the feed points used, probabilistically determines whether or not the
 * story should be published, and returns true if it should.
 *
 * @param   int     $app_id
 * @param   int     $user_id
 * @param   int     $num_feed_points
 * @return  bool    whether or not story should be published
 */
 // FBOPEN:NOTE - Part of an example feed gating mechanism
function application_use_feed_points($app_id, $user_id, $num_feed_points) {
  return true;
}

$GLOBALS['API_FEED']['MAX_TITLE'] = 100;
$GLOBALS['API_FEED']['MAX_BODY'] = 200;

// FBOPEN:NOTE - You may wish to allow different feed rules for
// different applications
function need_illegal_story_check($app_id)
{
  return true;
}

// disallow the word message in the feed outside of fb:user tag
// FBOPEN:NOTE - You may wish to allow different feed rules for
// different applications
function is_illegal_feed_story($app_id, $parsestr) {
  return false;
}

// disallow the word message in the feed outside of fb:user tag
// FBOPEN:NOTE - this is an example implementation of is_illegal_feed_story_tree
//  and is unused in the current API OS sample.
function is_illegal_feed_story_tree($fbml) {
  if (!$parse_tree_root) {
    return false;
  }

  // now traversing the tree
  $node_array = array();
  $node_array[] = $parse_tree_root;

  while ($currnode = array_pop($node_array)) {
    if (0 != strcasecmp('fb:user', $currnode->get_tag_name())) {
      if ($currnode->is_plaintext()) {
        // check for the word message in the node
        if (preg_match('/\bmessage(?:s?)\b/i', $currnode->text_not_escaped())) {
          return true;
        }
      } else {
        // queue up children if any
        $node_array = array_merge($currnode->get_children(), $node_array);
      }
    }
  }

  return false;
}

/**
 * Checks the syntax of the markup for a feed story, filtering and replacing as necessary
 *
 * @param   string $title
 * @param   string $body
 * @param   string $image_1
 * @param   string $image_2
 * @param   string $image_3
 * @param   string $image_4
 * @param   string &$error - contains the error on an unsuccessful call
 * @return  array if successful, error string or false if not
 *
 */
function application_create_feed_story($app_id, $user, $require_user_link,
                                       $title, $body,
                                       $image_1, $image_1_link,
                                       $image_2, $image_2_link,
                                       $image_3, $image_3_link,
                                       $image_4, $image_4_link,
                                       &$error) {
  // Get rid of nulls from input
  $title = str_replace("\0", '', $title);
  $body  = str_replace("\0", '', $body);


  // Check title length
  if (strlen(strip_tags($title)) > $GLOBALS['API_FEED']['MAX_TITLE']) {
    $error = 'error_title_length';
    return false;
  }
  $num_matches = preg_match_all('/<a /', $title, $matches);
  if ($num_matches > 1) {
    $error = 'error_title_link';
    return false;
  }



  // FBOPEN:NOTE - you may with to create separate flavors here.
  /*
  if ($require_user_link) {
    $flavor = new FeedTitleWithNamesFBMLFlavor($env);
  } else {
    $flavor = new FeedTitleFBMLFlavor($env);
  }
  */

  // Check for user links in title if necessary
  if ($require_user_link) {
    // FBOPEN:NOTE Add your checking here.
  }

  // Check body
  if ($body) {
    if (strlen(strip_tags($body)) > $GLOBALS['API_FEED']['MAX_BODY']) {
      $error = 'error_body_length';
      return false;
    }

    // See how it renders to make sure it doesn't come out blank
    $env = array('user' => $user, 'app_id' => $app_id);


    // FBOPEN:NOTE - you may wish to use a different flavor or implementation here.
    // This is just a sample.
    $fbml_flavor = new FBMLCanvasPageFlavor($env);
    $fbml_impl = new FBJSEnabledFacebookImplementation($fbml_flavor);

    $html = fbml_sample_parse($body, $fbml_impl);

    if ((!$html) || (need_illegal_story_check($app_id) && is_illegal_feed_story($app_id, $body))) {
      $error = 'error_illegal_content';
      return false;
    }
  }

  $images = api_feed_validate_images($app_id, $image_1, $image_1_link, $image_2, $image_2_link,
                                     $image_3, $image_3_link, $image_4, $image_4_link,
                                     $do_proxy=true, $error);
  if (false === $images) { // error has been set by api_feed_validate_images
    return false;
  }

  $short_info = application_get_short_info($app_id);
  return array('title'       => $title,
               'body'        => $body,
               'images'      => $images,
               'allow_names' => $require_user_link);
}

/**
 * Validates the images in a feed story -- makes sure the
 * image links are legitimate URLs. On success, returns an array of
 *   array('fbml' => FBML,
 *         'href' => HREF)
 * arrays that can be stored in the feed story
 *
 * @param int $app_id
 * @param string $image_1
 * @param string $image_1_link
 * @param string $image_2
 * @param string $image_2_link
 * @param string $image_3
 * @param string $image_3_link
 * @param string $image_4
 * @param string $image_4_link
 * @return array
 */
function api_feed_validate_images($app_id, $image_1, $image_1_link, $image_2, $image_2_link,
                                  $image_3, $image_3_link, $image_4, $image_4_link, $do_proxy=true,
                                  &$error = null) {

  $input_images = array(array('src'  => str_replace("\0", '', $image_1),
        'href' => str_replace("\0", '', $image_1_link)),
      array('src'  => str_replace("\0", '', $image_2),
        'href' => str_replace("\0", '', $image_2_link)),
      array('src'  => str_replace("\0", '', $image_3),
        'href' => str_replace("\0", '', $image_3_link)),
      array('src'  => str_replace("\0", '', $image_4),
        'href' => str_replace("\0", '', $image_4_link)));

  foreach ($input_images as $input_image) {
    if ($image_src = $input_image['src']) {
      if ((!$input_image['href']) || substr($input_image['href'], 0, 7) != 'http://') {
        $error = 'error_photo_link';
      return false;
    }

    $image_fbml = '<img src="'.$input_image['src'].'" />';
    $images[] = array('fbml' => $image_fbml,
        'href' => $input_image['href']);
    }
  }
  return $images;
}

