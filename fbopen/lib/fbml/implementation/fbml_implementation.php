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


include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/fbjs.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/context.php";
include_once $_SERVER['PHP_ROOT'] . "/lib/fbml/html.php";

// Facebook Copyright 2006 - 2008

/**
 * The FBMLImplementation is an abstract base class that
 * consolidates all methods needed by any specified FBML
 * implementation.
 */

abstract class FBMLImplementation {

  /**
   * Constructs an FBMLImplementation instance that understands
   * exactly how all supported HTML and FBML tags should be compiled.
   *
   * @flavor the FBMLFlavor instance that knows precisely which FBML
   *         flags are permitted and which flags are forbidden for
   *         a particular rendering.
   */

  protected function __construct($flavor)
  {
    $this->_flavor = $flavor;
    if ($flavor) {
      $this->_fbml_env = $flavor->get_fbml_env();
    } else {
      $this->_fbml_env = array();
    }

    $this->_errors = array();
    $this->_html_renderer = new HTMLRenderer();
    $this->_html_rewriter = $this->_html_rewriter();
  }

  /**
   * Returns the value attached to the specified environment
   * variable specified via 'var'.  If the environment variable
   * isn't defined even though it's required to be, then an
   * FBMLException gets thrown.  If the environment variable
   * isn't defined, but it isn't required either, then the
   * value supplied via 'default' is returned instead.
   *
   * @param var the name of the environment variable.
   * @param required true if and only if the environment variable
   *                 absolutely must be defined, false otherwise.
   * @param default the value to return if an environment variable
   *                is neither required nor defined.
   */

  public function get_env($var, $required = true, $default = null)
  {
    if (isset($this->_fbml_env[$var]))
      return $this->_fbml_env[$var];

    if ($required)
      throw new FBMLException('Environment setting missing: "' . $var . '"');

    return $default;
  }

  /**
   * Returns a new instance of the HTML rewriter that this implementation uses
   * @return    HTMLRewriter
   */
  protected function _html_rewriter() {
    return HTMLRewriter::most_recent_html_rewriter_($this);
  }


  /**
   * Add the specified error message to the accumulation
   * of error messages that have built up since the receiving
   * FBMLImplementation instance was created.
   *
   * @param error_msg the error message to add to the collection
   *                  of error messages that have accumulated since
   *                  the FBMLImplementation was created.
   */

  public function add_error($error_msg)
  {
    $this->_errors[] = $error_msg;
  }

  /**
   * Return the array of error messages that have accumulated
   * the FBMLImplementation's lifetime.
   *
   * @return an array containing all of the error messages that have built up during
   *         the lifetime of the receiving object.
   */

  public function get_errors()
  {
    return $this->_errors;
  }

  /**
   * Simple accessor to hand out the object responsible for
   * rendering traditional HTML.
   *
   * @return the HTMLRenderer associated with the receiving FBMLImplementation
   *         instance.
   */

  public function get_html_renderer()
  {
    return $this->_html_renderer;
  }

  /**
   * Performs some common useful substitutions on an
   * ID string and casts it to an int if it doesn't
   * match any of the terms.  This is managed by
   * the FBMLImplementation hierarchy instead of
   * FBMLNode, because different FBML implementations
   * might want to support different substitutions.
   *
   * @param id the uid of interest (in string form), which is either
   *           a genuine uid, or one of a few terms that get mapped to
   *           uids.
   * @return an integer uid, which is either the integer form of the string
   *         that came in, or the integer uid after substitution.
   */

  public function get_substituted_attr_id($id)
  {
    switch ($id) {
      case 'loggedinuser':
        return $this->get_env('user');
      case 'profileowner':
        return $this->get_env('profile');
      case 'sender': // for alerts
        return $this->get_env('sender');
      default:
        return (int) $id;
    }
  }

  public function prerender() { return ''; }
  public function render_children($node) { return $node->render_children($this); }

  /**
   * Checks to confirm that the specified tag is supported by the current
   * flavor.  The buck gets passed to the flavor class.
   *
   * @exception FBMLException is thrown if the active
   *            flavor forbids the following tag from
   *            appearing in the document being rendered.
   */

  protected function check($tag_category)
  {
    $this->_flavor->check($tag_category);
  }

  /**
   * Returns the FBJS rewritter for this FBML instance
   * @return FBJSParser
   */
  public function _fbjs_impl() {
    $retval = FBJSParser::singleton($this->get_env('app_id'), $this);
    return $retval;
  }

  /**
   * Returns arbitrary HTML code that should be appended to the end of the result
   * @param   FBMLParseTree
   * @return  string        HTML
   */
  public function postrender($parse_tree) {
    return $this->_fbjs_impl()->postrender();
  }

  /**
   * Adds the current context to this implementations set of contexts
   * @return    string    Hashed context identifier
   */
  public function add_context($flavor=null) {
    list($m_context, $h_context) = FBMLContext::marshall_($flavor ? $flavor : $this->_flavor);
    $this->_contexts[$h_context] = $m_context;
    return $h_context;
  }

  /**
   * Use this instead of the normal onloadRegister function so that everything
   * works properly inside an fb:js-string.  Make sure to render the result of
   * this out to the html.
   */
  // FBOPEN:NOTE - Theis stuff needs to be translated on output.
  public function onloadRegister($js) {
    return render_js_inline('(function(){' . $js . '})();');
  }

  public $_flavor; // Needs to be public for FBJSParser

  protected $_errors;
  protected $_html_renderer;
  protected $_fbml_env;
  public $_html_rewriter;


}

?>
