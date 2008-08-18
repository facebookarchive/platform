
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
        FB.FBDebug.LogLevel = 4;
        FB.Facebook.init(window.api_key, "xd_receiver.php");
        window.is_initialized = true;
        callback();
      });
  }
}

function showDocAfterRender() {
  ensure_init(function() {
      FB.XFBML.Host.get_areElementsReady().waitUntilReady (show_document);
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
function facebook_session_is_ready(link_to_current_user) {
    var user = FB.Facebook.apiClient.get_session() ?
        FB.Facebook.apiClient.get_session().uid :
        null;

  if (!user) {
    // probably should give some indication of failure to the user
    return;
  }


  // don't pass the user back raw to the server - it's too easy to spoof
  // better to just tell them that the session is ready, and let facebook
  // figure it out based on the cookies and signature
  var params = 'save=1';

  if (link_to_current_user) {
    params += '&link_to_current_user=1';
  }

  ajax('ajax.php', params, function(text) {
      if (text > 0) {
        window.location = 'index.php';
      }
    });
}

/*
 * This will process the session when it becomes available.
 */
function facebook_session_on_ready(link_to_current_user) {
  ensure_init(function() {
      FB.Facebook.get_sessionState().waitUntilReady(function() {
          facebook_session_is_ready(link_to_current_user);
        });
    });
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
 * This function detects whether the user is logged into facebook but just
 * not connected, and shows the checkbox if that's true.
 *
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
