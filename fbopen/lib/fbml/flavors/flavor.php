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
 * An FBML flavor dictates which kinds of FBML tags an FBML
 * implementation is permitted to render.
 */

abstract class FBMLFlavor {

  /**
   * Constructs an FBMLFlavorInstance to encapsulate
   * the specified FBML environment, which is an associative
   * array mapping key properties (as in 'user_id', 'app_id',
   * and so forth) to values.  These values directly influence
   * how certain pure HTML and FBML tags are rendered.
   *
   * @param fbml_env an associative array of keys relevant to
   *                 the current user's session to values.
   */

  public function FBMLFlavor($fbml_env)
  {
    $this->_fbml_env = $fbml_env;
  }

  /**
   * Returns a reference to the FBML environment.
   *
   * @return a reference to the FBML environment associative array
   *         held by the receiving FBMLFlavor.
   */

  public function get_fbml_env()
  {
    return $this->_fbml_env;
  }

  /**
   * Returns true if and only if the receiving FBML flavor
   * allows FBML tags in the specified 'category' to be rendered.
   * The implementation searches the class for a method with the name
   * allows_<$category> [so a call to allows('names') would search
   * for a method named 'allows_names'.  If the method exists, it
   * lets it decided whether or not tags under the specified category
   * are allowed.  It otherwise relies on whatever answer _default()
   * comes back with.
   *
   * @param @category the category of tags in question.
   */

  public function allows($category)
  {
    $method = 'allows_' . $category;
    if (method_exists($this, $method)) {
      return $this->$method();
    } else {
      return $this->_default();
    }
  }

  /**
   * Asserts that the receiving flavor allows tags in the specified
   * category to be rendered.  If the receiving flavor forbids the
   * specified category of tags to be rendered, then an FBMLException
   * is thrown.  If such tags are permitted, then nothing happens
   * and the method returns without any side effects.
   */

  public function check($category)
  {
    $tags_allowed = $this->allows($category);
    if ($tags_allowed) return;
    throw new FBMLException('The '.get_class($this).' flavor forbids '.
                            'tags in category \''.$category.' from being '.
                            'rendered.');
  }

  /**
   * The following section chooses to provide implementations for a
   * huge collection of categories.  Note that virtually all of the
   * public methods below defer to some other method.  This is done
   * to encode logical relationships between categories--i.e.  if we
   * don't allow HTML, then we normally don't allow images, videos, and
   * so forth.
   *
   * This also provides the type of naming conventions other FBML
   * implementations might want to adopt.  Note that the list of
   * categories below is exhaustive; it's the full list of categories
   * used by Facebook's Developer Platform.  The sample implementation
   * we've provided doesn't use all of these.
   */

  public function allows_html() { return $this->_default(); }
  public function allows_tables() { return $this->allows_html(); }
  public function allows_bdo() { return $this->allows_html(); }
  public function allows_bold() { return $this->allows_html(); }
  public function allows_underline() { return $this->allows_html(); }
  public function allows_italics() { return $this->allows_html(); }
  public function allows_lists() { return $this->allows_html(); }

  public function allows_phrases() { return $this->allows_html(); }
  public function allows_strikethru() { return $this->allows_html(); }
  public function allows_linebreaks() { return $this->_default(); }
  public function allows_block_level_elements() { return $this->allows_linebreaks(); }
  public function allows_horizontal_rules() { return $this->allows_block_level_elements(); }
  public function allows_spans() { return $this->allows_html(); }
  public function allows_css() { return $this->allows_html(); }
  public function allows_cssincludes() { return $this->_default(); }
  public function allows_styleattributes() { return $this->allows_css(); }
  public function allows_relative_urls() { return $this->_default(); }
  public function allows_interactivity() { return $this->_default(); }
  public function allows_mock_ajax() { return $this->allows_interactivity(); }
  public function allows_dialog() { return $this->allows_interactivity(); }
  public function allows_dialogresponse() { return false; }
  public function allows_flash() { return $this->allows_interactivity(); }
  public function allows_script() { return $this->allows_interactivity(); }
  public function allows_script_onload() { return $this->allows_interactivity(); }
  public function allows_flash_autoplay() { return $this->allows_flash(); }

  public function allows_message_preview() { return false; }
  public function allows_wall_attachment_img() { return false; }
  public function allows_forms() { return $this->allows_html(); }
  public function allows_links() { return $this->allows_html(); }
  public function allows_comments_macro() { return $this->allows_html(); }
  public function allows_board() { return $this->allows_html(); }
  public function allows_google_analytics() { return $this->_default(); }
  public function allows_proxy() { return $this->_default(); }
  public function allows_random() { return $this->_default(); }
  public function allows_intl() { return $this->_default(); }
  public function allows_headers() { return $this->_default(); }

  public function allows_requires() { return $this->_default(); }
  public function allows_visible_to() { return $this->_default(); }
  public function allows_visible_to_bgcolor() { return $this->_default(); }
  public function allows_user_agent() { return $this->allows_html(); }

  /**
   * Forces subclasses to make a decision about whether
   * or not certain categories of tags are permitted
   * or forbidden.  Of course, subclasses can override
   * all methods, but the most dramatic impact a subclass
   * has on what's allowed versus what isn't comes from the
   * implementation of this _default method.
   */

  /**
   * Returns a string that when unserialized turns into this flavor
   * @return  string   Serialized FBMLFlavor
   */
  public function serialize() {
    return serialize($this);
  }

  protected abstract function _default();
  public $_fbml_env; 
}

?>
