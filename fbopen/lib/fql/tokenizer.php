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
 * A tokenizer for parsing FQL with Lemon style parsers
 */
class FQLTokenizer {
  // constant for current token (e.g. FqlParser::TOK_CMP)
  public $token;

  // value of current token (e.g. 'SELECT', 'user', '=', etc)
  public $value;

  // input string to be tokenized
  public $input;

  // length of input string to be to tokenized
  public $inputlen;

  // current position in input string (by character)
  public $pos;

  // array of token constants
  private $tokens_map;

  // regular expression representing all parseable tokens
  private $tokens_re;

  /**
   * Initializes the tokenizer with the input string.
   *
   * @param $input  Input string to tokenize
   */
  public function __construct($input) {
    $this->input = $input;
    $this->inputlen = strlen($input);
    $tokens = $this->tokens();
    $this->tokens_re = '/^(' . implode(')|^(', array_keys($tokens)) . ')/' . ($this->is_case_sensitive() ? '' : 'i');
    $this->tokens_map = array_values($tokens);
    $this->pos = 0;
  }

  /**
   * Tells whether this tokenizer is case sensitive or not
   *
   * @return false
   */
  protected function is_case_sensitive() {
    return false;
  }

  /**
   * Advances the tokenizer by one token in the input stream
   * and returns whether or not there are any more tokens
   *
   * Caller should examine $token and $value for data on the current token
   *
   * @return boolean, true if there are more tokens
   */
  public function next_token() {
    do {
      if ($this->pos == $this->inputlen) {
        // end of input string
        return false;
      }
      if (!preg_match($this->tokens_re,
                      substr($this->input, $this->pos),
                      $matches)) {
        throw new ParserErrorException("unexpected '" . $this->input[$this->pos] . "' at position " . $this->pos . '.');
      }
      $matches = array_filter($matches, 'strlen'); // remove empty sub-patterns
      assert(count($matches)); // if this fails, a rule matched an empty string,
                               // so one of our tokens is bad
      next($matches); // skip global match
      $this->token = $this->tokens_map[key($matches)-1]; // subtract 1 since we don't count the global match
      $this->value = current($matches);
      $this->pos += strlen($this->value);
    } while ($this->token === null);
    return true;
  }

  /**
   * Associative array of token regular expressions matched to
   * the parser constant for the type of expression it represents.
   *
   * @return   array    { <regex> : <token> }
   */
  protected function tokens() {
    return array(
      '[ \r\t\n]+'            => null, // null means to skip over the token
      '"(?:[^"\\\\]|\\\\.)*"' => FqlParser::TOK_STRING, // note: doubly-escaped: once
      "'(?:[^'\\\\]|\\\\.)*'" => FqlParser::TOK_STRING, // for string, once for preg
      '[>=<!]=?|\\^=|!\\^|<>' => FqlParser::TOK_CMP,
      '[+\\-]'                => FqlParser::TOK_ADD_OP,
      '\\*'                   => FqlParser::TOK_STAR,
      '\\/'                   => FqlParser::TOK_SLASH,
      '\\('                   => FqlParser::TOK_OPEN_PAREN,
      '\\)'                   => FqlParser::TOK_CLOSE_PAREN,
      ','                     => FqlParser::TOK_COMMA,
      ';'                     => FqlParser::TOK_SEMICOLON,
      'NOT\b'                 => FqlParser::TOK_NOT,
      'AND\b'                 => FqlParser::TOK_AND,
      'OR\b'                  => FqlParser::TOK_OR,
      'IN\b'                  => FqlParser::TOK_IN,
      'SELECT\b'              => FqlParser::TOK_SELECT,
      'FROM\b'                => FqlParser::TOK_FROM,
      'WHERE\b'               => FqlParser::TOK_WHERE,
      'LIMIT\b'               => FqlParser::TOK_LIMIT,
      'OFFSET\b'              => FqlParser::TOK_OFFSET,
      'ORDER\b'               => FqlParser::TOK_ORDER,
      'BY\b'                  => FqlParser::TOK_BY,
      'DESC\b'                => FqlParser::TOK_DESC,
      'ASC\b'                 => FqlParser::TOK_ASC,
      '[a-z_][a-z0-9_]*(?:\\.[a-z_][a-z0-9_]*)*'
      => FqlParser::TOK_IDENT,
      '[0-9]+'                => FqlParser::TOK_INT,
    );
  }
}


