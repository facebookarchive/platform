
function /* class */ FBConnect(api_key, plugin_path,
                               template_bundle_id, home_url) {
    if (!api_key) {
        fbc_error("api_key is not set");
    }

    if (!plugin_path) {
        fbc_error("plugin path not provided");
    }

    this.home_url = home_url || "/";

    this.api_key = api_key;
    this.plugin_path = plugin_path;
    this.template_bundle_id = template_bundle_id;
    this.is_initialized = false;

    this.ensure_init(null);
}

FBConnect.prototype.ensure_init = function(callback) {

  outer_this = this;

  if(this.is_initialized) {
    if (callback) callback();
  } else {
    FB_RequireFeatures(["XFBML", "CanvasUtil"], function() {
        FB.FBDebug.LogLevel = 4;
        FB.Facebook.init(outer_this.api_key,
                         outer_this.plugin_path + "xd_receiver.php");
        outer_this.is_initialized = true;

        if (callback) callback();
      });
  }

}

/*
 * Simple Ajax call method.
 *
 * From http://en.wikipedia.org/wiki/XMLHttpRequest
 */
FBConnect.prototype.ajax = function(url, vars, callbackFunction) {
  var request =  new XMLHttpRequest();
  request.open("POST", url, true);
  request.setRequestHeader("Content-Type",
                           "application/x-www-form-urlencoded");

  request.onreadystatechange = function() {
      if (request.readyState == 4) {
        if (request.status == 200) {
            callbackFunction(request.responseText);
        } else if(request.status == 302) {

        }
      }
  };
  request.send(vars);
}

/*
 * "Session Ready" handler. This is called when the facebook
 * session becomes ready after the user clicks the "Facebook login" button.
 * In a more complex app, this could be used to do some in-page
 * replacements and avoid a full page refresh. For now, just
 * notify the server the user is logged in, and redirect to home.
 *
 */
FBConnect.prototype.facebook_session_is_ready = function(onsuccess) {
  var user = FB.Facebook.apiClient.get_session().uid;
  if (!user) {
    // probably should give some indication of failure to the user
    return;
  }

  // don't pass the user back raw to the server - it's too easy to spoof
  // better to just tell them that the session is ready, and let facebook
  // figure it out based on the cookies and signature
  fbconnect.ajax(fbconnect.plugin_path + 'save.php', 'save=1', function(text) {
          if(onsuccess) {
              onsuccess();
          }
      });
}

FBConnect.prototype.redirect_home = function() {
  window.location = fbconnect.home_url;
}


/*
 * Onclick handler for "facebook login" button. This will register
 * a handler for when the session becomes available, and then
 * start the process of getting a user's session (via popup, ajax
 * dialog, or just checking that they are already authenticated).
 *
 */
FBConnect.prototype.ensure_session = function(onsuccess) {

  var facebook_button = ge('facebook_button_loading');
  if(facebook_button) {
    facebook_button.style.visibility = "visible";
  }


  fbconnect.ensure_init(function() {
    if(facebook_button) {
      facebook_button.style.visibility = "hidden";
    }

    FB.Facebook.get_sessionState().waitUntilReady(function() {
      fbconnect.facebook_session_is_ready(onsuccess);
    });
    FB.Connect.requireSession();
  });
}



/*
 wordpress specific functions
 */


FBConnect.prototype.setup_feedform = function() {

  if (!fbconnect.template_bundle_id) {
      fbc_error("no template id provided");
      return ;
  }

  /* This is a bit of a hack.  The default theme gives the submit
     button an id of "submit".  This causes it to overwrite the
     .submit() function on the form.  The solution is to delete the
     submit button and recreate it with a different id.
     */
  var subbutton = ge("submit");
  fbc_remove(subbutton);
  subbutton = document.createElement("input");
  subbutton.setAttribute("id", "submitbutton");
  subbutton.setAttribute("type", "submit");
  ge('commentform').appendChild(subbutton);

  subbutton.onclick = function () {
    fbconnect.ensure_session(function () {
      fbconnect.show_comment_feedform();
    });
    return false;
  };
}

FBConnect.prototype.show_comment_feedform = function() {

  var template_data = {
                        'post-url': window.location.href,
                        'post-title': fbconnect.article_title,
                        'blog-name': fbconnect.blog_name,
                        'blog-url': fbconnect.home_url,
                        }
  var user = FB.Facebook.apiClient.get_session().uid;
  var body_general = fbc_body_general(user, ge('comment').value);
  FB.Connect.showFeedDialog(fbconnect.template_bundle_id,
                            template_data,
                            null, // template_ids
                            body_general,
                            null, // story_size
                            null, // require_connect
                            function() {
                              ge('commentform').submit();
                            });

  return false;

}

/*
 * Generates FBML for the body of a newsfeed story.  The story looks like:
 *
 * Sally wrote: "some insightful comment"
 */
function fbc_body_general(uid, comment) {
  var words = comment.split(' ');
  if (words.length > 50) {
    words = words.slice(0, 50);
    words.push('...');
  }
  var comment_clip = words.join(' ');
  return "<fb:pronoun capitalize=\'true\' useyou=\'false\' uid=\'" + uid +  "\'  /> wrote: \"" + comment_clip + "\"";
}

function fbc_insert_above_comment(e) {
  var sib = ge("comment").parentNode;
  sib.parentNode.insertBefore(e, sib);
  e.style.visibility = "";
}

function fbc_logged_in_cb() {
  fbc_remove(ge('fbc_login'));
  fbconnect.setup_feedform();
}

/* Depending on the theme being used this function may require
 tweaking to get the right behavior. Also see setup_feedform for
 similar issues.
 */
function fbc_logged_out_cb() {
    /*
      Remove existing comment text boxes
     */
    fbc_remove_parent(ge('author'));
    fbc_remove_parent(ge('email'));
    fbc_remove_parent(ge('url'));

    /*
      Replace the connect button with an indicator that the user is
      now logged in.
     */
    fbc_remove(ge('fbc_login'));
    var userlink = ge('fbc_userlink');

    // FIXME: why doesn't loggedinuser work?
    userlink.setAttribute("uid", FB.Facebook.apiClient.get_session().uid);
    FB.XFBML.Host.addElement(new FB.XFBML.UserLink(userlink));

    fbc_insert_above_comment(ge('fbc_logged_in'));

    fbconnect.setup_feedform();
}


function fbc_remove(e) {
  if(e)
    e.parentNode.removeChild(e);
}

function fbc_remove_parent(e) {
  if(e)
    fbc_remove(e.parentNode);
}

function fbc_error(e) {
    alert(e);
}

function ge(elem) {
  return document.getElementById(elem);
}
