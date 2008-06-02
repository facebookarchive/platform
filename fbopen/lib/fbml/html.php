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



include_once $_SERVER['PHP_ROOT'].'/lib/fbml/utils/attribute_utils.php';
/**
 * A class used for rewriting HTML in a safe way; used by FBML
 *
 */
class HTMLRewriter {

  public $_fbml_impl;
  public $_url_attr = array ('href',
                             'src',
                             'background',
                             'url',
                             'dynsrc',
                             'lowsrc',
                             'clickrewriteurl'
                                    );

  public $_mock_ajax_attr = array(
                                  'clicktohide',
                                  'clicktoshow',
                                  'clicktoshowdialog',
                                  'clicktotoggle',
                                  'clicktodisable',
                                  'clicktoenable',
                                  'clickrewriteurl',
                                  'clickrewriteid',
                                  'clickrewriteform'
                                  );

  public $_js_attr = array( 'onclick',
                            'onmouseover',
                            'onabort',
                            'onblur',
                            'ondblclick',
                            'onerror',
                            'onfocus',
                            'onkeydown',
                            'onkeypress',
                            'onkeyup',
                            'onload',
                            'onmousedown',
                            'onmousemove',
                            'onmouseout',
                            'onmouseover',
                            'onmouseup',
                            'onreset',
                            'onresize',
                            'onselect',
                            'onsubmit',
                            'onunload',
                            'onchange'
                            );



  public function __construct($fbml_impl) {
    $this->_fbml_impl = $fbml_impl;
    $this->_use_ext = function_exists('fbml_parse_opaque_11') &&
      (IS_DEV_SITE ||
       (get_site_variable('FBML_FBML_EXT') == 2) ||
       fbml_is_beta_machine());
  }

  /**
   * Does whatever we decide to do for attribute value encoding
   * @param    string    $attr_val
   * @return   string    Encoded attribute value
   *
   * Right now, this does txt2html
   * when PHP 5.2.3 is installed, without doubly encoding existing entities
   */
  public function attr_encode($attr_val) {
    return txt2html($attr_val);
  }

  /**
   * Renders a attr-value pair
   * @param   string    $attr
   * @param   string    $val
   * @return  string    attr="val"
   */
  public function render_attr($attr, $val) {

    return $attr . '="' . $this->attr_encode($val) . '"';
  }

  /**
   * Record an error the way we would in the FBML implementation
   * @param  string   $msg
   */
  public function add_error($msg) {
    return $this->_fbml_impl->add_error($msg);
  }

  /**
   * Proxy check calls straight to the implementation flavor
   * @param   string   $what
   */
  public function check($what) {
    return $this->_fbml_impl->_flavor->check($what);
  }

  /**
   * Proxy allows calls straight to the implementation flavor
   * @param   string   $what
   */
  public function allows($what) {
    return $this->_fbml_impl->_flavor->allows($what);
  }

  /**
   * Returns HTML for an open tag in HTML
   * @param   string   $tag_name
   * @param   dict     $attributes    {<attr>: <val>}
   * @return  string   HTML
   *
   */
  function render_html_open_tag($tag_name, $attributes) {
    $html = '<' . $tag_name;

    foreach ($attributes as $attr => $val) {
      $val = substr(txt2html(' '.$val.' '), 1, -1);
      $html .= ' '.$attr.'="'.$val.'"';
    }
    $html .= '>';
    return $html;
  }

  /**
   * Returns HTML for a close tag in HTML
   * @param   string   $tag_name
   * @return  string   HTML
   *
   */
  function render_html_close_tag($tag_name) {
    return '</' . $tag_name . '>';
  }

  /**
   * Returns HTML for ar singleton tag
   * @param    string   $tag_name
   * @param   dict     $attributes    {<attr>: <val>}
   * @return  string   HTML
   *
   */
  function render_html_singleton_tag($tag_name, $attributes) {

    $html = '<' . $tag_name;
    foreach ($attributes as $attr => $val) {
      $val = substr(txt2html(' '.$val.' '), 1, -1);
      $html .= ' '.$attr.'="'.$val.'"';
    }
    $html .= ' />';
    return $html;
  }

  /**
   * Prefixes an id attribute with the app id
   * @param   string    $id   HTML id attribute value
   * @return  string    Prefixed id attribute value
   */
  public function prefix_id($id) {
    return 'app' . $this->_fbml_impl->get_env('app_id') . '_' . $id;
  }

  /**
   * Returns the open tag for an HTML node
   * @param   FBMLHTMLNode    $node
   * @return  string          HTML
   *
   * ex: <a href="someurl">
   *
   * If you are thinking about modifying this function, you should
   * probably just be calling render_html_open_tag instead
   */
  public function node_open($node) {
    return $this->render_html_open_tag($node->get_tag_name(), $this->node_get_safe_attrs($node));
  }

  /**
   * Returns the close tag for an HTML node
   * @param   FBMLHTMLNode    $node
   * @return  string          HTML
   *
   * ex: </b>
   */
  public function node_close($node) {
    if ($node->get_tag_name() !== null) {
      return $this->render_html_close_tag($node->get_tag_name());
    }
  }

  /**
   * Returns the HTML representation of a singleton HTML node
   * @param   FBMLHTMLNode   $node
   * @return  string         HTML
   *
   * ex: <img src="someimgurl" />
   */
  public function node_singleton($node) {
    return $this->render_html_singleton_tag($node->get_tag_name(), $this->node_get_safe_attrs($node));
  }

  /**
   * Returns an instance of the most recent HTML rewriter in the codebase
   * @param   FBMLImplementation    $fbml_impl
   * @return  HTMLRewriter          The latest version of an HTML rewriter
   */
  public static function most_recent_html_rewriter_($fbml_impl) {
    return new HTMLRewriter_0_1($fbml_impl);
  }

  public function get_env($var, $required=true, $default=null) {
    return $this->_fbml_impl->get_env($var, $required, $default);
  }

}

/**
 * HTMLRewriter v0.1
 */
class HTMLRewriter_0_1 extends HTMLRewriter {

  function is_app_user() {
    return platform_app_has_full_permission($this->get_env('app_id'), $this->get_env('user'));
  }

  public function open_form($node) {
    $hidden_inputs = array();

    $flavor_codes = fbml_flavors_get_codes();

    $page = 0;

    if (($profile = $this->get_env('profile', false)) != null) {
      $hidden_inputs['profile'] = $profile;
      $page = obj_is_fbpage($profile) ? $profile : 0;
    }

    if ($this->_fbml_impl->_flavor->get_flavor_code() == $flavor_codes['CANVAS_PAGE']) {
      $page = $this->get_env('fb_page_id', false);
    }

    $who = array('user' => $this->get_env('user'));
    if ($page) {
      $hidden_inputs += api_canvas_parameters_other_fbpage($page, $this->get_env('user'));
      $who['page'] = $page;
    }

    $require_login = $node->attr_bool('requirelogin', true) && !$this->get_env('loggedout', false);


    $hidden_inputs = get_fb_validation_vars($who,
                                            $this->get_env('app_id'),
                                            $hidden_inputs,
                                            array(), $require_login);

    $attributes = $this->node_get_safe_attrs($node);

    if (isset($attributes['name'])) {
      unset($attributes['name']);
    }

    if (isset($attributes['action'])) {
      $allow_rel = $this->allows('relative_urls');
      $attributes['action'] = $this->validate_url($attributes['action'], true, $allow_rel, false);
    }

    if ($require_login) {
      // check for a valid session
      $session_key = api_get_valid_session_key($this->get_env('user'), $this->get_env('app_id'));
      if (!$session_key && $this->_fbml_impl->_flavor->allows('script')) {
        $onsubmit  = 'var form = this; ';
        $onsubmit .= 'FBML.requireLogin(' . $this->get_env('app_id') .
          ', function() { FBML.addHiddenInputs(form); form.submit(); });';
        $onsubmit .= 'return false;';
        $attributes['onsubmit'] = $onsubmit;
      }
    }

    $html = $this->render_html_open_tag('form', $attributes);

    foreach ($hidden_inputs as $name => $val) {
      $html .= $this->render_hidden_input($name, $val);
    }

    return $html;
  }


  /**
   * Returns HTML for a hidden input
   * @param   string     $name   INPUT name
   * @param   string     $value  value for this INPUT
   * @param   string     $id     optional value for id attribute
   * @return  string     HTML
   */
  public function render_hidden_input($name, $value, $id=null) {

    if ($id === null) {
      $id_attr = '';
    } else {
      $id_attr = ' ' . $this->render_attr('id', $id);
    }

    return '<input' . $id_attr . ' type="hidden" name="' . $this->attr_encode($name) . '" value="' . $this->attr_encode($value) . '" />';
  }


  /**
   * Checks to make sure that a form element is OK before rendering it
   * @param   FBMLNode    $node
   */
  public function check_form_element($node) {
    if (starts_with($name = $node->attr('name', ''), 'fb_')) {
      throw new FBMLRenderException('Names beginning with "fb_" in FORM elements are reserved for use by Facebook ("' . $name . '")');
    }
  }

  /**
   * Verifies that an URL is absolute and not Javascript
   * @param   string   $url             URL
   * @param   bool     $allow_empty     Whether to accept null as a valid value
   * @param   bool     $allow_relative  Whether to accept relative urls as a valid value
   * @param   bool     $allow_anchors   Whether to accept urls starting with #
   * @return  string   The URL
   *
   * Throws an exception if there is something wrong with the URL
   */
  function validate_url($url, $allow_empty=false, $allow_relative=true, $allow_anchors=true) {

    $purl = @parse_url($url);

    // If parse_url fails, it will return false
    if ($purl === false) {
      throw new FBMLUrlException('Unable to parse URL: ' . $url);
    }

    // scheme
    $scheme = isset($purl['scheme']) ? strtolower($purl['scheme']) : null;

    $allowed_schemes = array(
      'http' => true,
      'https' => true,
      'mailto' => true,
      'ftp' => true,
      'aim' => true,
      'irc' => true,
      'itms' => true,
      'gopher' => true,
      'gtalk' => true,
      'jabber' => true,
      'xmpp' => true,
      'ymsgr' => true,
      'msnim' => true,
      'skype' => true,
      'about' => true,
    );

    if (($scheme !== null) && (!isset($allowed_schemes[$scheme]))) {
      throw new FBMLUrlException('Invalid scheme for url (' . $url . ')');
    }

    // port
    $port_str = isset($purl['port']) ? ':' . $purl['port'] : '';

    // user / pass
    $username = isset($purl['user']) ? $purl['user'] : null;
    $passwd = isset($purl['pass']) ? $purl['pass'] : null;
    if (($username !== null) || ($passwd !== null)) {
      $user_pass_str = (($username === null) ? '' : $username);
      if ($passwd !== null) {
        $user_pass_str .= ':' . $passwd;
      }
      $user_pass_str .= '@';
    } else {
      $user_pass_str = '';
    }

    // special schemes
    if ($scheme == 'mailto' || $scheme == 'aim' || $scheme == 'gtalk' || $scheme == 'jabber' || $scheme == 'ymsgr' || $scheme == 'msnim' || $scheme == 'skype') {
      $scheme_sep = ':';
    } else {
      $scheme_sep = '://';
    }


    // querystring
    if (isset($purl['query'])) {
      $query_str = '?' . $purl['query'];
    } else {
      $query_str = '';
    }

    // anchor fragment
    if (isset($purl['fragment'])) {
      $anchor_fragment = '#' . $purl['fragment'];
    } else {

      // parse_url doesn't recognize a URL as having a fragment
      // if it just has '#' but nothing after it, so we special case that
      if (substr($url, -1) == '#') {
        $anchor_fragment = '#';
      } else {
        $anchor_fragment = '';
      }
    }


    // path
    $path_str = isset($purl['path']) ? $purl['path'] : '';

    // host
    $host_str = isset($purl['host']) ? $purl['host'] : '';



    // Assemble complete URL
    if ($scheme === null) {
      // not a full url...

      $valid_url = $path_str . $query_str . $anchor_fragment;
      if (!$valid_url) {
        // empty
        if (!$allow_empty) {
          throw new FBMLUrlException('Empty URLs not allowed here');
        }
      } else if (strlen($valid_url) > 0 && $valid_url[0] == '#') {
        // anchor/fragment
        if (!$allow_anchors) {
          throw new FBMLUrlException('Anchors/URL fragments not allowed here');
        }
      } else {
        // relative
        if (!$allow_relative) {
          throw new FBMLUrlException('Relative URLs not allowed here');
        }
      }

    } else {

      // Absolute URL
      $valid_url = $scheme . $scheme_sep . $user_pass_str . $host_str . $port_str . $path_str . $query_str . $anchor_fragment;
    }

    // make sure empty URLs are allowed
    if ((!$allow_empty) && ($valid_url === '')) {
      throw new FBMLUrlException('Not allowed to use empty URLs here');
    }

    // api_keep_url_in_sandbox is sort of expensive so we
    // only do it when we aren't on a production site
    if (IS_PRODUCTION_ANY) {
      $safe_url = $valid_url;
    } else {
      $safe_url = api_keep_url_in_sandbox($valid_url);
    }

    return $safe_url;
  }

  /**
   * Transform attributes for mock AJAX stuff
   * @param   dict        $attrs          The attributes
   * @param   bool        $onclick_retval Return value for onclick if it exists
   * @return  dict        {<attr>: <val>, <attr2>: <val2>, ...}
   */
  function mock_ajax_get_safe_attrs($attrs) {
    $safe_attrs = array();
    // 1.1 - click rewriteid is special
    if (isset($attrs['clickrewriteid'])) {
      // 1.1 in schema now
      $this->check('mock_ajax');
      //1.1 DONE checked in rewrite
      $url = $this->validate_url($attrs['clickrewriteurl']);

      // The form id can either be specified explicitly or else
      // we fall back on the form associated with the DOM element
      // that is being clicked on
      if ($form_id = isset($attrs['clickrewriteform']) ? $attrs['clickrewriteform'] : false) {
        $form_id_js = json_encode($this->prefix_id($form_id));
      } else {
        $form_id_js = 'self.form';
      }

      $loading_html = isset($attrs['clickrewriteloading']) ? $attrs['clickrewriteloading'] : null;

      // Set the onlick attribute to do the right thing
      $ajax_js = 'FBML.clickRewriteAjax('
        . json_encode($this->_fbml_impl->get_env('app_id')) . ', '
        . json_encode($this->is_app_user()) . ', '
        . json_encode($this->prefix_id($attrs['clickrewriteid'])) . ', '
        . json_encode($url) . ', ' . $form_id_js . ', '
        . json_encode($loading_html)
        . ');';
      $safe_attrs['onclick'] = 'var self = this;'.$ajax_js;
    }

    return $safe_attrs;
  }


  public function node_get_safe_attrs_ext($node) {
    // Chain all rewrite/hide/show/toggle elements together
    $onclick_retval = 'true';
    $attrs = $node->get_attributes();
    $safe_attrs = $attrs;

    // 1.1 DONE put these in interactivity
    $onclick = '';
    $click_params = array('clicktohide'    => 'clickToHide',
                          'clicktoshow'    => 'clickToShow',
                          'clicktoshowdialog' => 'clickToShowDialog',
                          'clicktotoggle'  => 'clickToToggle',
                          'clicktodisable' => 'clickToDisable',
                          'clicktoenable'  => 'clickToEnable');
    // 1.1 alter onclick
    foreach ($click_params as $cp => $cf) {
      if (isset($attrs[$cp])) {
        $onclick_retval = 'false';
        $click_elems = explode(',', $attrs[$cp]);
        foreach ($click_elems as $e) {
          $onclick .= 'FBML.'.$cf.'(' . json_encode($this->prefix_id(trim($e))) . ');';
        }
      }
    }


    // require login popup (FBOPEN:NOTE - behavior not defined in open source implementation)
    //
    $require_login = $node->attr_bool('requirelogin', false);
    if ($require_login) {
      $onclick .= 'var link = this; FBML.requireLogin(' . json_encode($this->_fbml_impl->get_env('app_id')) . ',' .
        ' function() { if (\'' . $attrs['href'] . '\') document.location.href=\'' . $attrs['href'] . '\'; });';
      $onclick_retval = 'false';
    }


    $mock_attrs = $this->mock_ajax_get_safe_attrs($attrs);
    if (isset($mock_attrs['onclick']) ) {
      $onclick = $mock_attrs['onclick'] . $onclick;
    }

    if (!empty($onclick)) {
      // moving the return false to mock_ajax_get_safe_attrs
      //$safe_attrs['onclick'] = $onclick.'return false;';
      empty($safe_attrs['onclick']) ? $safe_attrs['onclick'] = $onclick : $safe_attrs['onclick'] = $onclick . $safe_attrs['onclick'];
    }


    // allow a custom return value (mock_ajax_get_safe_attrs will ignore this if no
    // js is used)
    if (isset($attrs['clickrewriteid'])) {
      $onclick_retval = 'false';
    }
    if (isset($attrs['clickthrough'])) {
      // allow booleans only (it appears I can't use attr_bool because of the above code);
      // only check for true, false is the default anywho
      if ('true' == strtolower($attrs['clickthrough'])) {
        $onclick_retval = 'true';
      } else {
        $onclick_retval = 'false';
      }
    }

    $click_tracking_js = $this->get_fbml_click_tracking_js($node, $attrs);
    if ($click_tracking_js) {
      if (isset($safe_attrs['onclick'])) {
        // append click-tracking to the *beginning* of the onclick
        // handler, so we know it'll be executed
        $safe_attrs['onclick'] = $click_tracking_js . $safe_attrs['onclick'];
      } else {
        $safe_attrs['onclick'] = $click_tracking_js;
      }
    }

    // append feed tracking javascript if its defined by the calling function's environment
    // this is set in lib/feed/stories.php
    if ($this->_fbml_impl->get_env('feed_tracking_js', false)) {
      $feed_tracking_js = $this->_fbml_impl->get_env('feed_tracking_js');
      if (isset($safe_attrs['onclick'])) {
        $safe_attrs['onclick'] .= $feed_tracking_js;
      } else {
        $safe_attrs['onclick']  = $feed_tracking_js;
      }
    }


    // Rewrite id attribute
    if (isset($attrs['id'])) {
      $val = $attrs['id'];
      unset($attrs['id']);
      $safe_attrs['id'] = $this->prefix_id($val);
      $safe_attrs['fbcontext'] = $this->_fbml_impl->add_context();
    }

    // Proper return value for onclick
    if (isset($safe_attrs['onclick']) && $onclick_retval) {
      $safe_attrs['onclick'] .= 'return '.$onclick_retval;
    }

    return $safe_attrs;

  }


  /**
   * Returns an array of safe attributes for a node
   * @param   FBMLNode    $node
   * @param   bool        $allow_empty     Whether to accept null as valid values for 'href', 'src', 'background', and 'url'
   * @param   bool        $allow_relative  Whether to accept relative urls as valid values for 'href', 'src', 'background', and 'url'
   * @param   bool        $allow_anchors   Whether to accept urls starting with #
   * @return  dict        {<attr>: <val>, ...}
   */
  public function node_get_safe_attrs($node, $allow_empty=false, $allow_relative=true, $allow_anchors=false) {

    if ($this->_use_ext) {
      return $this->node_get_safe_attrs_ext($node);
    }

    // Check for a specific method that we should use for this tag
    $method = 'safe_attrs_' . $node->get_tag_name();
    if (method_exists($this, $method)) {
      $new_attrs = $this->$method($node);
      if ($new_attrs === null) {
        $attrs = $node->get_attributes();
      } else {
        $attrs = $new_attrs;
      }
    } else {

      $attrs = $node->get_attributes();
    }

    // Chain all rewrite/hide/show/toggle elements together
    $onclick_retval = 'true';
    $safe_attrs = $this->mock_ajax_get_safe_attrs($attrs);

    // 1.1 DONE put these in interactivity
    if ($this->allows('interactivity')) {
      $onclick = '';
      $click_params = array('clicktohide'    => 'clickToHide',
                            'clicktoshow'    => 'clickToShow',
                            'clicktoshowdialog' => 'clickToShowDialog',
                            'clicktotoggle'  => 'clickToToggle',
                            'clicktodisable' => 'clickToDisable',
                            'clicktoenable'  => 'clickToEnable');
      // 1.1 alter onclick
      foreach ($click_params as $cp => $cf) {
        if (isset($attrs[$cp])) {
          $onclick_retval = 'false';
          $click_elems = explode(',', $attrs[$cp]);
          foreach ($click_elems as $e) {
            $onclick .= 'FBML.'.$cf.'(' . json_encode($this->prefix_id(trim($e))) . ');';
          }
        }
      }


      // require login popup
      //
      $require_login = $node->attr_bool('requirelogin', false);
      if ($require_login) {
        $onclick .= 'var link = this; FBML.requireLogin(' . json_encode($this->_fbml_impl->get_env('app_id')) . ',' .
          ' function() { if (\'' . $attrs['href'] . '\') document.location.href=\'' . $attrs['href'] . '\'; });';
        $onclick_retval = 'false';
      }


      if (!empty($onclick)) {
        // moving the return false to mock_ajax_get_safe_attrs
        //$safe_attrs['onclick'] = $onclick.'return false;';
        empty($safe_attrs['onclick']) ? $safe_attrs['onclick'] = $onclick : $safe_attrs['onclick'] .= $onclick;
      }

      // allow a custom return value (mock_ajax_get_safe_attrs will ignore this if no
      // js is used)
      if (isset($attrs['clickrewriteid'])) {
        $onclick_retval = 'false';
      }
      if (isset($attrs['clickthrough'])) {
        // allow booleans only (it appears I can't use attr_bool because of the above code);
        // only check for true, false is the default anywho
        if ('true' == strtolower($attrs['clickthrough'])) {
          $onclick_retval = 'true';
        } else {
          $onclick_retval = 'false';
        }
      }
    }

    $click_tracking_js = $this->get_fbml_click_tracking_js($node, $attrs);
    if ($click_tracking_js) {
      if (isset($safe_attrs['onclick'])) {
        $safe_attrs['onclick'] .= $click_tracking_js;
      } else {
        $safe_attrs['onclick'] = $click_tracking_js;
      }
    }

    // append feed tracking javascript if its defined by the calling function's environment
    // this is set in lib/feed/stories.php
    // 1.1- yikes, not sure what to do
    if ($this->_fbml_impl->get_env('feed_tracking_js', false)) {
      $feed_tracking_js = $this->_fbml_impl->get_env('feed_tracking_js');
      if (isset($safe_attrs['onclick'])) {
        $safe_attrs['onclick'] .= $feed_tracking_js;
      } else {
        $safe_attrs['onclick']  = $feed_tracking_js;
      }
    }

    // Check if style attributes are allowed
    // 1.1 DONE, schema checks it
    if (isset($attrs['style'])) {
      if (!$this->allows('styleattributes')) {
        unset($attrs['style']);
        unset($safe_attrs['style']);
        $this->add_error('In tag ' . $node->get_tag_name() . ': Style attributes not allowed in flavor ' . get_class($this->_fbml_impl->_flavor));
      }
    }

    // Safe styles
    if (($this->_fbml_impl->get_env('unfiltered_css', false))) {
      $safe_attrs['style'] = isset($attrs['style']) ? $attrs['style'] : null;
    } else {
      if (isset($attrs['style'])) {
        if (!is_null($node->get_sanitized_style_attr())) {
          $safe_attrs['style'] = $node->get_sanitized_style_attr();
        } else {
          error_log('Expected a sanitized style attribute but didn\'t find one.  style=' . $attrs['style']);
        }
        unset($attrs['style']);
      }
    }

    // Rewrite id attribute
    if (isset($attrs['id'])) {
      $val = $attrs['id'];
      unset($attrs['id']);
      $safe_attrs['id'] = $this->prefix_id($val);
      $safe_attrs['fbcontext'] = $this->_fbml_impl->add_context();
    }

    foreach ($attrs as $attr => $val) {

      // Strip out non-alphanumeric attributes
      // 1.1 DONE, hard coded in parser
      if (preg_match('/[^A-Za-z0-9_\-]/', $attr)) {
        $this->add_error('Attribute names can only contain alphanumeric characters, underscores, and hyphens. (' . $attr . ')');
        unset($safe_attrs[$attr]);
        continue;
      }

      // Strip out any attribtues beginning with "on"
      if ( ($attr[0] == 'o' || $attr[0] == 'O') &&
           (isset($attr[1]) && ($attr[1] == 'n' || $attr[1] == 'N')) ) {  // LN: optimize starts_with(on)
        if ($this->allows('script')) {
          // 1.1 DONE (use rewrite attrs)
          $fbjs = $this->_fbml_impl->_fbjs_impl()->render_event($attr, $val);
          if ($fbjs === false) {
            $this->add_error('Unknown Javascript action attribute: '.$attr);
          } else {
            if (isset($safe_attrs[$attr])) {
              $safe_attrs[$attr] .= $fbjs;
            } else {
              $onclick_retval = false;
              $safe_attrs[$attr] = $fbjs;
            }
          }
        } else {
          // 1.1 - DONE, covered by schema
          $this->add_error('Javascript action attributes (onclick, onmouseup, etc.) are forbidden');
          unset($safe_attrs[$attr]);
        }
        continue;
      }

      // Make sure they don't use fb_ attributes
      if ($attr[0] == 'f' && (isset($attr[1]) && $attr[1] == 'b')) { // LN: optimize starts_with(fb)
        $this->add_error('Attributes starting with "fb" are reserved by Facebook, node: ' . $node->get_tag_name() . ', attribute: ' . $attr);
        continue;
      }

      // Make sure there are no angle brackets in the name of
      // the attribute
      // 1.1- DONE - Should now be a parse time error
      if ((strpos($attr, 60) === false) && (strpos($attr, 62) === false)) { // LN: use ords instead of chars to speed up strpos (60=<, 62=>)
        $safe_attrs[$attr] = $val;
      } else {
        $this->add_error('Angle brackets in attribute names are forbidden');
      }

    }

    // Proper return value for onclick
    if (isset($safe_attrs['onclick']) && $onclick_retval) {
      $safe_attrs['onclick'] .= 'return '.$onclick_retval;
    }

    // Sanitize attributes that we think are always URL
    // 1.1 DONE handled by rewrite,(this is nice)
    foreach (array(
      'href',
      'src',
      'background',
      'url',
      'dynsrc',
      'lowsrc',
    ) as $url_attr) {
      if (isset($safe_attrs[$url_attr])) {
        $safe_attrs[$url_attr] = $this->validate_url($safe_attrs[$url_attr], $allow_empty, $allow_relative, $allow_anchors);
      }
    }

    // Disallow names that begin with fb_
    // 1.1 DONE (rewrite)
    if (isset($safe_attrs['name'])) {
      $name = $safe_attrs['name'];

      if ($name[0] == 'f' && (isset($name[1]) && $name[1] == 'b')) { // LN: optimize starts_with(fb)
        $this->add_error('Names beginning with "fb" are reserved by Facebook (' . $val . ')');
        unset($safe_attrs['name']);
      }
    }

    return $safe_attrs;
  }

  //
  // Safe attribute methods
  //

  function safe_attrs_img($node) {
    // 1.1 try to get rid of this function
    $this->validate_url($node->_attributes['src'], true);
  }

  //  FBOPEN:NOTE - you may wish to track clicks of js for auditing purposes
  function get_fbml_click_tracking_js($node, $attrs = null) {
    return '';
  }

  function fbml_render_link($text, $href) {
    $user = $this->get_env('user');
    $app_id = $this->get_env('app_id');

    if (!$user || !$app_id) {
      return '';
    }

    $onclick_js = '';
    if ($this->allows('click_tracking')) {
      $action_types = application_get_platform_action_consts();
      $flav_codes = fbml_flavors_get_codes();
      $pos = $this->get_env('flavor_code', $flav_codes['UNKNOWN'], false);
      $onclick_js = get_click_tracking_js($app_id,
                                          $user,
                                          $action_types['LINK_CLICK'],
                                          $pos);
    }

    // append feed tracking javascript if its defined by the calling function's environment
    // this is set in lib/feed/stories.php
    if ($this->_fbml_impl->get_env('feed_tracking_js', false)) {
      $feed_tracking_js = $this->_fbml_impl->get_env('feed_tracking_js');
      if ($onclick_js) {
        $onclick_js .= $feed_tracking_js;
      } else {
        $onclick_js  = $feed_tracking_js;
      }
    }

    if ($onclick_js) {
      $onclick_js .= 'return true;';
    }

    return render_link($text,
                       $href,
                       $class='',
                       $id='',
                       $title='',
                       $target='',
                       $onclick_js,
                       $style='');
  }


  public function attr_is_url($attr) {
     return in_array($attr, $this->_url_attr);
  }
  public function attr_is_mock_ajax($attr) {
     return in_array($attr, $this->_mock_ajax_attr);
  }

  public function attr_is_js($attr) {
     return in_array($attr, $this->_js_attr);
  }


}

class HTMLRewriter_Internal extends HTMLRewriter {

  /**
   * No-op attribute rewriter. All attributes are safe as is in the
   * internal flavor.
   */
  public function node_get_safe_attrs($node, $allow_empty=false, $allow_relative=false, $allow_anchors=false) {
    return $node->get_attributes();
  }

  public function open_form($node) {
    return $this->node_open($node);
  }
}
