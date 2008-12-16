
FBConnect = {

  init : function(api_key, plugin_path,
                  template_bundle_id, home_url,
                  wp_user, app_config) {

    if (!api_key) {
        FBConnect.error("api_key is not set");
    }

    if (!plugin_path) {
        FBConnect.error("plugin path not provided");
    }

    FBConnect.home_url = home_url || "/";

    FBConnect.plugin_path = plugin_path;
    FBConnect.template_bundle_id = template_bundle_id;
    FBConnect.wp_user = wp_user;

    FB.init(api_key, plugin_path + "xd_receiver.php", app_config);

  },

  appconfig_reload : {
    reloadIfSessionStateChanged: true
  },

  appconfig_none : {},

  appconfig_ajaxy : {
    ifUserConnected : fbc_onlogin_noauto,
    ifUserNotConnected : fbc_onlogout_noauto
  },

  logout : function() {
    FB.ensureInit(function() {
       FB.Connect.logout();
    });
  },

  redirect_home : function() {
    window.location = FBConnect.home_url;
  },

  /*
   wordpress specific functions
   */
  setup_feedform : function() {

    if (!FBConnect.template_bundle_id) {
      FBConnect.error("no template id provided");
      return;
    }

    /* This is a bit of a hack.  The default theme gives the submit
       button an id of "submit".  This causes it to overwrite the
       .submit() function on the form.  The solution is to delete the
       submit button and recreate it with a different id.
       */
    var orig_submit = ge("submit");
    var comment_form = ge('commentform');

    subbutton = document.createElement("input");
    subbutton.setAttribute('name', 'fbc-submit-hack');
    subbutton.setAttribute("type", "submit");
    comment_form.appendChild(subbutton);

    orig_submit.parentNode.replaceChild(subbutton, orig_submit);

    subbutton.onclick = function () {
      FBConnect.show_comment_feedform();
      return false;
    };
  },

  show_comment_feedform : function() {

    var template_data = {
        'post-url': window.location.href,
        'post-title': FBConnect.article_title,
        'blog-name': FBConnect.blog_name,
        'blog-url': FBConnect.home_url
    };

    var comment_text = '';
    var comment = ge('comment');
    if (comment) {
      comment_text = comment.value;
    }

    if (comment_text.trim().length === 0) {
      return false;
    }

    var body_general = FBConnect.make_body_general(comment_text);

    FB.Connect.showFeedDialog(FBConnect.template_bundle_id,
                              template_data,
                              null, // template_ids
                              body_general,
                              null, // story_size
                              FB.RequireConnect.promptConnect, // require_connect
                              function() {
                                ge('commentform').submit();
                              });
    return false;

  },

  /*
   * Generates FBML for the body of a newsfeed story.  The story looks like:
   *
   * Sally wrote: "some insightful comment"
   */
  make_body_general : function(comment) {
    var words = comment.split(' ');
    if (words.length > 50) {
      words = words.slice(0, 50);
      words.push('...');
    }
    var comment_clip = words.join(' ');
    return "<fb:pronoun capitalize=\'true\' useyou=\'false\' uid=\'actor\' /> wrote: \"" + comment_clip + "\"";
  },

  error : function() {
    FB.FBDebug.writeLine.call(arguments);
  },

  log : function() {
    FB.FBDebug.writeLine.call(arguments);
  }
};

// end FBConnect

function fbc_onlogout_noauto() {
  fbc_set_visibility_by_class('fbc_hide_on_login', '');
  fbc_set_visibility_by_class('fbc_hide_on_logout', 'none');
  // TODO: feedform disable
}


function fbc_onlogin_noauto() {

  fbc_set_visibility_by_class('fbc_hide_on_login', 'none');
  fbc_set_visibility_by_class('fbc_hide_on_logout', '');

//   fbc_remove(ge('fbc_login'));

//   var userlink = ge('fbc_userlink');
//   if (userlink) {
//     userlink.setAttribute('uid', FB.Facebook.apiClient.get_session().uid);
//     userlink.setAttribute('usenetwork', 'false');
//     FB.XFBML.Host.addElement(new FB.XFBML.UserLink(userlink));
//   } // if false, probably already happened

//   ge('fbc_logged_in').style.visibility = '';

  fbconnect.setup_feedform();
}

function fbc_set_visibility_by_class(cls, vis) {
  var res = document.getElementsByClassName(cls);
  for(var i = 0; i < res.length; ++i) {
    res[i].style.visibility = vis;
  }
}


function ge(elem) {
  return document.getElementById(elem);
}

