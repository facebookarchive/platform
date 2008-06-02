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
 * Sample implementation of FBML, where the functionality
 * enabling FBJS is enabled.
 */

class FBJSEnabledFacebookImplementation extends MiniFacebookImplementation {


  /**
   * Manages the compilation of an <fb:js-string> node, which
   * is designed to inline the declaration and initialization of
   * a new Javascript string variable.  In a nutshell, the idea is for
   * something like this:
   *
   *  <fb:js-string var="myName">Jerry Coopersmith</fb:js-string>
   *
   * to render to something like this:
   *
   *  <script type="text/javascript">
   *       a<app-id>_myName = "Jerry Coopersmith";
   *  </script>
   *
   * The implementation is careful to prevent the use of nested <fb:js-string> tags, and
   * provisions are also made to support the one-dimensional arrays, as outline
   * on the FBML Wiki.
   *
   * @param node the FBMLNode modeling the <fb:js-string> tag.
   * @return a HTML string that helps define a new Javascript variable on the fly, where
   *         the new variable is set equal to the full rendering of the <fb:js-string>'s
   *         children.
   */

  public function fb_js_string($node)
  {
    $user_triggered = isset($this->_fbml_env['user_triggered']) ? $this->_fbml_env['user_triggered'] : false;
    $this->_fbml_env['user_triggered'] = true;

    if ($this->get_env('fb_js_string', false))
      throw new FBMLException("Cannot nest fb:js-string tags");

    $this->_fbml_env['fb_js_string'] = true;  // used to protect against fb:js-string within fb:js-string
    $html = $this->render_children($node);
    $this->_fbml_env['user_triggered'] = $user_triggered;
    unset($this->_fbml_env['fb_js_string']);

    $var = $node->attr('var', 'fbml', true);  // extract the name of the JavaScript variable
    if (!preg_match('#^[a-zA-Z$_][a-zA-Z$_0-9]*(?:\.[a-zA-Z$_][a-zA-Z$_0-9]*)?$#', $var))
      throw new FBMLException('"'.$var.'" isn\'t a legitimate JavaScript variable name');

    if (strpos($var, '.') > 0) { // looks to be array access
      $var = explode('.', $var);
      $var[0] = 'a'.$this->get_env('app_id').'_'.$var[0];
      $var = '(typeof '.$var[0].'!=\'undefined\'?'.$var[0].':'.$var[0].'={}).'.$var[1];
    } else {
      $var = 'a'.$this->get_env('app_id').'_'.$var;
    }

    return $this->onloadRegister($var.'=new fbjs_fbml_string(\''.escape_js_quotes($html).'\')');

  }



  private function collect_script($node) {
    $children = $node->get_children();
    $text = isset($children[0]) ? $children[0] : null;
    $code = '';
    foreach ($children as $node) {
      $unsafe_code = $node->text_not_escaped();
      $safe_code = utf8_sanitize($unsafe_code);
      $code .= $safe_code;
    }
    return $code;
  }


  public function tag_a($node) {
    $this->_flavor->check('links');
    if ($this->get_env('link_context', false, false)) {
      $this->add_error('Warning: Nested A tag with href ' . $node->attr('href', '', true));
    }

    $attrs = $this->_html_rewriter->node_get_safe_attrs($node, true, $this->_flavor->allows('relative_urls'), true);
    $open_tag = $this->_html_rewriter->render_html_open_tag($node->get_tag_name(), $attrs);
    $close_tag = $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
    $this->_fbml_env['link_context'] = true;
    $inner_html = $node->render_children($this);
    $this->_fbml_env['link_context'] = false;

    return $open_tag . $inner_html . $close_tag;
  }

  public function tag_form($node) {
    $this->_flavor->check('forms');
    $form_type = $node->attr('fbtype', false);

    if ($form_type && method_exists($this, 'form_' . $form_type)){
      $method_name = 'form_' . $form_type;
      return $this->$method_name($node);
    } else {
      $ret = $this->_html_rewriter->open_form($node);
      $ret .= $node->render_children($this);
      $ret .= $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
      return $ret;
    }
  }

  public function tag_img($node) {
    $this->_flavor->check('images');
    // absolute URLs required, so no problems
    $safe_attrs = $this->_html_rewriter->node_get_safe_attrs($node);
    if (isset($safe_attrs['src'])) {
      // FBOPEN:NOTE - images should ideally be cached on fbml upload in your implementation,
      // rather than requiring fetch at render time, for performance as well as security
      // reasons.
      // $safe_attrs['src'] = $this->safe_image_url($safe_attrs['src']);
    }
    return $this->_html_rewriter->render_html_singleton_tag($node->get_tag_name(), $safe_attrs);
  }

  public function tag_input($node) {
    $this->_flavor->check('forms');

    $special = $this->get_env('special_form', false);
    if ($node->attr('type') == 'submit' && $special) {

      $method_name = 'submit_'.$special['fbtype'];
      return $this->$method_name($node);
    }


    if ($node->attr('type') == 'password') {
      $this->_flavor->check('password_inputs');
    }


    $this->_html_rewriter->check_form_element($node);

    $safe_attrs = $this->_html_rewriter->node_get_safe_attrs($node);

    return $this->_html_rewriter->render_html_singleton_tag($node->get_tag_name(), $safe_attrs);
 }


  public function tag_script($node) {
    $this->check('script');

    $src = $node->attr('src');

    if($src != null) {
      // FBOPEN:NOTE : including external scripts will not enforce
      // preparsing unless they are fetched, preparsed, stored, and
      // served from your server. You may add this, but
      // the open source version works on inline scripts only.
      $this->_fbjs_impl()->parse_script_include($src);
    }
    else {
      $code = $this->collect_script($node);
      if ($code) {
        $this->_fbjs_impl()->parse_inline_script($code);
      }
    }

    return false;
  }


  public function tag_style($node) {
    $this->_flavor->check('css');
    $children = $node->get_children();
    $css = '';
    foreach ($children as $node) {
      $css .= $node->text_not_escaped();
    }
    if (!isset($this->_render_state['style'])) {
      $this->_render_state['style'] = '';
    }
    $this->_render_state['style'] .= $css;

    return false;
  }
  // Tablestate is a simple dfa to keep track of your position.
  // 0 -> baselevel, 1-> inside of table, 2 -> inside of tr
  // Error if td is wrong at base, Warn if td is wrong anywhere else
  // Error if tr is wrong at base, Warn if tr is wrong anywhere else
  // Error if table is wrong anywhere

  public function tag_table($node) {
    $table = $this->get_env('tablestate', 0); 
    $this->_fbml_env['tablestate'] =  1;
    if ($table == 2) {
      //throw new FBMLRenderException("failed table ");
      $ret = '<td>' . $this->_html_rewriter->node_open($node);
      $ret .= $node->render_children($this);
      $ret .= $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
      $ret .= '</td>';
        $this->_fbml_env['tablestate'] = 2;
    } else if ($table == 1) {
      $ret = '<tr><td>' . $this->_html_rewriter->node_open($node);
      $ret .= $node->render_children($this);
      $ret .= $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
      $ret .= '</td></tr>';
      $this->_fbml_env['tablestate'] = 1;
    } else {
      $ret = $this->_html_rewriter->node_open($node);
      $ret .= $node->render_children($this);
      $ret .= $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
      $this->_fbml_env['tablestate'] = 0;
    }
    return $ret;
  }

  public function tag_td($node) {
    // Check if this is allowed in a table
    $this->_fbml_env['tablestate'] = 0;
    $ret = $this->_html_rewriter->node_open($node);
    $ret .= $node->render_children($this);
    $ret .= $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
    $this->_fbml_env['tablestate'] = 2;
    return $ret;
  }

  public function tag_th($node) {
    // Check if this is allowed in a table
    $this->_fbml_env['tablestate'] = 0;
    $ret = $this->_html_rewriter->node_open($node);
    $ret .= $node->render_children($this);
    $ret .= $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
    $this->_fbml_env['tablestate'] = 2;
    return $ret;
  }

  public function tag_tr($node) {
    $this->_fbml_env['tablestate'] = 2;
    $ret = $this->_html_rewriter->node_open($node);
    $ret .= $node->render_children($this);
    $ret .= $this->_html_rewriter->render_html_close_tag($node->get_tag_name());
    $this->_fbml_env['tablestate'] = 1;
    return $ret;
  }



  public function open_abbr($node) {
    $this->_flavor->check('spans');
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_acronym($node) {
    $this->_flavor->check('spans');
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_address($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_b($node) {
    $this->_flavor->check('bold');
    return $this->_html_rewriter->node_open($node);
  }


  public function open_bdo($node) {
    $this->_flavor->check('bdo');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_big($node) {
    $this->_flavor->check('html');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_blockquote($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_caption($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_center($node) {
    $this->_flavor->check('html');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_cite($node) {
    $this->_flavor->check('italics');
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_code($node) {
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_dd($node) {
    $this->_flavor->check('lists');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_del($node) {
    $this->_flavor->check('strikethru');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_dfn($node) {
    $this->_flavor->check('italics');
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }


  public function open_div($node) {
    $this->_flavor->check('block_level_elements');
    return  $this->_html_rewriter->node_open($node);

  }

  public function open_dl($node) {
    $this->_flavor->check('lists');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_dt($node) {
    $this->_flavor->check('lists');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_em($node) {
    $this->_flavor->check('bold');
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_fieldset($node) {
    $this->_flavor->check('forms');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_font($node) {
    $this->_flavor->check('html');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_h1($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_h2($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_h3($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_h4($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_h5($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_h6($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_hr($node) {
    $this->_flavor->check('horizontal_rules');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_i($node) {
    $this->_flavor->check('italics');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_ins($node) {
    $this->_flavor->check('underline');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_kbd($node) {
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_label($node) {
    $this->_flavor->check('forms');
    $hrw = $this->_html_rewriter;
    $attrs = $hrw->node_get_safe_attrs($node, true, $this->_flavor->allows('relative_urls'), true);

    // We need to rewrite this ID because the "for" attribute
    // in labels refers to the ID of something else in the form
    if (isset($attrs['for'])) {
      $attrs['for'] = $hrw->prefix_id($attrs['for']);
    }
    return
      $this->_html_rewriter->render_html_open_tag($node->get_tag_name(), $attrs);
  }

  public function open_legend($node) {
    $this->_flavor->check('forms');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_li($node) {
    $this->_flavor->check('lists');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_ol($node) {
    $this->_flavor->check('lists');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_optgroup($node) {
    $this->_flavor->check('forms');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_option($node) {
    $this->_flavor->check('forms');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_p($node) {
    $this->_flavor->check('block_level_elements');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_pre($node) {
    return $this->_html_rewriter->node_open($node);
  }

  public function open_q($node) {
    $this->_flavor->check('html');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_s($node) {
    $this->_flavor->check('strikethru');
    return $this->_html_rewriter->node_open($node);
  }


  public function open_samp($node) {
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_select($node) {
    $this->_flavor->check('forms');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_small($node) {
    $this->_flavor->check('html');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_span($node) {
    $this->_flavor->check('spans');
    return $this->_html_rewriter->node_open($node);
  }


  public function open_strike($node) {
    $this->_flavor->check('strikethru');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_strong($node) {
    $this->_flavor->check('bold');
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_sub($node) {
    $this->_flavor->check('html');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_sup($node) {
    $this->_flavor->check('html');
    return $this->_html_rewriter->node_open($node);
  }


  public function open_table($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_tbody($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }


  public function open_td($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_textarea($node) {
    $this->_flavor->check('forms');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_tfoot($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_th($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_thead($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_tr($node) {
    $this->_flavor->check('tables');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_tt($node) {
    return $this->_html_rewriter->node_open($node);
  }

  public function open_u($node) {
    $this->_flavor->check('underline');
    return $this->_html_rewriter->node_open($node);
  }

  public function open_ul($node) {
    $this->_flavor->check('lists');
    return $this->_html_rewriter->node_open($node);
  }


  /**
   * Variable definition.
   * Similar to italic with default display, though
   * probably belongs to a wider category of character
   * formatting or phrase elements.
   * @martin
   */
  public function open_var($node) {
    $this->_flavor->check('italics');
    $this->_flavor->check('phrases');
    return $this->_html_rewriter->node_open($node);
  }

  /**
   * Renders an FBMLPlaintextNode as HTML or text
   * @param   FBMLPlaintextNode   $node
   * @return  string              HTML
   */
  public function render_plaintext_node($node) {
    return $this->render_plaintext($node->raw_text());
  }

  /**
   * Returns rendered plaintext
   * @param   string   $text
   * @return  string   HTML
   *
   * Based on plaintext fbml_env setting
   */
  public function render_plaintext($text) {

    if ($this->get_env('html_literal_spaces', false, false)) {
      $text_spaces = str_replace(' ', '&nbsp;', $text);
    } else {
      $text_spaces = $text;
    }

    if ($this->get_env('html_literal_newlines', false, false)) {
      $text_newlines = str_replace("\n", '<br />', $text_spaces);
    } else {
      $text_newlines = $text_spaces;
    }

    return $text_newlines;

  }

}

