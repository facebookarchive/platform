<?php
/*
Plugin Name: Facebook Connect
Author: Adam Hupp
Author URI: http://hupp.org/adam/
Description: Integrate Facebook and Wordpress with Facebook Connect.  Provides single-signon, avatars, and newsfeed comment publication.  Requires a <a href="http://www.facebook.com/developers/">Facebook API Key</a> for use.
Version: 0.9
*/

require_once('common.php');
require_once('storytemplate.php');

define('FBC_APP_KEY_OPTION', 'fbc_app_key_option');
define('FBC_APP_SECRET_OPTION', 'fbc_app_secret_option');
define('FBC_BUNDLE_OPTION', 'fbc_template_bundle_id');
define('FBC_LAST_UPDATED_CACHE_OPTION', 'fbc_last_updated_cache_option');


function fbc_add_options_to_admin() {
  if (function_exists('add_options_page')) {
    add_options_page('Facebook Connect',
                     'Facebook Connect',
                     8,
                     __FILE__,
                     'fbc_admin_options');
  }
}

function fbc_is_app_config_valid($api_key, $secret, &$error) {
   $facebook = new Facebook($api_key,
                             $secret,
                             false,
                             'connect.facebook.com');
  $sucess = false;
  try {
    $facebook->api_client->feed_getRegisteredTemplateBundles();
    $sucess = true;
  } catch(Exception $e) {
    $error = $e->getMessage();
  }
  return $sucess;
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

/*
 * Generated and process the administrative options panel, for api key
 * and secret configuration.
 */
function fbc_admin_options() {

  $hidden_field_name = 'mt_submit_hidden';

  // Read in existing option value from database
  $app_key = get_option(FBC_APP_KEY_OPTION);
  $app_secret = get_option(FBC_APP_SECRET_OPTION);

  // See if the user has posted us some information
  // If they did, this hidden field will be set to 'Y'
  if( $_POST[ $hidden_field_name ] == 'Y' ) {
      // Read their posted value
      $app_key = $_POST[FBC_APP_KEY_OPTION];
      $app_secret = $_POST[FBC_APP_SECRET_OPTION];

      $error = null;
      if (fbc_is_app_config_valid($app_key, $app_secret, $error)) {
        // Save the posted value in the database
        update_option(FBC_APP_KEY_OPTION, $app_key);
        update_option(FBC_APP_SECRET_OPTION, $app_secret);

        fbc_register_templates();

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
<li>Visit <a target="_blank" href="http://www.new.facebook.com/developers/apps.php">the Facebook application registration page</a>.
<li>Select "Apply for a Registration Key"</li>
<li>Enter a descriptive name for your blog in the "Application Name" field.  This will be seen by users when they sign up for your site.</li>
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

function fbc_header() {
  $plugin_dir = this_plugin_path() . '/';
  $connect_js = $plugin_dir . 'fbconnect.js';
  $css = $plugin_dir . 'fbconnect.css';
  echo  <<<EOF
<!-- fbc_header -->

<link type="text/css" rel="stylesheet"
 href="http://static.ak.connect.facebook.com/css/fb_connect.css"></link>

<link type="text/css" rel="stylesheet"
 href="$css"></link>

<script
 src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php"
 type="text/javascript"></script>

<script src="$connect_js" type="text/javascript"></script>

<!-- end fbc_header -->

EOF;

  $site_url = get_option('siteurl');
  $blogname = get_option('blogname');
  $article_title = ltrim(wp_title($sep='',$display=false,$seplocation=''));
  $bundle_id = get_option(FBC_BUNDLE_OPTION);

  onloadRegister(sprintf("window.fbconnect = new FBConnect('%s', '%s', '%s', '%s');",
                         get_option(FBC_APP_KEY_OPTION),
                         $plugin_dir,
                         $bundle_id,
                         $site_url));
  onloadRegister(sprintf("fbconnect.blog_name = '%s'",
                         addslashes($blogname)));
  onloadRegister(sprintf("fbconnect.article_title = '%s'",
                         addslashes($article_title)));

}

function fbc_footer() {
  global $onloadJS;
  $onloads = implode("\n", $onloadJS);
  $onloadJS = null;

  echo <<<EOF
<script
  type="text/javascript">
$onloads;
</script>
EOF;

}

function fbc_login_form() {
  return render_fbconnect_button('fbconnect.redirect_home');
}


function fbc_comment_form() {
  if (!empty($GLOBALS['FBC_DEBUGINFO'])) {
    $dbg = $GLOBALS['FBC_DEBUGINFO'];
    echo <<<EOF
<pre>
$dbg
</pre>
EOF;
  }

  $user = wp_get_current_user();
  if ($user->id && fbc_get_fbuid($user->id)) {
    /*
     Already logged in users don't get the connect button.
    */
    onloadRegister("fbconnect.setup_feedform();");
    return;
  } else if($user->id) {
    // For the moment disallow connecting existing accounts
    return;
  }

  $site_url = get_option('siteurl');

  // TODO: different look for unconnect users
  $callback = 'fbc_logged_out_cb';
  $button = render_fbconnect_button($callback, 'medium');
  echo <<<EOF
<div class="fbc_connect_button_area" style="visibility:hidden" id="fbc_login">
<span>Connect with your Facebook Account</span> <br/> $button
</div>

<div style="visibility:hidden" id="fbc_logged_in">
Logged in as: <span id="fbc_userlink" uid="loggedinuser" shownetwork="false"></span> <a href="$site_url/wp-login.php?action=logout">Log out &raquo;</a>
</div>

EOF;

  onloadRegister("fbc_insert_above_comment(ge('fbc_login'));");
}

function fbc_get_fbuid($wpuid) {
  if (!$wpuid) {
    return 0;
  } else {
    return get_usermeta($wpuid, 'fbuid');
  }
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

function fbc_register_templates() {

  $bundle_id = get_option(FBC_BUNDLE_OPTION);
  if ($bundle_id) {
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
 * Accumulates a list of javascript to be executed once
 * the page is loaded.  Usage:
 * onloadRegister('some_javascript_function();');
 *
 */
function onloadRegister($js) {
  global $onloadJS;
  if (!$onloadJS) {
    $onloadJS = array();
  }
  $onloadJS[] = $js;
}

// Just a hook to run once per page, doesn't use posts
function fbc_post_prequery($posts) {
  $last_cache_update = get_option(FBC_LAST_UPDATED_CACHE_OPTION);
  $delta = time() - $last_cache_update;
  if ($delta > 24*60*60) {
    update_option(FBC_LAST_UPDATED_CACHE_OPTION,
                  time());

    update_facebook_data();
  }
  return $posts;
}


function update_facebook_data() {
  global $wpdb;
  $sql = "SELECT user_id, meta_value FROM $wpdb->usermeta WHERE meta_key = 'fbuid'";
  $res = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);
  if (!$res) {
    return;
  }

  $fbuid_to_wpuid = array();
  foreach($res as $result) {
    $fbuid_to_wpuid[$result['meta_value']] = $result['user_id'];
  }

  $userinfo = fbc_api_client()->users_getInfo(array_keys($fbuid_to_wpuid),
                                              array('name'));

  $userinfo_by_fbuid = array();
  foreach($userinfo as $info) {
    $fbuid = $info['uid'];
    $wpuid = $fbuid_to_wpuid[$fbuid];
    $name = $info['name'];
    $url = fbc_make_public_url($info);
    fbc_update_user_info($wpuid, $name, $url);
  }

}

function fbc_update_user_info($wpuid, $name, $url) {
    $userdata = array('ID' => $wpuid,
                      'display_name' => $name,
                      'user_url' => $url);
    wp_update_user($userdata);
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

if (fbc_is_configured()) {
    add_action('wp_head', 'fbc_header');
    add_action('login_head', 'fbc_header');
    add_action('wp_footer', 'fbc_footer');
    add_action('comment_form', 'fbc_comment_form');
    add_action('login_form', 'fbc_login_form');
    add_filter('get_avatar', 'fbc_get_avatar', 10, 4);
    add_filter('the_posts', 'fbc_post_prequery');
}

add_action('admin_menu', 'fbc_add_options_to_admin');


?>
