
/*
 * API key, this should be initialized before any another function in this file is called.
 */
var is_initialized = false;

/*
 * Ensure Facebook app is initialized and call callback afterward
 *
 */
function ensure_init(callback) {
  if(!window.api_key) {
    window.alert("api_key is not set");
  }

  if(window.is_initialized) {
    callback();
  } else {
    FB_RequireFeatures(["XFBML", "CanvasUtil"], function() {
        // xd_receiver.php is a relative path here, because The Run Around
        // could be installed in a subdirectory
        // you should prefer an absolute URL (like "/xd_receiver.php") for more accuracy
        FB.Facebook.init(window.api_key, "xd_receiver.php");

        window.is_initialized = true;
        callback();
      });
  }
}

/*
 * The facebook_onload statement is printed out in the PHP. If the user's logged in
 * status has changed since the last page load, then refresh the page to pick up
 * the change.
 *
 * This helps enforce the concept of "single sign on", so that if a user is signed into
 * Facebook when they visit your site, they will be automatically logged in -
 * without any need to click the login button.
 *
 * @param already_logged_into_facebook  reports whether the server thinks the user
 *                                      is logged in, based on their cookies
 *
 */
function facebook_onload(already_logged_into_facebook) {
  // user state is either: has a session, or does not.
  // if the state has changed, detect that and reload.
  ensure_init(function() {
      FB.Facebook.get_sessionState().waitUntilReady(function(session) {
          var is_now_logged_into_facebook = session ? true : false;

          // if the new state is the same as the old (i.e., nothing changed)
          // then do nothing
          if (is_now_logged_into_facebook == already_logged_into_facebook) {
            return;
          }

          // otherwise, refresh to pick up the state change
          refresh_page();
        });
    });
}

/*
 * "Session Ready" handler. This is called when the facebook
 * session becomes ready after the user clicks the "Facebook login" button.
 * In a more complex app, this could be used to do some in-page
 * replacements and avoid a full page refresh. For now, just
 * notify the server the user is logged in, and redirect to home.
 *
 * @param link_to_current_user  if the facebook session should be
 *                              linked to a currently logged in user, or used
 *                              to create a new account anyway
 */
function facebook_button_onclick() {

  ensure_init(function() {
      FB.Facebook.get_sessionState().waitUntilReady(function() {
          var user = FB.Facebook.apiClient.get_session() ?
            FB.Facebook.apiClient.get_session().uid :
            null;

          // probably should give some indication of failure to the user
          if (!user) {
            return;
          }

          // The Facebook Session has been set in the cookies,
          // which will be picked up by the server on the next page load
          // so refresh the page, and let all the account linking be
          // handled on the server side

          // This could be done a myriad of ways; for a page with more content,
          // you could do an ajax call for the account linking, and then
          // just replace content inline without a full page refresh.
          refresh_page();
        });
    });
}

/*
 * Do a page refresh after login state changes.
 * This is the easiest but not the only way to pick up changes.
 * If you have a small amount of Facebook-specific content on a large page,
 * then you could change it in Javascript without refresh.
 */
function refresh_page() {
  window.location = 'index.php';
}

/*
 * Prompts the user to grant a permission to the application.
 */
function facebook_prompt_permission(permission) {
  ensure_init(function() {
    FB.Connect.showPermissionDialog(permission);
  });
}

/*
 * Show the feed form. This would be typically called in response to the
 * onclick handler of a "Publish" button, or in the onload event after
 * the user submits a form with info that should be published.
 *
 */
function facebook_publish_feed_story(form_bundle_id, template_data) {
  // Load the feed form
  ensure_init(function() {
          FB.Connect.showFeedDialog(form_bundle_id, template_data);
          //FB.Connect.showFeedDialog(form_bundle_id, template_data, null, null, FB.FeedStorySize.shortStory, FB.RequireConnect.promptConnect);

      // hide the "Loading feed story ..." div
      ge('feed_loading').style.visibility = "hidden";
  });
}

/*
 * If a user is not connected, then the checkbox that says "Publish To Facebook"
 * is hidden in the "add run" form.
 *
 * This function detects whether the user is logged into facebook but just
 * not connected, and shows the checkbox if that's true.
 */
function facebook_show_feed_checkbox() {
  ensure_init(function() {
      FB.Connect.get_status().waitUntilReady(function(status) {
          if (status != FB.ConnectState.userNotLoggedIn) {
            // If the user is currently logged into Facebook, but has not
            // authorized the app, then go ahead and show them the feed dialog + upsell
            checkbox = ge('publish_fb_checkbox');
            if (checkbox) {
              checkbox.style.visibility = "visible";
            }
          }
        });
    });
}
