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


include_once $_SERVER['PHP_ROOT'] . '/lib/display/common.php';

class FBJSParser {
  protected static $instances = array();
  protected $appid = null;
  protected $fbml = null;
  protected $used = false;
  //script_info is an array of script information. For each node in the array, it may
  //contain either an array of 'inline' => <inline script content> key-value pair or
  //an array of 'src' => <url to script> key-value pair.
  protected $script_infos = array();
  private $script_src_count = 0;
  protected $postrendered = false;

  const EVENT_TYPE_PASSIVE = 1; // An event a user might passively trigger
  const EVENT_TYPE_ACTIVE  = 2; // An event a user actively triggers

  const PAREN = 1;
  const BRACKET = 2;
  const PROP_BRACKET = 3;
  const CURLY = 4;

  // protected constructor function. please use the singleton factory
  protected function __construct($appid, $fbml) {
    $this->appid = $appid;
    $this->fbml = $fbml;
    $this->user = (int)$fbml->get_env('user');
  }

  /**
   * Returns a psuedo-singleton object for an application by id
   * @return  fbjs
   */
  public static function singleton($appid, $fbml) {
    if (!isset(self::$instances[$appid])) {
      self::$instances[$appid] = new FBJSParser($appid, $fbml);
    }
    self::$instances[$appid]->fbml = $fbml;
    return self::$instances[$appid];
  }

  public static function sanitize_code($js, $appid) {

    static $lex = null;
    if ($lex) {
      $lex->reset($js);
    } else {
      $lex = new js_lexer($js);
    }

    // We get all the tokens before looping through them, because they can change afterwards
    $tokens = array();
    while ($token = $lex->token()) {
      $tokens[] = $token;
    }

    $new_js = '';
    $bracket_stack = array();
    $prev = null;
    $banned_properties = array(
      '__proto__' => true,
      '__parent__' => true,
      'constructor' => true,
      'caller' => true,
      'watch' => true,
      '__defineGetter__' => true,
      '__defineSetter__' => true);
    foreach ($tokens as $key => $token) {
      if ($token->type == 'IDENTIFIER') {
        if ($token->value == 'arguments') {
          $new_js .= 'arg(arguments)';
        } else if ($prev && $prev->value == 'instanceof' && ($token->value == 'Array' || $token->value == 'Object')) {
          $new_js .= $token->value;
        } else {
          $new_js .= 'a'. $appid .'_'.$token->value;
        }
      } else if ($token->type == 'KEYWORD' && $token->value == 'this') {
        if ($prev && $prev->value == 'new' && $prev->type == 'KEYWORD') {
          $new_js .= '(ref(this))'; // this allows factories to work
        } else {
          $new_js .= 'ref(this)'; // this allows implicit semicolons to work
        }
      } else if ($token->type == 'KEYWORD' && $token->value == 'with') {
        throw new FBMLJSParseError('FBJS: with structures are not supported');
      } else if ($token->type == 'REGEX') {
        // Special case REGEX because we don't parse these correctly in all cases.
        preg_match('#/(.+)/([gims]*)#', $token->value, $matches);
        $new_js .= '(new a'. $appid .'_RegExp(\''.escape_js_quotes($matches[1]).'\',\''.(isset($matches[2]) ? $matches[2] : '').'\'))';
      } else if ($token->value == '[') {
        if ($prev->type == 'IDENTIFIER' || $prev->type == 'PROPERTY' || $prev->type == 'NUMBER' || $prev->value == ']' || $prev->value == ')' || $prev->value == 'this') {
          $new_js .= '[idx(';
          $bracket_stack[] = self::PROP_BRACKET;
        } else {
          $new_js .= '[';
          $bracket_stack[] = self::BRACKET;
        }
      } else if ($token->value == ']') {
        if (!$bracket_stack) {
          throw new FBMLJSParseError($token);
        } else {
          switch (array_pop($bracket_stack)) {
            case self::PROP_BRACKET:
              $new_js .= ')]';
              break;
            case self::BRACKET:
              $new_js .= ']';
              break;
            default:
              throw new FBMLJSParseError($token);
              break;
          }
        }
      } else if ($token->type == 'PROPERTY' && (isset($banned_properties[$token->value]))) {
        $new_js .= '__unknown__';
      } else if (isset($token->property_value) && (isset($banned_properties[$token->property_value]))) {  // see the comment for property_value in the lexer below
        $new_js .= '"__unknown__"';
      } else if ($token->type == 'COMMENT_MULTI_LINE') {
        $new_js .= '/**/';
      } else if ($token->type == 'COMMENT_ONE_LINE') {
        $new_js .= '//';
      } else {
        switch ($token->value) {
          case '{':
            $bracket_stack[] = self::CURLY;
            break;
          case '}':
            if (!$bracket_stack || array_pop($bracket_stack) != self::CURLY) {
              throw new FBMLJSParseError($token);
            }
            break;

          case '(':
            $bracket_stack[] = self::PAREN;
            break;
          case ')':
            if (!$bracket_stack || array_pop($bracket_stack) != self::PAREN) {
              throw new FBMLJSParseError($token);
            }
            break;
        }
        $new_js .= $token->value;
      }
      if (!isset($lex->JUNK[$token->type])) {
        $prev = $token;
      }
    }

    return $new_js;
  }

  public function parse_inline_script($text) {
    $this->used = true;
    $this->script_infos[] = array('inline'=> $text);
  }

  public function parse_script_include($src) {
    if ($this->script_src_count < 5) {
      $this->used = true;
      $this->script_infos[] = array('src'=> $src);
      $this->script_src_count++;
    } else {
      throw new FBMLJSParseError('App exceeded maximum number of script references');
    }
  }

  public function postrender() {
    if (!$this->used) {
      return '';
    }

    // Go through all the inline scripts and sanitize
    $sanitized_scripts = array();
    if ($this->script_infos) {
      foreach ($this->script_infos as $script_info) {
        if (isset($script_info['inline'])) {
          $sanitized_scripts[] = array('inline' => self::sanitize_code($script_info['inline'], $this->appid));
        } else if(isset($script_info['src'])) {
          // FBOPEN:NOTE - if js sources are fetched from outside, these will
          // have to be fetched, cached, sanitized, and stored.  Requests then
          // would need to be directed to your cached version.  The open source
          // code at this point does not support such caching.
          // $sanitized_scripts[] = array('src' => FBJSUrlRef::get_url($script_info['src'], $this->appid, 'js'));
        }
      }
    }

    // If this is our first postrender build some bootstrapping code
    $bootstrap = false;
    if (!$this->postrendered) {
      $bootstrap = 'var app=new fbjs_sandbox('.$this->appid.');';

      $profile = $this->fbml->get_env('profile', false, 0);
      $validation_vars = get_fb_validation_vars(
        array('user' => $this->user),
        $this->appid,
        $profile ? array('profile' => $profile) : array());
      $bootstrap .= 'app.validation_vars='.json_encode($validation_vars).';';
      $context = $this->fbml->add_context();
      $bootstrap .= 'app.context=\''.escape_js_quotes($context).'\';';
      $bootstrap .= 'app.contextd=\''.escape_js_quotes($this->fbml->_contexts[$context]).'\';';
      $bootstrap .= 'app.data='.json_encode(array('user' => $this->user,
                                                  'installed' => $this->user ? is_platform_app_installed($this->appid, $this->user) : false,
                                                  'loggedin' => $this->user ? (bool)api_get_valid_session_key($this->user, $this->appid) : false)).';';
    }

    // Render all inline scripts
    $html = '';
    if ($this->fbml->_flavor->allows('script_onload')) {
      if (!$this->postrendered) {
        $bootstrap .= 'app.bootstrap();';
      }
      foreach ($sanitized_scripts as $script) {
        if (isset($script['inline'])) {
          $html .= render_js_inline($script['inline'])."\n";
        } else {
          $script_include = '<script src="' . $script['src'] . '"></script>';
          $html .= $script_include;
        }
      }
    } else {
      foreach ($sanitized_scripts as $script) {
        if (isset($script['inline'])) {
          $bootstrap .= 'app.pending_bootstraps.push(\''.escape_js_quotes($script['inline']).'\');';
        } else {
          // We don't support script include for this flavor at this time.
          throw new FBMLJSParseError('Cannot allow external script');
        }
      }
    }

    $this->used = false;
    $this->postrendered = true;
    return render_js_inline($bootstrap).$html;
  }

  protected function get_event_type($event) {
    switch ($event) {
      case 'onfocus':
      case 'onclick':
      case 'onmousedown':
      case 'onmouseup':
      case 'ondblclick':
      case 'onchange':
      case 'onreset':
      case 'onselect':
      case 'onsubmit':
      case 'onkeydown':
      case 'onkeypress':
      case 'onkeyup':
        return self::EVENT_TYPE_ACTIVE;

      case 'onblur':
      case 'onload':
      case 'onmouseover':
      case 'onmouseout':
      case 'onmousemove':
      case 'onselectstart':
        return self::EVENT_TYPE_PASSIVE;

      // what to do about these...
      // resize scroll unload
      default:
        return null;
    }
  }

  public function render_event($event, $js) {
    $code = '';
    $event_type = $this->get_event_type($event);
    if ($event_type == self::EVENT_TYPE_PASSIVE && !$this->fbml->_flavor->allows('script_onload')) {
      $code .= 'if (!fbjs_sandbox.instances.a'.$this->appid.'.bootstrapped)return;';
    } else if ($event_type == self::EVENT_TYPE_ACTIVE) {
      $code .= 'fbjs_sandbox.instances.a'.$this->appid.'.bootstrap();';
    } else if (!$event_type) {
      return false;
    }
    $code .= 'return fbjs_dom.eventHandler.call([fbjs_dom.get_instance(this,'.$this->appid.'),function(a'.$this->appid.'_event) {'.self::sanitize_code($js, $this->appid).'},'.$this->appid.'],new fbjs_event(event));';
    $this->used = true;
    return $code;
  }
}

class FBMLJSParseError extends FBMLRenderException {
  function __construct($data) {
    if ($data instanceof lex_token) {
      parent::__construct('Javascript parse error, unexpected: '.$data->type);
    } else {
      parent::__construct($data);
    }
  }
}

// no really... it's crude.
abstract class crude_lexer {

  protected $expression_types = array();
  protected $callbacks = array();
  protected $lineno = 1;
  protected $s_data = null;
  protected $s_index = 0;
  protected $s_tokens = array();

  const PARSE_TYPE_REGEX    = 1;
  const PARSE_TYPE_FUNCTION = 2;

  public function __construct($data) {
    foreach ($this->tokens as $token) {
      $var = 't_'.$token;
      if (isset($this->$var)) {
        $this->expression_types[$token] = self::PARSE_TYPE_REGEX;
        $this->$var = str_replace('/', '\\/', $this->$var);
        if (!is_int(@preg_match('/'.$this->$var.'/mA', ''))) {
          throw new Exception('Token '.$token.': '.$this->$var." contains a regex syntax error.\n");
        }
        $this->$var = '/'.$this->$var.'/mA';
      } else if (method_exists($this, $var)) {
        $this->expression_types[$token] = self::PARSE_TYPE_FUNCTION;
      } else {
        throw new Exception('Token '.$token." not defined.\n");
      }

      $cb = 'cb_'.$token;
      if (method_exists($this, $cb)) {
        $this->callbacks[$token] = 1;
      }
    }

    $this->s_data = $data;
  }

  public function reset($data) {
    $this->s_data = $data;
    $this->s_index = 0;
    $this->s_token = array();
    $this->lineno = 1;
  }

  public function prev($index = 0) {
    $i = count($this->s_tokens)-1-$index;
    return isset($this->s_tokens[$i]) ? $this->s_tokens[$i] : null;
  }

  public function token() {
    foreach ($this->tokens as $expression) {
      $var = 't_'.$expression;
      switch ($this->expression_types[$expression]) {
        case self::PARSE_TYPE_REGEX:
          if (preg_match($this->$var, $this->s_data, $matches, null, $this->s_index)) {
            $value = $matches[0];
            $token = new lex_token($expression, $value, $this->lineno, $this->s_index, $this);
            if (isset($this->callbacks[$expression])) {
              $cb = 'cb_'.$expression;
              if (!($token = $this->$cb($token))) {
                break;
              }
            }
            $this->s_index += strlen($value);
            $this->s_tokens[] = $token;
            return $token;
          }
          break;

        case self::PARSE_TYPE_FUNCTION:
          if ($value = $this->$var($this->s_data, $this->s_index)) {
            $token = new lex_token($expression, $value, $this->lineno, $this->s_index, $this);
            $this->s_index += strlen($value);
            $this->s_tokens[] = $token;
            return $token;
          }
          break;
      }
    }
    return false;
  }
}

class lex_token {
  public $type;
  public $value;
  public $line;
  public $lexpos;
  public $lexer;

  function __construct($type, $value, $line, $lexpos, $lexer) {
    $this->type = $type;
    $this->value = $value;
    $this->line = $line;
    $this->lexpos = $lexpos;
    $this->lexer = $lexer;
  }

  function is($type, $value = null) {
    return $this->type == $type && ($value === null || $this->value == $value);
  }

  function next( ) {
    $tok = $this->lexer->token( );
    return $tok;
  }

  public function __toString( ) {
    return '{'.$this->type.' -> '.$this->value.'}';
  }
}

class js_lexer extends crude_lexer {

  // Not part of the lexer class... These just help us to parse just enough to get the job done.
  public $KEYWORDS = array('break', 'case', 'catch', 'const', 'continue', 'debugger', 'default', 'delete', 'do', 'else', 'export', 'false', 'finally',
                           'for', 'function', 'if', 'import', 'in', 'instanceof', 'new', 'null', 'return', 'switch', 'this',
                           'throw', 'true', 'try', 'typeof', 'var', 'void', 'while', 'with');
  public $OPERATORS = array('LCURLY', 'RCURLY', 'LPAREN', 'RPAREN', 'LBRACKET', 'RBRACKET', 'COMMA', 'LT', 'GT', 'LE', 'GE', 'EQ',
                            'NE', 'TRIPLE_EQUALS', 'NOT_TRIPLE_EQUALS', 'PLUS', 'MINUS', 'TIMES', 'PERCENT', 'INCR', 'DECR', 'LSHIFT',
                            'RSHIFT', 'RSHIFT3', 'BIT_AND', 'BIT_OR', 'BIT_XOR', 'NOT', 'BIT_NOT', 'AND', 'OR', 'QUESTIONMARK', 'COLON',
                            'EQUALS', 'PLUS_EQUALS', 'MINUS_EQUALS', 'TIMES_EQUALS', 'PERCENT_EQUALS', 'LSHIFT_EQUALS', 'RSHIFT_EQUALS',
                            'RSHIFT3_EQUALS', 'BIT_AND_EQUALS', 'BIT_OR_EQUALS', 'BIT_XOR_EQUALS', 'DIV', 'DIV_EQUALS');
  public $JUNK = array('COMMENT_ONE_LINE', 'COMMENT_DOCBLOCK', 'COMMENT_MULTI_LINE', 'WHITESPACE', 'NEWLINE', 'ERROR');

  // Tokens, prioritized in loose order from top to bottom
  protected $tokens = array(
    'WHITESPACE',
    'NEWLINE',
    'COMMENT_ONE_LINE',
    'COMMENT_DOCBLOCK',
    'COMMENT_MULTI_LINE',
    'STRING',
    'NUMBER',
    'IDENTIFIER',
    'LCURLY',
    'RCURLY',
    'LPAREN',
    'RPAREN',
    'LBRACKET',
    'RBRACKET',
    'PERIOD',
    'SEMICOLON',
    'COMMA',
    'COLON',
    'QUESTIONMARK',
    'AND',
    'OR',
    'TRIPLE_EQUALS',
    'NOT_TRIPLE_EQUALS',
    'LE',
    'GE',
    'EQ',
    'NE',
    'INCR',
    'DECR',
    'LSHIFT_EQUALS',
    'LSHIFT',
    'RSHIFT_EQUALS',
    'RSHIFT3_EQUALS',
    'RSHIFT3',
    'RSHIFT',
    'PLUS_EQUALS',
    'MINUS_EQUALS',
    'TIMES_EQUALS',
    'PERCENT_EQUALS',
    'BIT_AND_EQUALS',
    'BIT_OR_EQUALS',
    'BIT_XOR_EQUALS',
    'DIV_EQUALS',
    'DIV',
    'REGEX',
    'LT',
    'GT',
    'PLUS',
    'MINUS',
    'TIMES',
    'PERCENT',
    'BIT_AND',
    'BIT_OR',
    'BIT_XOR',
    'NOT',
    'BIT_NOT',
    'EQUALS',
    'ERROR',
  );

  // Regular expressions for most of the tokens above
  protected $t_WHITESPACE = '[ \\t\\r]+';
  protected $t_STRING = '(\'|\")(?:\\\\.|\\\\[\n\r]|.)*?\\1';
  protected $t_NUMBER = '\\d(?:[xX][a-fA-F0-9]+|\\d*)';
  protected $t_LCURLY = '\\{';
  protected $t_RCURLY = '\\}';
  protected $t_LPAREN = '\\(';
  protected $t_RPAREN = '\\)';
  protected $t_LBRACKET = '\\[';
  protected $t_RBRACKET = '\\]';
  protected $t_PERIOD = '\\.';
  protected $t_SEMICOLON = ';';
  protected $t_COMMA = ',';
  protected $t_LT = '<';
  protected $t_GT = '>';
  protected $t_LE = '<=';
  protected $t_GE = '>=';
  protected $t_EQ = '==';
  protected $t_NE = '!=';
  protected $t_TRIPLE_EQUALS = '===';
  protected $t_NOT_TRIPLE_EQUALS = '!==';
  protected $t_PLUS = '\\+';
  protected $t_MINUS = '-';
  protected $t_TIMES = '\\*';
  protected $t_PERCENT = '%';
  protected $t_INCR = '\\+\\+';
  protected $t_DECR = '--';
  protected $t_LSHIFT = '<<';
  protected $t_RSHIFT = '>>';
  protected $t_RSHIFT3 = '>>>';
  protected $t_BIT_AND = '&';
  protected $t_BIT_OR = '\\|';
  protected $t_BIT_XOR = '\\^';
  protected $t_NOT = '!';
  protected $t_BIT_NOT = '~';
  protected $t_AND = '&&';
  protected $t_OR = '\\|\\|';
  protected $t_QUESTIONMARK = '\\?';
  protected $t_EQUALS = '=';
  protected $t_PLUS_EQUALS = '\\+=';
  protected $t_MINUS_EQUALS = '-=';
  protected $t_TIMES_EQUALS = '\\*=';
  protected $t_PERCENT_EQUALS = '%=';
  protected $t_LSHIFT_EQUALS = '<<=';
  protected $t_RSHIFT_EQUALS = '>>=';
  protected $t_RSHIFT3_EQUALS = '>>>=';
  protected $t_BIT_AND_EQUALS = '&=';
  protected $t_BIT_OR_EQUALS = '\\|=';
  protected $t_BIT_XOR_EQUALS = '\\^=';
  protected $t_ERROR = '.';

  // t_IDENTIFIER
  // We use a callback here to define reserved words
  protected $t_IDENTIFIER = '[a-zA-Z$_][a-zA-Z$_0-9]*';
  protected function cb_IDENTIFIER($token) {
    $i = 0;
    while ($prev = $this->prev($i++)) {
      if (!isset($this->JUNK[$prev->type])) {
        break;
      }
    }
    if ($prev && ($prev->type == 'PERIOD')) {
      $token->type = 'PROPERTY';
    } else if (isset($this->KEYWORDS[$token->value])) {
      $token->type = 'KEYWORD';
    }
    return $token;
  }

  // t_REGEX, t_DIV, t_DIV_EQUALS, t_COMMENT_ONE_LINE, t_COMMENT_MULTI_LINE
  // These are all grouped together because it's difficult to distinguish /regexs/ from math/ematical/division.
  // NOTE: This parsing isn't perfect, because, well... it's not REAL parsing. You can easily break this if
  //       you throw it very malformed Javascript. Garbage in, garbage out. However, the garbage that comes out
  //       should NEVER have a security vulnerability in it.
  protected $t_REGEX = '/(?:\\[(?:[^]\\\\]+|\\\\.)+\\]|\\\\.|.)+?/[A-Za-z]*';
  protected $t_DIV = '/';
  protected $t_COMMENT_DOCBLOCK = '/\\*\\*\s*\n(.|[^a])*?\\*/';
  protected $t_DIV_EQUALS = '/=';
  protected $t_COMMENT_ONE_LINE = '(?://|<!--).*$';
  protected $t_COMMENT_MULTI_LINE = '/\\*(.|[^a])*?\\*/';
  protected function cb_DIV($token) {
    $i = 0;
    while ($prev = $this->prev($i++)) {
      if (!isset($this->JUNK[$prev->type])) {
        break;
      }
    }
    if ($prev) {
      // If there's a / after one of these, we can "safely" assume it's division
      if ($prev->type == 'IDENTIFIER' || $prev->type == 'NUMBER' ||
          $prev->type == 'RBRACKET' || $prev->type == 'STRING' ||
          $prev->type == 'INCR' || $prev->type == 'DECR') {
        return $token;
      // If there's a / after paren, it's a bit trickier...
      } else if ($prev->type == 'RPAREN') {
        $paren_count = 1;
        while (($prev = $this->prev($i++)) && ($paren_count || isset($this->JUNK[$prev->type]))) {
          if ($prev->type == 'LPAREN') {
            $paren_count--;
          } else if ($prev->type == 'RPAREN') {
            $paren_count++;
          }
        }
        if (!$prev || $prev->type != 'KEYWORD' ||
            ($prev->value != 'if' && $prev->value != 'for' && $prev->value != 'while')) {
          return $token;
        }
      }
    }
    return false;
  }
  protected function cb_DIV_EQUALS($token) {
    return $this->cb_DIV($token);
  }

  // t_COLON
  // After we see one of these, trackback and make the identifier before a PROPERTY if it's part of an object
  protected $t_COLON = ':';
  protected function cb_COLON($token) {
    $prev1 = null;
    $i = 0;
    while ($prev = $this->prev($i++)) {
      if (!isset($this->JUNK[$prev->type])) {
        if (!$prev1) {
          $prev1 = $prev;
        } else {
          break;
        }
      }
    }
    if ($prev1 &&
        ($prev  && $prev->type == 'LCURLY' ||
         $prev && $prev->type == 'COMMA')) {
      if ($prev1->type == 'IDENTIFIER') {
        $prev1->type = 'PROPERTY';
      } else if ($prev1->type == 'STRING') {
        // The main goal of this is to catch people building this kind of construct:
        // {'__parent__': w}
        // It doesn't parse all kinds of literals (i.e. \n) but it gets enough to catch __parent__ and __proto__.
        // Also, luckily, the syntax {'__'+'parent__': w} is not valid
        $prev1->property_value = substr($prev1->value, 1, -1); // trim off the quotes
        $prev1->property_value = preg_replace('#(^|[^\\\\])\\\\x([a-f0-9]{2})#ie', '"\1".chr(hexdec("\2"))', $prev1->property_value); // turn \x57 into '_'
        $prev1->property_value = preg_replace('#(^|[^\\\\])\\\\([0-7]{1,3})#e', '"\1".chr(octdec("\2"))', $prev1->property_value); // turn \137 into '_'
        $prev1->property_value = preg_replace('#(^|[^\\\\])\\\\u00([a-f0-9]{2})#ie', '"\1".chr(hexdec("\2"))', $prev1->property_value); // turn \u005f, etc into '_'... doesn't catch other escapes but i'm worried about clobbering utf-8 data
      }
    }
    return $token;
  }

  // t_NEWLINE
  // Not really needed since we don't handle errors or use line numbers
  protected $t_NEWLINE = '\\n+';
  protected function cb_NEWLINE($token) {
    $this->lineno += strlen($token->value);
    return $token;
  }

  protected function cb_COMMENT_MULTI_LINE($token) {
    $this->lineno += substr_count($token->value, "\n");
    return $token;
  }

  function __construct($data) {
    $this->KEYWORDS = array_flip($this->KEYWORDS);
    $this->JUNK = array_flip($this->JUNK);
    $this->OPERATORS = array_flip($this->OPERATORS);
    return parent::__construct($data);
  }
}
