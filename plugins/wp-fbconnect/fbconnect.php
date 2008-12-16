<?php
/*  Copyright 2008  Adam Hupp  (email : adam at hupp.org / ahupp at facebook.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: Facebook Connect
Author: Adam Hupp
Author URI: http://hupp.org/adam/
Description: Integrate Facebook and Wordpress with Facebook Connect.  Provides single-signon, avatars, and newsfeed comment publication.  Requires a <a href="http://www.facebook.com/developers/">Facebook API Key</a> for use.
Version: 1.0

*/

require_once('common.php');
require_once('config.php');

define('FBC_APP_KEY_OPTION', 'fbc_app_key_option');
define('FBC_APP_SECRET_OPTION', 'fbc_app_secret_option');
define('FBC_BUNDLE_OPTION', 'fbc_template_bundle_id');
define('FBC_LAST_UPDATED_CACHE_OPTION', 'fbc_last_updated_cache_option');
define('FBC_REMOVE_NOFOLLOW_OPTION', 'fbc_remove_nofollow_option');

function fbc_get_fbconnect_user() {
  return fbc_facebook_client()->get_loggedin_user();
}

function fbc_init_auth() {

  $fbuid = fbc_facebook_client()->get_loggedin_user();
  $user = wp_get_current_user();
  $assoc_fbuid = fbc_get_fbuid($user->ID);

  if ($assoc_fbuid) {
    if ($fbuid) {
       // user is already logged in to both
      return;
    } else {
      //wp session, no fbsession = logout of wp and reload page
      wp_logout();
      header('Location: ' . $_SERVER['REQUEST_URI']);
      exit();
    }
  } else {
     if ($user->ID) {
       // wpuser not associated w/ fb.  do nothing
       return;
     } else if($fbuid) {
       // not a wp user, but fb authed = log them in to wp
       $res = fbc_login_if_necessary();
       if ($res > 0) {
         $wp_uid = fbc_fbuser_to_wpuser($res);
         wp_set_current_user($wp_uid);
       }
     } else {
       // neither facebook nor wordpress, do nothing
       return;
     }
  }
}

function fbc_init() {

  if (fbc_is_configured()) {

    add_action('init', 'fbc_init_auth');

    /* Adds core and wp-fbconnect css to page.  Also responsible for
       setting up onloads that initialize fbconnect on the page
    */
    add_action('wp_head', 'fbc_header');

    /* Similar to fbc_header, but includes the js at the top of the page
     as well.  This is necessary becaue there does not appear to be a
     footer hook on the login page.
    */
    add_action('login_head', 'fbc_login_head');

    /* Includes any necessary js in the page, registers onload handlers,
       and prints the absolutely positioned "Welcome, username" div.
    */
    add_action('wp_footer', 'fbc_footer');

    /* Adds a "Connect" button to the login form, and handles fbconnect
      logout when a wordpress logout is performed.
    */
    add_action('login_form', 'fbc_login_form');

    /* Prints fbml with the user's profile pic when that user is a
      facebook connect user.
    */
    add_filter('get_avatar', 'fbc_get_avatar', 10, 4);

    /*
      Make sure the comment author info is in sync with the db.
    */
    add_filter('get_comment_author', 'fbc_get_comment_author');
    add_filter('get_comment_author_url', 'fbc_get_comment_author_url');

    /* Remove nofollow from links back to profile pages */
    if (get_option(FBC_REMOVE_NOFOLLOW_OPTION) === 'true') {
      add_filter('get_comment_author_link', 'fbc_remove_nofollow');
    }

    /* Add the xmlns:fb namespace to the page.  This is necessary to be
       stricty correct with xhtml, and more importantly it's necessary for
       xfbml to work in IE.
    */
    add_filter('language_attributes', 'fbc_language_attributes');

    /* Setup feedforms and post-specific data.
    */
    add_action('comment_form', 'fbc_comment_form_setup');

    /* Why do this? So you can print the form with
       do_action('fbc_comment_form') and not spew errors if the
       plugin is removed.
    */
    add_action('fbc_display_login_state', 'fbc_display_login_state');
    add_action('fbc_display_login_button', 'fbc_display_login_button');

  }

  /* Install the admin menu.
  */
  add_action('admin_menu', 'fbc_add_options_to_admin');

}


function fbc_add_options_to_admin() {
  if (function_exists('add_options_page')) {
    add_options_page('Facebook Connect',
                     'Facebook Connect',
                     8,
                     __FILE__,
                     'fbc_admin_options');
  }
}

function fbc_remove_nofollow($anchor) {
  global $comment;
  // Only remove for facebook comments, since url is trusted
  if ($comment->user_id && fbc_get_fbuid($comment->user_id)) {
    return preg_replace('/ rel=[\"\'](.*)nofollow(.*?)[\"\']/', ' rel="$1 $2" ', $anchor);
  }
  return $anchor;
}


function fbc_is_app_config_valid($api_key, $secret, &$error) {
   $facebook = new Facebook($api_key,
                             $secret,
                             false,
                             'connect.facebook.com');

  $api_client = $facebook->api_client;
  // The following line causes an error in the PHP admin console if
  // you are using php4.
  try { // ATTENTION: This plugin is not compatible with PHP4
    $api_client->feed_getRegisteredTemplateBundles();
    $success = true;
  } catch(Exception $e) {
    $success = false;
    $error = $e->getMessage();
  }

  return $success;
}

function fbc_get_comment_author_url($url) {
  global $comment;
  if ($comment->user_id) {
    $user = get_userdata($comment->user_id);
    if ($user) {
      return $user->user_url;
    } else {
      return null;
    }
  } else {
    return $url;
  }

}

function fbc_get_comment_author($author) {
  global $comment;

  // normally wordpress will not update the name of logged in comment
  // authors if they change it after the fact.  This hook makes sure
  // we use the most recent name when displaying comments.
  if ($comment->user_id) {
    $user = get_userdata($comment->user_id);
    if ($user) {
      return $user->display_name;
    } else {
      // This probably means the account was deleted.
      // Should it says something like "Unknown user" or "deleted account"?
      return _(FBC_ANONYMOUS_DISPLAYNAME);
    }
  } else {
    return $author;
  }
}

function fbc_clear_config() {
  update_option(FBC_APP_KEY_OPTION, null);
  update_option(FBC_APP_SECRET_OPTION, null);
  update_option(FBC_BUNDLE_OPTION, null);
}

function fbc_is_configured() {
  $app_key = get_option(FBC_APP_KEY_OPTION);
  $app_secret = get_option(FBC_APP_SECRET_OPTION);
  return !empty($app_key) && !empty($app_secret);
}

function fbc_language_attributes($output) {
  return $output . ' xmlns:fb="http://www.facebook.com/2008/fbml"';
}

/*
 * Generated and process the administrative options panel, for api key
 * and secret configuration.
 */
function fbc_admin_options() {

  $hidden_field_name = 'mt_submit_hidden';

  // Read in existing option value from database
  $app_key = get_option(FBC_APP_KEY_OPTION);
  $app_secret = get_option(FBC_APP_SECRET_OPTION);
  $remove_nofollow = get_option(FBC_REMOVE_NOFOLLOW_OPTION);
  if ($remove_nofollow === false) {
    // set default
    $remove_nofollow = 'true';
    update_option(FBC_REMOVE_NOFOLLOW_OPTION, $remove_nofollow);
  }

  // See if the user has posted us some information
  // If they did, this hidden field will be set to 'Y'
  if( $_POST[ $hidden_field_name ] == 'Y' ) {

      // Read their posted value
      $app_key = $_POST[FBC_APP_KEY_OPTION];
      $app_secret = $_POST[FBC_APP_SECRET_OPTION];
      $remove_nofollow = isset($_POST[FBC_REMOVE_NOFOLLOW_OPTION]) ? 'true' : 'false';

      $error = null;
      if (fbc_is_app_config_valid($app_key, $app_secret, $error)) {
        // Save the posted value in the database
        update_option(FBC_APP_KEY_OPTION, $app_key);
        update_option(FBC_APP_SECRET_OPTION, $app_secret);
        update_option(FBC_REMOVE_NOFOLLOW_OPTION, $remove_nofollow);

        $force_template_reload = isset($_POST['force_template_reload']);
        fbc_register_templates($force_template_reload);
        fbc_set_callback_url();

        echo fbc_update_message(__('Options saved.', 'mt_trans_domain' ));

      } else {
        echo fbc_update_message(__("Failed to set API Key.  Error: $error", 'mt_trans_domain' ));
      }

    }

    echo '<div class="wrap">';
    echo "<h2>" . __( 'Facebook Connect Plugin Options', 'mt_trans_domain' ) . "</h2>";
    $form_action = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
    echo <<<EOF
<div>
<br/>To use Facebook Connect you will first need to get a Facebook API Key:
<ol>
<li>Visit <a target="_blank" href="http://www.facebook.com/developers/createapp.php?version=new">the Facebook application registration page</a>.
<li>Enter a descriptive name for your blog in the "Application Name"
    field.  This will be seen by users when they sign up for your
    site.</li>
<li>Submit</li>
<li>Copy the displayed API Key and Secret into this form.</li>
<li>Recommended: Upload icon images on the app configuration page.  These images are seen as the icon in newsfeed stories and when the user is registering with your application</li>
</ol>
<hr/>
<form name="form1" method="post" action="$form_action">
EOF;

  echo fbc_tag_input('hidden', $hidden_field_name, 'Y');
  echo fbc_tag_p(__("API Key:", 'mt_trans_domain'),
                 fbc_tag_input('text', FBC_APP_KEY_OPTION, $app_key, 50));
  echo fbc_tag_p(__("Secret:", 'mt_trans_domain' ),
                 fbc_tag_input('text', FBC_APP_SECRET_OPTION, $app_secret, 50));

  echo fbc_tag_p(__('Force Reload of Template Bundle from config.php (resets ID):',
                 'mt_trans_domain'),
                 fbc_tag_input('checkbox', 'force_template_reload', 'true'));

  echo fbc_tag_p(__('Strip nofollow from Facebook comment author links:',
                 'mt_trans_domain'),
                 '<input type="checkbox" name="'.FBC_REMOVE_NOFOLLOW_OPTION .'" '.
                 ($remove_nofollow === 'true' ? 'checked' : '') .' value="true" />');

  echo fbc_tag_p(__('Last user data update:', 'mt_trans_domain'),
                 get_option(FBC_LAST_UPDATED_CACHE_OPTION));
  echo fbc_tag_p(__('Template Bundle ID:', 'mt_trans_domain'),
                 get_option(FBC_BUNDLE_OPTION));

?>
<hr />

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
</p>

</form>

</div>

<?php
}

function this_plugin_path() {
  $path = explode("/", dirname(__FILE__));
  return get_option('siteurl').'/'. PLUGINDIR .'/' . array_pop($path);
}

function fbc_require_static() {
  $plugin_dir = this_plugin_path() . '/';
  $css = $plugin_dir . 'fbconnect.css';

  $connect_js = $plugin_dir . 'fbconnect.js';

  $featureloader = 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php';

  echo  <<<EOF
<!-- fbc_header -->

<link type="text/css" rel="stylesheet"
 href="$css"></link>

<script
 src="$featureloader"
 type="text/javascript"></script>

<script src="$connect_js" type="text/javascript"></script>

<!-- end fbc_header -->
EOF;

}

function fbc_register_init($app_config='reload') {
  $plugin_dir = this_plugin_path() . '/';

  $bundle_id = get_option(FBC_BUNDLE_OPTION);
  $site_url = get_option('siteurl');

  $user = wp_get_current_user();

  $init = "FBConnect.init('%s', '%s', '%s', '%s', %d, FBConnect.appconfig_%s);";
  fbc_footer_register(sprintf($init,
                              get_option(FBC_APP_KEY_OPTION),
                              $plugin_dir,
                              $bundle_id,
                              $site_url,
                              $user->ID,
                              $app_config));

}

function fbc_header() {

  fbc_update_facebook_data();

  fbc_require_static();

  fbc_register_init();
}

function _fbc_flush_footer_js() {
  global $onloadJS;
  $onloadJS[] = '';
  $js = implode("\n", $onloadJS);
  $onloadJS = null;

  return <<<EOF
<script type="text/javascript">
$js
</script>
EOF;
}

function fbc_footer() {
  if (FBC_USER_PROFILE_WINDOW) {
    echo fbc_render_login_state();
  }

  echo _fbc_flush_footer_js();

  fbc_debug_info();
}

/*
 Generates the absolutely positioned box declaring who your are logged
 in as.
*/
function fbc_display_login_state() {
  echo fbc_render_login_state();
}

function fbc_login_form() {
  echo render_fbconnect_button('FBConnect.redirect_home()');

  if ($_GET['loggedout']) {
    /* Discussed in
     http://wiki.developers.facebook.com/index.php/Authenticating_Users_on_Facebook
   */

    // The 'none' config prevents unnessary reloads on logout
    fbc_register_init($appconfig='none');
    fbc_footer_register('FBConnect.logout();');

    echo _fbc_flush_footer_js();
  }

}

/*
 * Show debug info, if available.
 */
function fbc_debug_info() {
  if (!empty($GLOBALS['FBC_DEBUGINFO'])) {
    $dbg = $GLOBALS['FBC_DEBUGINFO'];
    echo <<<EOF
<pre>
$dbg
</pre>
EOF;
  }
}

function fbc_render_login_state() {

  $fbuid = fbc_get_fbconnect_user();
  if (!$fbuid) {
    echo '<!-- no logged in Facebook user -->';
    return; // don't display anything if not logged in
  }

  return sprintf('
<div id="fbc_profile" class="fbc_profile_header">
<div class="fbc_profile_pic">
<fb:profile-pic uid="%d" facebook-logo="true" size="square"></fb:profile-pic>
</div>
Welcome, <fb:userlink shownetwork="false" uid="%d"></fb:userlink>
<br/>
<a onclick="FBConnect.logout(); return false" href="#">Logout of Facebook</a>
</div>
', $fbuid, $fbuid);
}

function fbc_comment_form_setup() {

  if (fbc_get_fbconnect_user()) {

    $blogname = get_option('blogname');
    $article_title = ltrim(wp_title($sep='',$display=false,$seplocation=''));

    fbc_footer_register(sprintf("FBConnect.blog_name = '%s';",
                           addslashes($blogname)));
    fbc_footer_register(sprintf("FBConnect.article_title = '%s';",
                           addslashes($article_title)));

    fbc_footer_register("FBConnect.setup_feedform();");
  }
}

function fbc_display_login_button($hidden=false) {
  $user = wp_get_current_user();
  if($user->ID) {
    // For the moment disallow connecting existing accounts
    return;
  }

  if ($hidden) {
    $visibility = 'style="visibility:hidden"';
  } else {
    $visibility = '';
  }

  $site_url = get_option('siteurl');

  $button = render_fbconnect_button();
  echo <<<EOF
<div class="fbc_hide_on_login fbc_connect_button_area" $visibility  id="fbc_login">
<span><small>Connect with your Facebook Account</small></span> <br/> $button
</div>

EOF;
}


function fbc_get_fbuid($wpuid) {
  static $fbc_uidcache = null;
  if ($fbc_uidcache === null) {
    $fbc_uidcache = array();
  }

  if (isset($fbc_uidcache[$wpuid])) {
    return $fbc_uidcache[$wpuid];
  }

  if (!$wpuid) {
    $fbuid = 0;
  } else {
    $fbuid = get_usermeta($wpuid, 'fbuid');
  }

  return ($fbc_uidcache[$wpuid] = $fbuid);
}


function fbc_get_avatar($avatar, $id_or_email, $size, $default) {
  if (!is_object($id_or_email)) {
    return $avatar;
  }

  if ($fbuid = fbc_get_fbuid($id_or_email->user_id)) {
    return render_fb_profile_pic($fbuid);
  } else {
    return $avatar;
  }
}

function fbc_get_userinfo($wpuid) {
  $fbuid = fbc_get_fbuid($wpuid);
  if (!$fbuid) {
    return null;
  }

  $userinfo = fbc_api_client()->users_getInfo(array($fbuid),
                                              array('name'));

  return $userinfo[0];
}

function fbc_register_templates($force=false) {

  $bundle_id = get_option(FBC_BUNDLE_OPTION);
  if ($bundle_id && !$force) {
    return $bundle_id;
  }

  global $fbc_short_story_templates;
  global $fbc_one_line_stories;

  $bundle_id = fbc_api_client()->feed_registerTemplateBundle(
                 $fbc_one_line_stories,
                 $fbc_short_story_templates,
                 null,
                 null
               );

  update_option(FBC_BUNDLE_OPTION, "$bundle_id");

  return $bundle_id;

}

/*
 * Facebook will test the callback url to see that the
 * xd_receiver contains it. We'll automatically
 * set the callback url on the app so the user doesn't have to.
 */
function fbc_set_callback_url() {

  $api_client = $facebook->api_client;

  // the plugin path becomes the callback url
  // because it is the path for the xd_receiver
  $plugin_path = this_plugin_path() . '/';

  $ret = fbc_api_client()->admin_setAppProperties(array('callback_url' => $plugin_path));

  return $ret;
}


/*
 * Accumulates a list of javascript to be executed at the end of the
 * page.  Usage:
 * fbc_footer_register('some_javascript_function();');
 *
 */
function fbc_footer_register($js) {
  global $onloadJS;
  if (!$onloadJS) {
    $onloadJS = array();
  }
  $onloadJS[] = $js;
}


function fbc_update_facebook_data($force=false) {
  $last_cache_update = get_option(FBC_LAST_UPDATED_CACHE_OPTION);
  $delta = time() - $last_cache_update;
  if ($delta < 24*60*60 && !$force) {
    return;
  }

  update_option(FBC_LAST_UPDATED_CACHE_OPTION,
                time());

  global $wpdb;
  $sql = "SELECT user_id, meta_value FROM $wpdb->usermeta WHERE meta_key = 'fbuid'";
  $res = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);
  if (!$res) {
    return -1;
  }

  $fbuid_to_wpuid = array();
  foreach($res as $result) {
    $fbuid_to_wpuid[$result['meta_value']] = $result['user_id'];
  }

  try {
    $userinfo = fbc_anon_api_client()->users_getInfo(array_keys($fbuid_to_wpuid),
                                                array('name', 'profile_url'));
  } catch(Exception $e) {
    return -1;
  }

  $userinfo_by_fbuid = array();
  foreach($userinfo as $info) {

    $fbuid = $info['uid'];
    $wpuid = $fbuid_to_wpuid[$fbuid];
    $name = fbc_get_displayname($info);
    $url = fbc_make_public_url($info);

    $userdata = array('ID' => $wpuid,
                      'display_name' => $name,
                      'user_url' => $url);

    wp_update_user($userdata);
  }

  return count($userinfo);
}

function fbc_tag_p() {
  $args = func_get_args();
  $inner = implode("\n", $args);
  return "<p>\n$inner</p>\n";
}

function fbc_tag_input($type, $name, $value=null, $size=null) {

  $vals = array("type" => $type,
                "name" => $name);
  if ($value !== null) {
    $vals['value'] = $value;
  }

  if ($size !== null) {
    $vals['size'] = $size;
  }

  $inner = '';
  foreach($vals as $k => $v) {
    $inner .= sprintf("%s='%s' ", $k, $v);
  }

  return "<input $inner />";

}

function fbc_update_message($message) {
  return <<<EOF
<div class="updated"><p><strong>$message</strong></p></div>
EOF;
}

function fbc_login_head() {
  fbc_require_static();
}


// start it up.
fbc_init();

?>
