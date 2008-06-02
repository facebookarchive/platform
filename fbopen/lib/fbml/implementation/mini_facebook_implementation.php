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

/**
 * Sample implementation of FBML, where this particular
 * implementation supports a very small subset of HTML and
 * an equally small subset of the traditional FBML tag set
 * currently supported by the true Facebook Developer Platform.
 */

class MiniFacebookImplementation extends FBMLImplementation {

  /**
   * Constructs an instance of the MiniFacebookPlatformImplementation,
   * using the specified flavor.  There are no specific configuration
   * needs at this level that aren't already handled at the base
   * class level, so the implementation here is just a wrapper around
   * a call to the parent constructor.
   *
   * @flavor the FBMLFlavor instance that knows precisely which FBML
   *         flags are permitted and which flags are forbidden for
   *         a particular rendering.
   */

  public function __construct($flavor)
  {
    parent::__construct($flavor);
  }

  /**
   * Uses PHP reflection to figure out what FBML tags
   * this implementation supports.  Because methods designed
   * to render FBML nodes follow a strict naming convention, it's
   * easy to compile a list of all methods defined by the class,
   * and based on the naming convention, reverse engineer the
   * set of FBML tag types we support.  So, if this class defined,
   * among other things, methods named "fb_name" and "fb_is_user"
   * and no other methods prefixed by "fb_", then it's clear there's
   * support for just the two FBML tags "fb:name" and "fb:is-user".
   *
   * @return an array of all those FBML tags supported by the receiving
   *         FBMLImplementation.
   */

  public function get_all_fb_tag_names()
  {
    $all_methods = get_class_methods($this);
    $macros = array();

    foreach ($all_methods as $name) {
      if (string_starts_with(strtolower($name), 'fb_')) {
        $macros[] = self::transform_macro_method_to_tag($name);
      }
    }

    return $macros;
  }

  /**
   * Method designed to identify all of those HTML
   * and FBML tags that have precaching needs--that is,
   * that work should be done on behalf of those tags
   * before compilation advances to the rendering phase.
   *
   * The simplest example of a tag that might have precaching
   * needs is <fb:name>, which in practice may need to load user profile
   * information before it can convert an id to a name.  We could
   * just wait until rendering time to do the lookup, sequentially
   * engaging the databases of user information as we need to compile
   * <fb:name> tags to the equivalent HTML.  Or, we could make
   * a few passes over the FBML tree, using the earlier passes to
   * asynchronously request the data be loaded so it's immediately
   * accessible during the actual rendering.  That's the
   * basic idea.
   *
   * As with get_all_fb_tag_names, we constrain that all precaching
   * methods begin with 'batch_precache", and additionally methods
   * begin with 'batch_precache_fb' if the node type is an FBML
   * one.  This method uses the same introspection techniques to
   * figure out which tags have precaching needs.
   *
   * @return a list of all those HTML and FBML tags requiring that
   *         precaching functionality be applied to them.
   */

  // FBOPEN:NOTE - no precaching implemented in open source version.
  // This requires a database for storing pre-sanitized developer content.
  public function get_precache_tags()
  {
    $all_methods = get_class_methods($this);
    $precache_tags = array();

    foreach ($all_methods as $name) {
      $n = strtolower($name);
      if (string_starts_with($n, 'batch_precache_fb')) {
        $start = strpos($n, '_');
        $tag = substr($n, $start + 13); // 13 characters ahead of first '-'
        $precache_tags[] = "fb:" . $tag;
      } else if (string_starts_with($n, 'batch_precache')) {
        $start = strpos($n, '_');
        $precache_tags[] = substr($n,$start+10);
      }
    }

    return $precache_tags;
  }

  /**
   * Uses PHP reflection to figure out which traditional HTML tags
   * require special rendering that's handled by this implementation.
   * Most HTML tags are rendered as you'd expect--that is, and FBML
   * node surround most HTML tags get rendered as:
   *
   *    <[tag-name] [name-1]="[value-1]" [name-2]="[value-2]" >
   *
   * But some HTML nodes--those with links to external URLs, for example--
   * might have special rendering needs.  An FBML implementation can
   * override the traditional rendering functionality for a tag by providing
   * one-argument method called "tag_[tag-name]".  That's a statement that
   * the FBML implementation is prepared to execute these methods on behalf
   * of these special HTML nodes.
   *
   * As with get_all_fb_tag_names, we compile a full list of all the methods,
   * search for those beginning with "tag_", and then reverse engineer the
   * name of the HTML tag type that requires special rendering.
   *
   * @return an array of all of the HTML tags which this FBML handles.  All HTML
   *         tag types not included in this list are assumed to be traditional
   *         HTML tags that can be rendered in the traditional manner.
   */

  public function get_special_html_tags()
  {
    $all_methods = get_class_methods($this);

    $html_special= array();
    foreach ($all_methods as $name) {
      $n = strtolower($name);
      if ( string_starts_with($n, 'tag_') ||
          string_starts_with($n, 'open_') ) {
        $start = strpos($n,'_');
        $tag = substr($n,$start+1);
        $html_special[$tag] = $tag;
      }
    }

    return $html_special;
  }

  /**
   * Method that knows precisely how to produce the name of the
   * rendering method that should be called on behalf of an
   * officially recognized FBML tag name.
   *
   * @param an FBML tag type/name, expressed as a string.
   * @return the name of the rendering method within this class that should
   *         be invoked on behalf of all FBML nodes with this tag name/type.
   */

  public function macro_method($tag_name)
  {
    return 'fb_' . str_replace('-', '_', substr($tag_name, 3));
  }

  /**
   * Placeholder method that gets invoked on behalf of any <fb:name> tag
   * in the FBML tree.  Support for the fb:name tag requires this class
   * to provide an fb_name method, so that the render_fb_node callback
   * function contained in fbml_node.php has something to
   * call back into.
   *
   * @param node the FBMLNode of type "fb:name".
   * @return the PHP string of the HTML that should substitute the <fb:name>
   *         tag.
   */

  public function fb_name($node)
  {
    $this->check('names');
    $uid = $node->attr_int('uid', 0, true); // true says it's required
    switch ($uid) {
      case 4: return '<b>Test User M</b>';
      case 1160: return '<b>Test User C</b>';
      case 8055: return '<b>Test User D</b>';
      case 214707: return '<b>Test User J</b>';
      case 1240077: return '<b>Test User 1</b>';
      case 1240078: return '<b>Test User 2</b>';
      default: return '<b>Unknown</b>';
    }
  }

  /**
   * Rendering functionality associated with the <fb:user> tag,
   * which manages FBML content that should be visible only to
   * those users who have permission to see it.  For instance,
   * the following:
   *
   *   <fb:user uid="214707">
   *     Here's some content that only those with permission to
   *     see user 214707 can see.
   *   </fb:user>
   *
   * should render to absolutely nothing if the viewer can't
   * see user 214707.
   *
   * @param node the FBMLNode wrapping the <fb:user> FBML node.
   * @return the empty string if the viewer doesn't have access to
   *         the user's information, or the full rendering of all of the
   *         children if the viewer does.
   * @exception thrown if the uid of the person viewing the rendering is
   *            isn't included in the implementation environment.
   */

  public function fb_user($node)
  {
    if ($this->is_logged_out())
      return $this->render_else($node);

    $viewing_user_id = $this->get_env('user');
    $protected_user_id = $node->attr_int('uid', 0, true); // uid of user we're protecting
    $visible = ($viewing_user_id == 4) || ($protected_user_id != 4);
    return $this->render_if($node, $visible);
  }

  /**
   * Rendering functionality associated with the <fb:pronoun> tag.  This particular
   * tag requires just one attribute be present, and in most cases compiles to
   * either "he", "she", or "they", depending on the gender (male, female, and unspecified)
   * of the identified user.  There are several optional attributes that can be used
   * to return variations on the "he", "she", and "they" that would otherwise be returned.
   * Those attributes are:
   *
   *   useyou: Boolean value which, if true, prompts the <fb:pronoun> tag to compile to
   *           "you" whenever the viewer and the supplied UID are the same person.  This
   *           attribute defaults to true.
   *   possessive: Boolean value which, if true, prompts the <fb:pronoun> tag to compile
   *               to "his", "her", "your", or "their" instead of the standard "he", "she",
   *               "you", or "they".  This attribute defaults to false.
   *   reflexive: Boolean value which, if true, compiles the <fb:pronoun> tag to "himself"
   *              "herself", "yourself", or "themself".  This particular attribute
   *              is optional, and defaults to false.  This particular implementation
   *              ignores the $reflexive param if the possessive attribute is explicitly
   *              set to true.
   *   objective: Boolean value which, if true, compiles the <fb:pronoun> tag to "him", "her",
   *              "you", and "them" instead of the standard "he", "she", "you", or "they".
   *              The attribute is optional, defaults to false, and this particular
   *              implementation igonres $objective if either the possessive attribute
   *              or the reflexive attribute is set.
   *   usethey: Boolean value which, if true, allows <fb:pronoun> to compile to "they", "them"
   *            "themself", or "their" if the gender of the specified user is unknown.
   *            This particular attribute defaults to true, but this particular implementation
   *            ignores it.
   *   capitalize: Boolean value which, if true, prompts compilation to capitalize the generated
   *               text.  "they" becomes "They", "she" becomes "She", "your" becomes "Your", and
   *               so forth.  This attribute is optional and defaults to false.
   *
   * @param node the FBML node modelling the <fb:pronoun> tag.
   * @return the pronoun that results from the rendering of the node.
   */

  public function fb_pronoun($node)
  {
    $viewer = $this->get_env('user');
    $uid = $node->$attr_int('uid', 0, true); // the uid for whom we're generating the pronoun
    $useyou = $node->attr_bool('useyou', null, true);
    $possessive = $node->attr_bool('possessive', null, false);
    $reflexive = $node->attr_bool('reflexive', null, false);
    $objective = $node->attr_bool('objective', null, false);
    $usethey = $node->attr_bool('usethey', null, true);
    $capitalize = $node->attr_bool('capitalize', null, false);

    $base_pronoun = ($useyou && ($viewer == $uid)) ? "you" : "he"; // all three users in the sample happen to be men
    $pronoun = $base_pronoun;
    if ($possessive) {
      $pronoun = ($base_pronoun == "he") ? "him" : "your";
    } else if ($reflexive) {
      $pronoun = ($base_pronoun == "he") ? "himself": "yourself";
    } else if ($objective) {
      $pronoun = ($base_pronoun == "he") ? "him" : "you";
    }

    // $usethey is irrelevant for this mock implementation, since the
    // supported users (Test User 1, Test User 2, Test User M, Test User C, Test User D, and Test User J) are all men.

    if ($capitalize) $pronoun = ucwords($pronoun);
    return $pronoun;
  }

  /**
   * Compiles an <fb:profile-pic> tag to the corresponding
   * img tag with a src attribute leading to the specified user's
   * picture.  There's one required attribute, which is the user id
   * of the profile or Facebook Page for the picture you want
   * to be displayed.  The tag can be thought of as a variation on
   * the standard img tag in the sense that valid img attributes
   * are also value fb:profile-pic attributes (except that the
   * 'src' attribute is ignored.)
   *
   * The original fb:profile-pic tag can be decorated with
   * a few additional attributes, and they are:
   *
   *   size: string value identifying the size of the image.  The
   *         attribute is optional and defaults to 'thumb'.  Legitimate
   *         values are thumb (scaled to be 50px wide), small (scaled to
   *         be 100px wide), normal (scaled to be 200px wide), and
   *         square (clipped/scaled to be 50px by 50px.
   *   linked: Boolean attribute which, if true, sets the img to
   *           be a link to the user's profile.  (This particular
   *           implementation ignores this.)
   *
   * @param node the FBMLNode modeling the <fb:profile-pic> tag.
   * @return the HTML form of the corresponding img tag.
   */

  public function fb_profile_pic($node)
  {
    $this->check('images');

    $uid = $node->attr_int('uid', 0, true);
    $size = $node->attr('size', "thumb");
    $linked = $node->attr_bool('linked', true, false);
    $img_src = "http://photos-160.ll.facebook.com/photos-ll-sf2p/v198/170/3/1160/n1160_33939823_1080.jpg";
    // above line is placeholder, and of course would involve the values of $uid and $size.
    $attrs = array();

    // here's where other attributes would be folded into the attrs associative
    // array, all before the following line

    $attrs['src'] = $img_src;
    return $this->_html_renderer->render_html_singleton_tag('img', $attrs);
  }

  /**
   * Compiles the <fb:if-can-see> tag, modeled by the supplied
   * $node, so that its children are rendered if and only if
   * the logged in user can see the 'what' attribute of the specified
   * user.  The "uid" attribute is absolutely required, but the
   * "what" attribute defaults to "search".  The "what" attribute
   * can take of one of several values, which currently are:
   *
   *    search, profile, friends, not_limited, online, statusupdates, wall,
   *    groups, courses, photosofme, notes, feed, contact, email, aim,
   *    cell, phone, mailbox, address, basic, education, professional,
   *    personal, seasonal
   *
   * This particular implementation is a placeholder that renders the children
   * under the jurisdiction of the <fb:if-can-see> tag if the what attribute
   * is set (or defaults) to one of the legal values *and* the user is one of the
   * first 2000 users to join Facebook.
   *
   * @param node the FBMLNode modeling the <fb:if-can-see> tag.
   * @return a string of HTML that is the rendering of the node's children if the specified
   *         logged in user can in fact see the specified what content of the specified user, or
   *         the rendering of just the top-level <fb:else> children otherwise (unless that
   *         what attribute isn't recognized, in which case we just return '')
   */

  public function fb_if_can_see($node)
  {
    $uid = $node->attr_int('uid', 0, true);
    $what = $node->attr_raw('what', 'search');
    $legal_what_values = array('search' => true, 'profile' => true, 'friends' => true,
                               'not_limited' => true, 'online' => true, 'statusupdates' => true,
                               'wall' => true, 'groups' => true, 'courses' => true,
                               'photosofme' => true, 'notes' => true, 'feed' => true,
                               'contact' => true, 'email' => true, 'aim' => true,
                               'cell' => true, 'phone' => true, 'mailbox' => true,
                               'address' => true, 'basic' => true, 'education' => true, 'professional' => true,
                               'personal' => true, 'seasonal' => true);
    if (!isset($legal_what_values[$what])) return '';
    return $this->render_if($node, $uid <= 2000); // handles the else case for us
  }

  /**
   * Compiles the <fb:if-is-app-user> tag, modeled by the specified node so
   * that its children are rendered and the corresponding HTML returns if and only
   * if the specified user has accepted the terms of service of the application
   * producing the FBML.  The only supported attribute is 'uid', which is optional
   * and defaults to 'loggedinuser' if nothing is specified.
   *
   * The placeholder implementation below contrives logic that states that only
   * users with a uid that ends in 60 have added the app.
   *
   * @param node the FBMLNode modeling the <fb:if-is-app-user> tag.
   * @return the full rendering of all of the <fb:if-is-app-user>'s children if (and
   *         only if) the specified user has accepted the terms of service of the
   *         application serving the FBML, or the rendering of any <fb:else> children
   *         otherwise.
   */

  public function fb_if_is_app_user($node)
  {
    if ($this->is_logged_out())
      return $this->render_else($node);

    $uid = $node->attr_id('uid', $this, false, 'loggedinuser');
    $show = ($uid % 100) == 60; // placeholder, really determines whether or not the user has accepted the app's TOS.
    return $this->render_if($node, $show);
  }

  /**
   * Generates HTML for the enclosed content whenever the logged in user and the
   * specified user are friends.  There are two optional attributes
   *
   *   uid: the user id of the person to check.  This defaults to 'profileowner' if
   *        it isn't explicitly specified.
   *   includeself: Boolean value stating whether or not friendship is reflexive (and
   *                this defaults to true.)
   *
   * @param node the FBML node modeling the <fb:if-is-friends-with-viewer> tag.
   * @return the HTML rendering of the node's children if the logged in user and
   *         the specified user are friends, or just the node's <fb:else> children
   *         otherwise.
   */

  public function fb_if_is_friends_with_user($node)
  {
    if ($this->is_logged_out()) {
      return $this->render_else($node);
    }

    $viewer = $this->get_env('user');
    $uid = $node->attr_id('uid', $this, false, 'profileowner');

    if ($viewer == $uid) {
      $show = $node->attr_bool('includeself', true);
    } else {
      $show = true; // placeholder for more complex application logic
    }

    return $this->render_if($node, $show);
  }

  /**
   * Rendering functionality for the <fb:if-is-user> tag, which controls
   * whether or not the children are rendered.  The one supported attribute
   * named "uid" is required, and should be attached to a comma-delimited
   * list of UIDs permitted to see the controlled content.
   *
   * @param node the root of some FBML subtree.
   * @return a string of HTML that's been rendered on behalf of the
   *         specified <fb:if-is-user> node.
   */

  public function fb_if_is_user($node)
  {
    if ($this->is_logged_out())
      return $this->render_else($node);

    $viewer = $this->get_env('user');
    $user_ids = $node->attr_ids('uid', $this, true);
    $show = isset($user_ids[$viewer]);
    return $this->render_if($node, $show);
  }

  /**
   * Compiles the <fb:if-user-has-added-app> tag modeled by
   * node and returns whatever it renders to.  The placeholder
   * implementation here assumes that anyone with an even-numbered
   * UIDs has installed all apps, and those with odd UIDs haven't
   * installed any.
   *
   * @param node the FBMLNode modeling the <fb:if-user-has-added-app>
   *             tag being compiled.
   * @return the full rendering of the <fb:if-user-has-added-app> tag
   *         modeled by the specified FBMLNode.
   */

  public function fb_if_user_has_added_app($node)
  {
    if ($this->is_logged_out())
      return $this->render_else($node);

    $user = $node->attr_id('uid', $this, false, 'loggedinuser');
    $show = $uid % 2 == 0; // placeholder, really determines whether or not the user has accepted the app's TOS.
    return $this->render_if($node, $show);
  }

  /**
   * Manages the rendering of all of <fb:narrow>'s children.  If (and
   * only if) a position environment variable exists and is set to "narrow"
   * do we render the children.  Otherwise, we just return the empty string.
   *
   * @param node the FBMLNode modeling the <fb:narrow> tag in question.
   * @return the full rendering of all the incoming <fb:narrow> tag's children (if
   *         the "position" environment variable is present and equal to "narrow"),
   *         or the empty string (when the "position" environment variable is
   *         missing, or present but not equal to "narrow").
   */

  public function fb_narrow($node)
  {
    if ($this->get_env("position", false) == "narrow")
      return $this->render_children($node);
    return "";
  }

  /**
   * Manages the rendering of all of <fb:wide>'s children.  If (and
   * only if) a position environment variable exists and is set to "wide"
   * do we render the children.  Otherwise, we just return the empty string.
   *
   * @param node the FBMLNode modeling the <fb:wide> tag in question.
   * @return the full rendering of all the incoming <fb:wide> tag's children (if
   *         the "position" environment variable is present and equal to "wide"),
   *         or the empty string (when the "position" environment variable is
   *         missing, or present but not equal to "wide").
   */

  public function fb_wide($node)
  {
    if ($this->get_env("position", false) == "wide")
      return $this->render_children($node);
    return "";
  }

  /**
   * Manages the compilation and rendering of everything rooted
   * at a <fb:visible-to-owner> tag.  All children are rendered, but
   * span tags and CSS visibility attribute are used to hide and show
   * certain sections, depending on whether or not the person viewing
   * the profile is the profile owner.
   *
   * @param node the FBMLNode modeling the <fb:visible-to-owner> tag.
   * @return the full rendering of all the tag's children, with visibility
   *         of certain sections decorared with hide and show, depending on
   *         whether or not the user is the same as the profile owner.
   */

  public function fb_visible_to_owner($node)
  {
    $this->_flavor->check('visible_to');

    if ($this->is_logged_out())
      return $this->render_visible_to($node, false);

    $profile = $this->get_env('profile');
    $user = $this->get_env('user');
    return $this->render_visible_to($node, $this->can_edit_profile($user, $profile));
  }

  /**
   * Renders the children hanging from <fb:visible-to-user> and displays.  The
   * rendering of all children is included in the return value, but CSS and its visibility
   * attribute is used to hide content and show other content, depending on
   * whether or not the logged in user is among the list of users identifies in the
   * uid attribute.
   *
   * @param node the FBMLNode modeling the <fb:visible-to-user> tag.
   * @return the full rendering of all children, with CSS visibility
   *         tags set to hide content if the logged in user's UID isn't
   *         among the list specified as part of the uid attribute.
   */

  public function fb_visible_to_user($node)
  {
    $this->_flavor->check('visible_to');
    if ($this->is_logged_out())
      return $this->render_visible_to($node, false);

    $uid_set = $node->attr_ids('uid', $this);
    $user_in_set = isset($uid_set[$this->get_env('user')]);

    return $this->render_visible_to($node, $user_in_set);
  }

  /**
   * Similar to fb_visible_to_user, except that visibility is determined
   * by whether or not the logged in user is friends with the profile
   * owner.
   *
   * @param node an FBMLNode modeling the <fb:visible-to-friends> tag.
   * @return the full rendering of all children, including <fb:else> children,
   *         wrapped up in span tags so that visibility can be set based on
   *         friendship status.
   */

  public function fb_visible_to_friends($node)
  {
    $this->_flavor->check('visible_to');
    if ($this->is_logged_out())
      return $this->render_visible_to($node, false);
    $profile = $this->get_env('profile');
    $user = $this->get_env('user');
    $are_friends = true; // placeholder for real logic to determine friendship
    return $this->render_visible_to($node, $are_friends);
  }

  /**
   * Renders all children, but sets visibility flags based on
   * whether or not the user has granted app permissions to the
   * application.
   *
   * @param node the FBMLNode modeling the <fb:visible-to-app-users>
   *             tag.
   * @return the full rendering of all children, where the if and else
   *         parts are wrapped with span tags so that visibility is easily
   *         turned on or off.
   */

  public function fb_visible_to_app_users($node)
  {
    $this->_flavor->check('visible_to');
    if ($this->is_logged_out())
      return $this->render_visible_to($node, false);

    $uid = $node->attr_id('uid', $this, false, 'loggedinuser');
    $app_id = $this->get_env('app_id');
    $visible = $this->application_has_full_permission($app_id, $uid);
    return $this->render_visible_to($node, $visible);
  }

  /**
   * Renders all of the children of the <fb:visible-to-added-app-users>
   * node modeled by node, setting visibilities on the basis of whether
   * or not the viewer has added the relevant application.
   *
   * @param node the FBMLNode modeling a <fb:visible-to-added-app-users>
   *             tag.
   * @return the full rendering of all children (including <fb:else> children)
   *         where CSS visibility is used to show and hide various parts
   *         of the rendering.
   */

  public function fb_visible_to_added_app_users($node)
  {
    $this->_flavor->check('visible_to');
    if ($this->is_logged_out())
      return $this->render_visible_to($node, false);

    $uid = $node->attr_id('uid', $this, false, 'loggedinuser');
    $app_id = $this->get_env('app_id');
    $visible = $this->user_has_added_application($uid, $app_id);
    return $this->render_visible_to($node, $visible);
  }

  /**
   * Invoked to render an FBML tree rooted by an <fb:if> tag, where
   * the <fb:if> tag is modeled by the incoming node.  This is a
   * generic if tag with one optional attribute called value.  If
   * the value of the attribute is "true", then fb_if returns the
   * full rendering of all children.  If it's anything else (or if
   * it's omitted entirely), then just the <fb:else> children are
   * rendered and their serialization returned.
   *
   * @param node the FBMLNode modeling a <fb:if> tag.
   * @return the full HTML rendering of all of the children (if the
   *         value attribute comes in as 'true') or the full rendering
   *         of just the <fb:else> children (if the value attribute is
   *         missing or set to anything other than logical true.
   */

  public function fb_if($node)
  {
    return $this->render_if($node, $node->attr_bool('value', false));
  }

  /**
   * If called, it's because an fb:else node was asked to render itself.
   * "fb:else" nodes render to the empty string, because the child nodes
   * hanging from the <fb:else> nodes aren't supposed to be rendered.
   * Nodes below an <fb:else> node are only rendered when some condition
   * fails, but if fb_else is being invoked, it's because some test passed.
   *
   * @param ignored_node a FBML node correspoding to the root of some FBML
   *                     tree.
   * @return the empty string.
   */

  public function fb_else($ignored_node) { return ''; }

  /**
   * Manages the compilation/rendering of the fb:user-agent tag,
   * which can be included in an FBML document to include content
   * on a browser-by-browser basis.  The fb:user-agent node requires
   * an "includes" attribute, which is a comma-separated list of strings
   * to test for inclusion against the HTTP request's user-agent header.
   * If and only if some string in that list appears within the user-agent
   * header do to the children of the node modeling <fb:user-agent> get
   * rendered.  An optional (and self-explanatory) "excluded" attribute
   * can't be used to included content for all but a subset of browsers.
   *
   * @param node the FBMLNode modelling the <fb:user-agent> tag.
   * @return the rendering of the node's children if (and only if) the
   *         includes and excludes values allow the content to be rendered,
   *         and false otherwise.
   */

  public function fb_user_agent($node)
  {
    $this->check('user_agent');
    $includes = $node->attr('includes', null, true);
    $excludes = $node->attr('excludes', null, false);

    if (!$includes && !$excludes)
      return $this->render_children($node);

    $render_children = false; // assume no

    if (($includes && self::matches_token($includes)) || (!$includes && $excludes))
      $render_children = true; // okay, maybe children get rendered..

    if ($includes && self::matches_token($excludes))
      $render_children = false; // okay, maybe not..

    if ($render_childrenm)
      return $this->render_children($node);

    return '';
  }


  /**
   * Compiles/rendered the <fb:time> tag modeled by the specified node.
   * The one required attribute is 't', whose value is the time (expressed in
   * epoch second) to display.  There are two optional attributes: 'tz' can
   * be set equal to the time zone (but if omitted defaults to the logged in
   * user's time zone), and 'preposition', which is a Boolean value which
   * if true prompts compilation to prepend "at " to the front of a time or
   * "on " to the front of the date.
   *
   * @param node the FBMLNode modeling the <fb:time> tag.
   * @return a string spelling out to the time expressed via <fb:time>'s 't'
   *         attribute.
   */

  public function fb_time($node)
  {
    $time = $node->attr_int('t', 0, true); // required
    $time_zone = $node->attr('tz', '', false);
    if (!$time_zone) $time_zone = "America/Los_Angeles";
    $preposition = $node->attr_bool('preposition', false, false);
    $format = $this->get_fbml_data_format($time, $preposition);
    $old_time_zone = date_default_timezone_get();
    date_default_timezone_set($time_zone);
    $time_str = date($format, $time);
    date_default_timezone_set($old_time_zone);
    return $time_str;
  }

  /**
   * Constructs the HTML needed to print the name of the
   * text and format it as a link to the event's page.
   *
   * Note the contrived implementation of helper methods
   * in place to simulate the actual checks that would likely
   * be done by a true FBML implementation.
   *
   * @param node an FBML node representing the <fb:eventlink> tag.
   * @return the HTML responsible for printing the name of the
   *         event and formatting it as a hyperlink to the
   *         named event's page.
   */

  public function fb_eventlink($node)
  {
    $this->check('links');  // ensure flavor allows hyperlinks
    $eid = $node->attr_id('eid', $this, true);
    if (!$this->is_event($eid))
      throw new FBMLException('"'.$eid.'" is not a valid event id.');

    $viewer = $this->get_env('user');
    if ($this->is_logged_out() ||
        !$this->user_can_see_event($eid, $viewer)) return "";

    $event_link = "http://www.facebook.com/event.php?eid=".$eid;
    $event_name = $this->get_event_name($eid);
    return '<a href='.$event_link.'>'.$event_name.'</a>';
  }

  /**
   * Pretend that all legitimate event identifiers are in the
   * range from 1000000000 to 1999999999, and end in 8.  Of course,
   * this is entirely contrived for the purpose of the sample
   * implementation.
   *
   * @param eid an event identifier being confirmed.
   * @return true if and only if the supplied event id is a
   *         legitimate one, and false otherwise.
   */

  protected function is_event($eid)
  {
    return ((1000000000 <= $eid) &&
            ($eid < 2000000000) &&
            ($eid % 10 == 8));
  }

  /**
   * Returns true if and only if this specified user aka 'viewer'
   * has permission to view the event page for the event with the
   * supplied 'eid'.  This placeholder implementation just returns
   * true.
   *
   * @param eid the event id of the event in question.
   * @param viewer the user id of the user in question.
   * @return true if and only if the specified user has permission to
   *         view the specified event's page.
   */

  protected function user_can_see_event($eid, $viewer) { return true; }

  /**
   * Extracts the name from the event record associated with the
   * specified event id.  This mock implementation constructs
   * a generic name to include the event id itself.
   *
   * @param eid the id of the event record whose name we're interested in.
   * @return the name of the event identified by the incoming event id.
   */

  protected function get_event_name($eid)
  {
    return "Event With Event ID ".$eid;
  }

  /**
   * Traverses the FBML subtree rooted at 'node'
   * and returns either the full rendering of everything
   * hanging from that node, or the rendering of the
   * <fb:else> children if the supplied predicate test
   * fails.
   *
   * @param node the root of some FBML tree.
   * @param predicate a bool deciding whether to render just
   *                  the immediate children that are "fb:name"
   *                  nodes or to render everything else.
   * @return the serialization of all of the children (excluding the
   *         <fb:else> children) if the supplied predicate is true,
   *         and the serialization of just the <fb:else> children
   *         if the supplied predicate is false.
   */

  protected function render_if($node, $predicate)
  {
    if ($predicate) {
      return $this->render_children($node);
    } else {
      return $this->render_else($node);
    }
  }

  /**
   * Renders either what is inside a node or inside the else branch of a node
   * This function is different from render_if, because everything is laid down in the
   * HTML serialization, except that CSS's visibility attribute is used to dictate
   * what gets shown and what doesn't.
   *
   * @param node an FBML node modeling a tag from the family of <fb:visible-to-*> tags.
   * @param predicate backup Boolean value to rely on if the current user can't view the
   *                  current profile.
   */

  protected function render_visible_to($node, $predicate)
  {
    $user = $this->get_env('user');
    $profile = $this->get_env('profile');
    if ($this->can_edit_profile($user, $profile)) {
      $show_if = true;
      $show_else = true;
    } else {
      $show_if = $predicate;
      $show_else = !$predicate;
    }

    $if_block = $this->render_children($node);
    $else_block = $this->render_else($node);
    $bgcolor = $node->attr_color('bgcolor', '#FFFFFF');
    return
      '<span style="background: ' . $bgcolor . ';">' .
      '<span style="visibility: ' . ($show_if ? 'visible' : 'hidden') . ';">' . $if_block . '</span>' .
      '<span style="visibility: ' . ($show_else ? 'visible' : 'hidden') . ';">' . $else_block . '</span>' .
      '</span>'
      ;
  }

  /**
   * Traverses the FBML tree hanging from the specified 'node', including just
   * the content under jurisdiction of those immediate children of tag type
   * "fb:else".
   *
   * @param node the root of some FBML tree.
   * @return the rendering of all of the root node's "fb:else" children.
   */

  protected function render_else($node)
  {
    $html = '';
    foreach ($node->get_children() as $child) {
      if ($child->get_tag_name() == 'fb:else') {
        $html .= $child->render_children($this);
      }
    }

    return $html;
  }

  /**
   * Placeholder implementation to imitate the check as to
   * whether or not the user (stored in the implementation's
   * environment) is logged in.  The dummy implementation here
   * just returns false, so the workflow isn't interrupted.
   *
   * @return true if and only if the user is logged out, and false
   *              otherwise.  (Dummy implementation here always
   *              returns false.)
   */

  protected function is_logged_out()
  {
    return false;
  }

  /**
   * Placeholder implementation to imitate the check as to
   * whether or not the specified user can view the specified
   * profile.  The dummy implementation here just returns true if
   * the user and profile variables are equal in the == sense.
   *
   * @param $user the id of some user.  This is ignored by our placeholder
   *              implementation.
   * @param $profile the id of some profile.  This is ignored by our placeholder
   *                 implementation.
   * @return Boolean value that's true if the specified user can edit the specified
   *                 profile.  The placeholder implementation just returns true
   *                 when $user and $profile are equal in the == sense.
   */

  protected function can_edit_profile($user, $profile)
  {
    return $user == $profile;
  }

  /**
   * Placeholder implementation for the logic that determines
   * whether or not the specified user (expressed via 'uid') has
   * added the application with the specified 'app_id'  Here, I just
   * return true as a placeholder.
   *
   * @param uid the id of the user in question.
   * @param app_id the id of some application.
   * @return true if and only if the specified user had added the
   *              identified application.
   */

  protected function user_has_added_application($uid, $app_id)
  {
    return true;
  }

  /**
   * Returns true if and only if the specified application has
   * been granted all permissions by the specified user.  Our
   * placeholder implementation just returns true.
   *
   * @param app_id the id of the application in question.
   * @param uid the id of the logged in user.
   * @return true if and only if the specified application has been
   *              granted all permissions by the specified user.
   */

  protected function application_has_full_permission($app_id, $uid)
  {
    return true;
  }

  /**
   * Returns the format string needed to sensibly display a time.
   * If the specified timestamp happens to be some time today,
   * then we post just the time and not the date (with a preposition,
   * if that's what's expected).  If the specified time falls on some
   * other day this year, then we return a format string that includes
   * the month and the day but not the year.  If the time falls on
   * some day in some year other than the current one, then we return
   * the format string that display month, day, year, and time.
   *
   * @param timestamp a time, expressed in epoch seconds.
   * @param prep true if either "at" or "or" as appropriate should
   *             be prepended to the time, and false if nothing should
   *             be prepended at all.
   * @return the format string that should be used to publish the specified
   *         time.
   */

  protected function get_fbml_data_format($timestamp, $prep = false)
  {
    $now = time();
    if (date('Ymd', $timestamp) == (date('Ymd', $now))) {
      if ($prep) {
        $format = '\a\t g:ia';
      } else {
        $format = 'g:ia';
      }
    } elseif (date('Y', $timestamp) == (date('Y', $now))) {
      if ($prep) {
        $format = '\a\t g:ia \o\n F j';
      } else {
        $format = 'F j g:ia';
      }
    } else {
      if ($prep) {
        $format = '\a\t g:ia \o\n F j, Y';
      } else {
        $format = 'F j, Y g:ia';
      }
    }

    return $format;
  }

  /**
   * Constructs that FBML tag name that gets handled by the provided
   * method name called 'name'.
   *
   * @param name the name of the method, which presumably begins with
   *             "fb_".
   * @return the FBML tag type/name covered by the named method.
   */

  protected static function transform_macro_method_to_tag($name)
  {
    return 'fb:' .  str_replace('_', '-', substr($name, 3));
  }

  /**
   * Returns true if and only if one or more of the
   * tokens in the comma-delimited set is a substring of
   * the user-agent value provided as part of the HTTP request.
   *
   * @param $string_set the comma-delimited set of user agents
   *                    of interest (i.e. "IE6, IE7")
   * @return true if and only if one or more of the tokens in
   *         the string set appears as a substring of the
   *         user-agent string that came with the HTTP User-Agent
   *         request header.
   */

  protected static function matches_token($string_set)
  {
    $list_arr = explode(',', $string_set);
    foreach ($list_arr as $token) {
      if (stristr($_SERVER['HTTP_USER_AGENT'], $token))
        return true;
    }

    return false;
  }

}
