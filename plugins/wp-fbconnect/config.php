<?php

/* Controls whether the absolutely positioned "Welcome, username"
 window is displayed.  If this is disabled you *must* place equivalent
 information somewhere on your page.  */
define('FBC_USER_PROFILE_WINDOW', true);

define('FBC_ANONYMOUS_DISPLAYNAME', 'Facebook User');


/*
  In each of the templates below, the first template is for a single
  story and the second template is for an aggregated story.
 */
$fbc_one_line_stories = array(
  '{*actor*} commented on the <a href="{*blog-url*}">{*blog-name*}</a> blog post "<a href="{*post-url*}">{*post-title*}</a>".',
  '{*actor*} posted comments on <a href="{*blog-url*}">{*blog-name*}</a>.'
);


$fbc_short_story_templates = array(
  array('template_title' =>
        '{*actor*} commented on the <a href="{*blog-url*}">{*blog-name*}</a> blog post "<a href="{*post-url*}">{*post-title*}</a>".',
        'template_body' => ''),
   array('template_title' =>
         '{*actor*} posted comments on <a href="{*blog-url*}">{*blog-name*}</a>.',
         'template_body' => '')
);

