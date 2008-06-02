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
 * This can be used to store both the string representation of
 * a token, and any useful meta-data associated with the token.
 *
 * meta-data should be stored as an array
 */
class FqlParseyyToken implements ArrayAccess
{
    public $string = '';
    public $metadata = array();

    function __construct($s, $m = array())
    {
        if ($s instanceof FqlParseyyToken) {
            $this->string = $s->string;
            $this->metadata = $s->metadata;
        } else {
            $this->string = (string) $s;
            if ($m instanceof FqlParseyyToken) {
                $this->metadata = $m->metadata;
            } elseif (is_array($m)) {
                $this->metadata = $m;
            }
        }
    }

    function __toString()
    {
        return $this->_string;
    }

    function offsetExists($offset)
    {
        return isset($this->metadata[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    function offsetSet($offset, $value)
    {
        if ($offset === null) {
            if (isset($value[0])) {
                $x = ($value instanceof FqlParseyyToken) ?
                    $value->metadata : $value;
                $this->metadata = array_merge($this->metadata, $x);
                return;
            }
            $offset = count($this->metadata);
        }
        if ($value === null) {
            return;
        }
        if ($value instanceof FqlParseyyToken) {
            if ($value->metadata) {
                $this->metadata[$offset] = $value->metadata;
            }
        } elseif ($value) {
            $this->metadata[$offset] = $value;
        }
    }

    function offsetUnset($offset)
    {
        unset($this->metadata[$offset]);
    }
}

/** The following structure represents a single element of the
 * parser's stack.  Information stored includes:
 *
 *   +  The state number for the parser at this level of the stack.
 *
 *   +  The value of the token stored at this level of the stack.
 *      (In other words, the "major" token.)
 *
 *   +  The semantic value stored at this level of the stack.  This is
 *      the information used by the action routines in the grammar.
 *      It is sometimes called the "minor" token.
 */
class FqlParseyyStackEntry
{
    public $stateno;       /* The state-number */
    public $major;         /* The major token value.  This is the code
                     ** number for the token at this stack level */
    public $minor; /* The user-supplied minor token value.  This
                     ** is the value of the token  */
};

// code external to the class is included here

// declare_class is output here
#line 9 "fql-parser.y"
 class FqlParser #line 102 "fql-parser.php"
{
/* First off, code is included which follows the "include_class" declaration
** in the input file. */
#line 10 "fql-parser.y"

  public $result; // the resulting FQLStatement
  public function __construct($user, $app_id=PLATFORM_APP_ID_FACEBOOK) {
    $this->user = $user;
    $this->app_id = $app_id;

    // This is the initialization code from the generated doParse function,
    // because if we exit our script before ever hitting doParse and we don't
    // do this initialization code, the destructor will freak out.
    $this->yyidx = 0;
    $this->yyerrcnt = -1;
    $x = new FqlParseyyStackEntry;
    $x->stateno = 0;
    $x->major = 0;
    $this->yystack = array();
    array_push($this->yystack, $x);
  }
  private function parse($str) {
    $this->tokenizer = new FqlTokenizer($str);
    while ($this->tokenizer->next_token()) {
      $this->doParse($this->tokenizer->token, $this->tokenizer->value);
    }
    $this->doParse(0, 0);
  }
  public function query($str, $format) {
    $this->parse($str);
    return $this->result->evaluate($format);
  }
  public function query_thrift($str) {
    return $this->query($str, FQLStatement::OUT_FORMAT_THRIFT);
  }
  public function query_xml($str) {
    return $this->query($str, FQLStatement::OUT_FORMAT_XML);
  }
  public function query_numeric($str) {
    return $this->query($str, FQLStatement::OUT_FORMAT_NUMERIC);
  }
  public function get_normalized_query() {
    return $this->result ? $this->result->to_string() : null;
  }
#line 148 "fql-parser.php"

/* Next is all token values, as class constants
*/
/* 
** These constants (all generated automatically by the parser generator)
** specify the various kinds of tokens (terminals) that the parser
** understands. 
**
** Each symbol here is a terminal symbol in the grammar.
*/
    const TOK_SEMICOLON                      =  1;
    const TOK_SELECT                         =  2;
    const TOK_FROM                           =  3;
    const TOK_IDENT                          =  4;
    const TOK_WHERE                          =  5;
    const TOK_ORDER                          =  6;
    const TOK_BY                             =  7;
    const TOK_DESC                           =  8;
    const TOK_ASC                            =  9;
    const TOK_LIMIT                          = 10;
    const TOK_INT                            = 11;
    const TOK_COMMA                          = 12;
    const TOK_OFFSET                         = 13;
    const TOK_OR                             = 14;
    const TOK_AND                            = 15;
    const TOK_CMP                            = 16;
    const TOK_IN                             = 17;
    const TOK_OPEN_PAREN                     = 18;
    const TOK_CLOSE_PAREN                    = 19;
    const TOK_ADD_OP                         = 20;
    const TOK_STAR                           = 21;
    const TOK_SLASH                          = 22;
    const TOK_NOT                            = 23;
    const TOK_STRING                         = 24;
    const YY_NO_ACTION = 120;
    const YY_ACCEPT_ACTION = 119;
    const YY_ERROR_ACTION = 118;

/* Next are that tables used to determine what action to take based on the
** current state and lookahead token.  These tables are used to implement
** functions that take a state number and lookahead value and return an
** action integer.  
**
** Suppose the action integer is N.  Then the action is determined as
** follows
**
**   0 <= N < self::YYNSTATE                              Shift N.  That is,
**                                                        push the lookahead
**                                                        token onto the stack
**                                                        and goto state N.
**
**   self::YYNSTATE <= N < self::YYNSTATE+self::YYNRULE   Reduce by rule N-YYNSTATE.
**
**   N == self::YYNSTATE+self::YYNRULE                    A syntax error has occurred.
**
**   N == self::YYNSTATE+self::YYNRULE+1                  The parser accepts its
**                                                        input. (and concludes parsing)
**
**   N == self::YYNSTATE+self::YYNRULE+2                  No such action.  Denotes unused
**                                                        slots in the yy_action[] table.
**
** The action table is constructed as a single large static array $yy_action.
** Given state S and lookahead X, the action is computed as
**
**      self::$yy_action[self::$yy_shift_ofst[S] + X ]
**
** If the index value self::$yy_shift_ofst[S]+X is out of range or if the value
** self::$yy_lookahead[self::$yy_shift_ofst[S]+X] is not equal to X or if
** self::$yy_shift_ofst[S] is equal to self::YY_SHIFT_USE_DFLT, it means that
** the action is not in the table and that self::$yy_default[S] should be used instead.  
**
** The formula above is for computing the action when the lookahead is
** a terminal symbol.  If the lookahead is a non-terminal (as occurs after
** a reduce action) then the static $yy_reduce_ofst array is used in place of
** the static $yy_shift_ofst array and self::YY_REDUCE_USE_DFLT is used in place of
** self::YY_SHIFT_USE_DFLT.
**
** The following are the tables generated in this section:
**
**  self::$yy_action        A single table containing all actions.
**  self::$yy_lookahead     A table containing the lookahead for each entry in
**                          yy_action.  Used to detect hash collisions.
**  self::$yy_shift_ofst    For each state, the offset into self::$yy_action for
**                          shifting terminals.
**  self::$yy_reduce_ofst   For each state, the offset into self::$yy_action for
**                          shifting non-terminals after a reduce.
**  self::$yy_default       Default action for each state.
*/
    const YY_SZ_ACTTAB = 187;
static public $yy_action = array(
 /*     0 */    34,   61,   70,   37,   56,   67,   30,   13,   15,   38,
 /*    10 */    28,   26,   70,   23,   68,   67,   25,   40,    7,   70,
 /*    20 */    30,   69,   67,   38,   28,   65,   70,   23,   68,   67,
 /*    30 */    37,    3,   70,   30,   63,   67,   38,   28,   27,   70,
 /*    40 */    23,   68,   67,   24,   53,   16,   30,   67,   12,   38,
 /*    50 */    28,   32,   70,   23,   68,   67,    8,   19,   71,    9,
 /*    60 */    30,   49,   20,   38,   28,    1,   70,   23,   68,   67,
 /*    70 */    33,   52,    2,   30,   46,   36,   38,   28,    7,   70,
 /*    80 */    23,   68,   67,   43,    4,   55,   30,  119,   21,   38,
 /*    90 */    28,    8,   70,   23,   68,   67,   10,   30,   45,   31,
 /*   100 */    35,   28,   44,   70,   23,   68,   67,    3,   58,   32,
 /*   110 */     8,   32,   12,   42,   11,   18,   71,   28,   71,   70,
 /*   120 */    23,   68,   67,    5,   50,    5,   48,   29,   14,   52,
 /*   130 */    14,   52,   39,   32,   60,   17,   66,   47,   64,   28,
 /*   140 */    71,   70,   23,   68,   67,   62,    6,    5,   91,   91,
 /*   150 */    51,   91,   14,   52,   91,   32,   41,   32,   70,   23,
 /*   160 */    68,   67,   71,   91,   71,   70,   22,   68,   67,    5,
 /*   170 */    91,    5,   54,   91,   14,   52,   14,   52,   59,   57,
 /*   180 */    91,   91,   91,   91,   91,   91,   11,
    );
    static public $yy_lookahead = array(
 /*     0 */    27,   11,   39,   30,   41,   42,   33,   21,   22,   36,
 /*    10 */    37,   38,   39,   40,   41,   42,   29,   30,   12,   39,
 /*    20 */    33,   41,   42,   36,   37,   19,   39,   40,   41,   42,
 /*    30 */    30,    2,   39,   33,   41,   42,   36,   37,   38,   39,
 /*    40 */    40,   41,   42,   30,   39,   17,   33,   42,   20,   36,
 /*    50 */    37,    4,   39,   40,   41,   42,   14,   30,   11,   15,
 /*    60 */    33,   19,   31,   36,   37,   18,   39,   40,   41,   42,
 /*    70 */    30,   24,   18,   33,   19,    3,   36,   37,   12,   39,
 /*    80 */    40,   41,   42,   30,   12,   19,   33,   26,   27,   36,
 /*    90 */    37,   14,   39,   40,   41,   42,    7,   33,   12,   13,
 /*   100 */    36,   37,    6,   39,   40,   41,   42,    2,   11,    4,
 /*   110 */    14,    4,   20,   33,   16,   11,   11,   37,   11,   39,
 /*   120 */    40,   41,   42,   18,   19,   18,   19,    4,   23,   24,
 /*   130 */    23,   24,   10,    4,   34,   33,    1,   28,   32,   37,
 /*   140 */    11,   39,   40,   41,   42,   35,    5,   18,   43,   43,
 /*   150 */    21,   43,   23,   24,   43,    4,   37,    4,   39,   40,
 /*   160 */    41,   42,   11,   43,   11,   39,   40,   41,   42,   18,
 /*   170 */    43,   18,   21,   43,   23,   24,   23,   24,    8,    9,
 /*   180 */    43,   43,   43,   43,   43,   43,   16,
);
    const YY_SHIFT_USE_DFLT = -15;
    const YY_SHIFT_MAX = 45;
    static public $yy_shift_ofst = array(
 /*     0 */    29,  105,  107,  129,  151,  153,  153,  153,  153,  153,
 /*    10 */   153,  153,  153,  153,  153,  153,   47,  170,   86,   96,
 /*    20 */   122,  135,  -14,  -14,   42,   72,   66,    6,   28,  141,
 /*    30 */    98,  -10,   54,   77,   55,   44,  123,   77,   44,  104,
 /*    40 */    77,   92,   98,   77,   89,   97,
);
    const YY_REDUCE_USE_DFLT = -38;
    const YY_REDUCE_MAX = 21;
    static public $yy_reduce_ofst = array(
 /*     0 */    61,  -27,    0,  -13,   53,   13,   27,   40,   64,   80,
 /*    10 */   102,  119,  126,   -7,  -20,  -37,    5,  100,  110,   31,
 /*    20 */   106,  109,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(2, ),
        /* 1 */ array(2, 4, 11, 18, 19, 23, 24, ),
        /* 2 */ array(4, 11, 18, 19, 23, 24, ),
        /* 3 */ array(4, 11, 18, 21, 23, 24, ),
        /* 4 */ array(4, 11, 18, 21, 23, 24, ),
        /* 5 */ array(4, 11, 18, 23, 24, ),
        /* 6 */ array(4, 11, 18, 23, 24, ),
        /* 7 */ array(4, 11, 18, 23, 24, ),
        /* 8 */ array(4, 11, 18, 23, 24, ),
        /* 9 */ array(4, 11, 18, 23, 24, ),
        /* 10 */ array(4, 11, 18, 23, 24, ),
        /* 11 */ array(4, 11, 18, 23, 24, ),
        /* 12 */ array(4, 11, 18, 23, 24, ),
        /* 13 */ array(4, 11, 18, 23, 24, ),
        /* 14 */ array(4, 11, 18, 23, 24, ),
        /* 15 */ array(4, 11, 18, 23, 24, ),
        /* 16 */ array(4, 11, 18, 24, ),
        /* 17 */ array(8, 9, 16, ),
        /* 18 */ array(12, 13, ),
        /* 19 */ array(6, 14, ),
        /* 20 */ array(10, ),
        /* 21 */ array(1, ),
        /* 22 */ array(21, 22, ),
        /* 23 */ array(21, 22, ),
        /* 24 */ array(14, 19, ),
        /* 25 */ array(3, 12, ),
        /* 26 */ array(12, 19, ),
        /* 27 */ array(12, 19, ),
        /* 28 */ array(17, 20, ),
        /* 29 */ array(5, ),
        /* 30 */ array(16, ),
        /* 31 */ array(11, ),
        /* 32 */ array(18, ),
        /* 33 */ array(14, ),
        /* 34 */ array(19, ),
        /* 35 */ array(15, ),
        /* 36 */ array(4, ),
        /* 37 */ array(14, ),
        /* 38 */ array(15, ),
        /* 39 */ array(11, ),
        /* 40 */ array(14, ),
        /* 41 */ array(20, ),
        /* 42 */ array(16, ),
        /* 43 */ array(14, ),
        /* 44 */ array(7, ),
        /* 45 */ array(11, ),
        /* 46 */ array(),
        /* 47 */ array(),
        /* 48 */ array(),
        /* 49 */ array(),
        /* 50 */ array(),
        /* 51 */ array(),
        /* 52 */ array(),
        /* 53 */ array(),
        /* 54 */ array(),
        /* 55 */ array(),
        /* 56 */ array(),
        /* 57 */ array(),
        /* 58 */ array(),
        /* 59 */ array(),
        /* 60 */ array(),
        /* 61 */ array(),
        /* 62 */ array(),
        /* 63 */ array(),
        /* 64 */ array(),
        /* 65 */ array(),
        /* 66 */ array(),
        /* 67 */ array(),
        /* 68 */ array(),
        /* 69 */ array(),
        /* 70 */ array(),
        /* 71 */ array(),
);
    static public $yy_default = array(
 /*     0 */   118,  118,  118,  118,  118,  118,  118,  118,  118,  118,
 /*    10 */   118,  118,  118,  118,  118,  118,  118,   82,   87,   79,
 /*    20 */    85,   74,   98,   99,  118,   76,  118,  118,   97,   77,
 /*    30 */    90,  118,  106,  113,  118,   89,  118,  112,   88,  118,
 /*    40 */   116,   92,   91,  117,  118,  118,   93,   72,  109,  103,
 /*    50 */    95,  114,  111,   96,  115,   94,  101,   81,   83,   80,
 /*    60 */    78,   86,   84,  100,   75,  108,   73,  107,  102,  104,
 /*    70 */   105,  110,
);
/* The next thing included is series of defines which control
** various aspects of the generated parser.
**    self::YYNOCODE      is a number which corresponds
**                        to no legal terminal or nonterminal number.  This
**                        number is used to fill in empty slots of the hash 
**                        table.
**    self::YYFALLBACK    If defined, this indicates that one or more tokens
**                        have fall-back values which should be used if the
**                        original value of the token will not parse.
**    self::YYSTACKDEPTH  is the maximum depth of the parser's stack.
**    self::YYNSTATE      the combined number of states.
**    self::YYNRULE       the number of rules in the grammar
**    self::YYERRORSYMBOL is the code number of the error symbol.  If not
**                        defined, then do no error processing.
*/
    const YYNOCODE = 44;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 72;
    const YYNRULE = 46;
    const YYERRORSYMBOL = 25;
    const YYERRSYMDT = 'yy0';
    const YYFALLBACK = 0;
    /** The next table maps tokens into fallback tokens.  If a construct
     * like the following:
     * 
     *      %fallback ID X Y Z.
     *
     * appears in the grammer, then ID becomes a fallback token for X, Y,
     * and Z.  Whenever one of the tokens X, Y, or Z is input to the parser
     * but it does not parse, the type of the token is changed to ID and
     * the parse is retried before an error is thrown.
     */
    static public $yyFallback = array(
    );
    /**
     * Turn parser tracing on by giving a stream to which to write the trace
     * and a prompt to preface each trace message.  Tracing is turned off
     * by making either argument NULL 
     *
     * Inputs:
     * 
     * - A stream resource to which trace output should be written.
     *   If NULL, then tracing is turned off.
     * - A prefix string written at the beginning of every
     *   line of trace output.  If NULL, then tracing is
     *   turned off.
     *
     * Outputs:
     * 
     * - None.
     * @param resource
     * @param string
     */
    static function Trace($TraceFILE, $zTracePrompt)
    {
        if (!$TraceFILE) {
            $zTracePrompt = 0;
        } elseif (!$zTracePrompt) {
            $TraceFILE = 0;
        }
        self::$yyTraceFILE = $TraceFILE;
        self::$yyTracePrompt = $zTracePrompt;
    }

    /**
     * Output debug information to output (php://output stream)
     */
    static function PrintTrace()
    {
        self::$yyTraceFILE = fopen('php://output', 'w');
        self::$yyTracePrompt = '';
    }

    /**
     * @var resource|0
     */
    static public $yyTraceFILE;
    /**
     * String to prepend to debug output
     * @var string|0
     */
    static public $yyTracePrompt;
    /**
     * @var int
     */
    public $yyidx;                    /* Index of top element in stack */
    /**
     * @var int
     */
    public $yyerrcnt;                 /* Shifts left before out of the error */
    /**
     * @var array
     */
    public $yystack = array();  /* The parser's stack */

    /**
     * For tracing shifts, the names of all terminals and nonterminals
     * are required.  The following table supplies these names
     * @var array
     */
    static public $yyTokenName = array( 
  '$',             'SEMICOLON',     'SELECT',        'FROM',        
  'IDENT',         'WHERE',         'ORDER',         'BY',          
  'DESC',          'ASC',           'LIMIT',         'INT',         
  'COMMA',         'OFFSET',        'OR',            'AND',         
  'CMP',           'IN',            'OPEN_PAREN',    'CLOSE_PAREN', 
  'ADD_OP',        'STAR',          'SLASH',         'NOT',         
  'STRING',        'error',         'start',         'statement',   
  'opt_semicolon',  'star_catching_expr_list',  'disjunction',   'opt_order',   
  'opt_limit',     'expression',    'opt_desc',      'opt_offset',  
  'conjunction',   'sum',           'expression_list',  'term',        
  'prod',          'big_term',      'constant',    
    );

    /**
     * For tracing reduce actions, the names of all rules are required.
     * @var array
     */
    static public $yyRuleName = array(
 /*   0 */ "start ::= statement opt_semicolon",
 /*   1 */ "opt_semicolon ::= SEMICOLON",
 /*   2 */ "opt_semicolon ::=",
 /*   3 */ "statement ::= SELECT star_catching_expr_list FROM IDENT WHERE disjunction opt_order opt_limit",
 /*   4 */ "statement ::= SELECT star_catching_expr_list",
 /*   5 */ "statement ::= SELECT star_catching_expr_list FROM IDENT",
 /*   6 */ "opt_order ::= ORDER BY expression opt_desc",
 /*   7 */ "opt_order ::=",
 /*   8 */ "opt_desc ::= DESC",
 /*   9 */ "opt_desc ::= ASC",
 /*  10 */ "opt_desc ::=",
 /*  11 */ "opt_limit ::= LIMIT INT COMMA INT",
 /*  12 */ "opt_limit ::= LIMIT INT opt_offset",
 /*  13 */ "opt_limit ::=",
 /*  14 */ "opt_offset ::= OFFSET INT",
 /*  15 */ "opt_offset ::=",
 /*  16 */ "disjunction ::= conjunction",
 /*  17 */ "disjunction ::= disjunction OR conjunction",
 /*  18 */ "conjunction ::= expression",
 /*  19 */ "conjunction ::= conjunction AND expression",
 /*  20 */ "expression ::= expression CMP sum",
 /*  21 */ "expression ::= sum IN OPEN_PAREN statement CLOSE_PAREN",
 /*  22 */ "expression ::= sum IN OPEN_PAREN expression_list CLOSE_PAREN",
 /*  23 */ "expression ::= sum IN OPEN_PAREN CLOSE_PAREN",
 /*  24 */ "expression ::= sum IN term",
 /*  25 */ "expression ::= sum",
 /*  26 */ "sum ::= sum ADD_OP prod",
 /*  27 */ "sum ::= prod",
 /*  28 */ "prod ::= prod STAR big_term",
 /*  29 */ "prod ::= prod SLASH big_term",
 /*  30 */ "prod ::= big_term",
 /*  31 */ "big_term ::= OPEN_PAREN disjunction CLOSE_PAREN",
 /*  32 */ "big_term ::= NOT big_term",
 /*  33 */ "big_term ::= term",
 /*  34 */ "term ::= IDENT",
 /*  35 */ "term ::= constant",
 /*  36 */ "term ::= IDENT OPEN_PAREN expression_list CLOSE_PAREN",
 /*  37 */ "term ::= IDENT OPEN_PAREN CLOSE_PAREN",
 /*  38 */ "constant ::= INT",
 /*  39 */ "constant ::= STRING",
 /*  40 */ "expression_list ::= disjunction",
 /*  41 */ "expression_list ::= expression_list COMMA disjunction",
 /*  42 */ "star_catching_expr_list ::= STAR",
 /*  43 */ "star_catching_expr_list ::= star_catching_expr_list COMMA STAR",
 /*  44 */ "star_catching_expr_list ::= disjunction",
 /*  45 */ "star_catching_expr_list ::= star_catching_expr_list COMMA disjunction",
    );

    /**
     * This function returns the symbolic name associated with a token
     * value.
     * @param int
     * @return string
     */
    function tokenName($tokenType)
    {
        if ($tokenType === 0) {
            return 'End of Input';
        }
        if ($tokenType > 0 && $tokenType < count(self::$yyTokenName)) {
            return self::$yyTokenName[$tokenType];
        } else {
            return "Unknown";
        }
    }

    /**
     * The following function deletes the value associated with a
     * symbol.  The symbol can be either a terminal or nonterminal.
     * @param int the symbol code
     * @param mixed the symbol's value
     */
    static function yy_destructor($yymajor, $yypminor)
    {
        switch ($yymajor) {
        /* Here is inserted the actions which take place when a
        ** terminal or non-terminal is destroyed.  This can happen
        ** when the symbol is popped from the stack during a
        ** reduce or during error processing or when a parser is 
        ** being destroyed before it is finished parsing.
        **
        ** Note: during a reduce, the only symbols destroyed are those
        ** which appear on the RHS of the rule, but which are not used
        ** inside the C code.
        */
            default:  break;   /* If no destructor action specified: do nothing */
        }
    }

    /**
     * Pop the parser's stack once.
     *
     * If there is a destructor routine associated with the token which
     * is popped from the stack, then call it.
     *
     * Return the major token number for the symbol popped.
     * @param FqlParseyyParser
     * @return int
     */
    function yy_pop_parser_stack()
    {
        if (!count($this->yystack)) {
            return;
        }
        $yytos = array_pop($this->yystack);
        if (self::$yyTraceFILE && $this->yyidx >= 0) {
            fwrite(self::$yyTraceFILE,
                self::$yyTracePrompt . 'Popping ' . self::$yyTokenName[$yytos->major] .
                    "\n");
        }
        $yymajor = $yytos->major;
        self::yy_destructor($yymajor, $yytos->minor);
        $this->yyidx--;
        return $yymajor;
    }

    /**
     * Deallocate and destroy a parser.  Destructors are all called for
     * all stack elements before shutting the parser down.
     */
    function __destruct()
    {
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        if (is_resource(self::$yyTraceFILE)) {
            fclose(self::$yyTraceFILE);
        }
    }

    /**
     * Based on the current state and parser stack, get a list of all
     * possible lookahead tokens
     * @param int
     * @return array
     */
    function yy_get_expected_tokens($token)
    {
        $state = $this->yystack[$this->yyidx]->stateno;
        $expected = self::$yyExpectedTokens[$state];
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return $expected;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return array_unique($expected);
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate])) {
                        $expected += self::$yyExpectedTokens[$nextstate];
                            if (in_array($token,
                                  self::$yyExpectedTokens[$nextstate], true)) {
                            $this->yyidx = $yyidx;
                            $this->yystack = $stack;
                            return array_unique($expected);
                        }
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new FqlParseyyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return array_unique($expected);
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return $expected;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        return array_unique($expected);
    }

    /**
     * Based on the parser state and current parser stack, determine whether
     * the lookahead token is possible.
     * 
     * The parser will convert the token value to an error token if not.  This
     * catches some unusual edge cases where the parser would fail.
     * @param int
     * @return bool
     */
    function yy_is_expected_token($token)
    {
        if ($token === 0) {
            return true; // 0 is not part of this
        }
        $state = $this->yystack[$this->yyidx]->stateno;
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return true;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return true;
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate]) &&
                          in_array($token, self::$yyExpectedTokens[$nextstate], true)) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        return true;
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new FqlParseyyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        if (!$token) {
                            // end of input: this is valid
                            return true;
                        }
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return false;
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return true;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        $this->yyidx = $yyidx;
        $this->yystack = $stack;
        return true;
    }

    /**
     * Find the appropriate action for a parser given the terminal
     * look-ahead token iLookAhead.
     *
     * If the look-ahead token is YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return YY_NO_ACTION.
     * @param int The look-ahead token
     */
    function yy_find_shift_action($iLookAhead)
    {
        $stateno = $this->yystack[$this->yyidx]->stateno;
     
        /* if ($this->yyidx < 0) return self::YY_NO_ACTION;  */
        if (!isset(self::$yy_shift_ofst[$stateno])) {
            // no shift actions
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_shift_ofst[$stateno];
        if ($i === self::YY_SHIFT_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            if (count(self::$yyFallback) && $iLookAhead < count(self::$yyFallback)
                   && ($iFallback = self::$yyFallback[$iLookAhead]) != 0) {
                if (self::$yyTraceFILE) {
                    fwrite(self::$yyTraceFILE, self::$yyTracePrompt . "FALLBACK " .
                        self::$yyTokenName[$iLookAhead] . " => " .
                        self::$yyTokenName[$iFallback] . "\n");
                }
                return $this->yy_find_shift_action($iFallback);
            }
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Find the appropriate action for a parser given the non-terminal
     * look-ahead token $iLookAhead.
     *
     * If the look-ahead token is self::YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return self::YY_NO_ACTION.
     * @param int Current state number
     * @param int The look-ahead token
     */
    function yy_find_reduce_action($stateno, $iLookAhead)
    {
        /* $stateno = $this->yystack[$this->yyidx]->stateno; */

        if (!isset(self::$yy_reduce_ofst[$stateno])) {
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_reduce_ofst[$stateno];
        if ($i == self::YY_REDUCE_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Perform a shift action.
     * @param int The new state to shift in
     * @param int The major token to shift in
     * @param mixed the minor token to shift in
     */
    function yy_shift($yyNewState, $yyMajor, $yypMinor)
    {
        $this->yyidx++;
        if ($this->yyidx >= self::YYSTACKDEPTH) {
            $this->yyidx--;
            if (self::$yyTraceFILE) {
                fprintf(self::$yyTraceFILE, "%sStack Overflow!\n", self::$yyTracePrompt);
            }
            while ($this->yyidx >= 0) {
                $this->yy_pop_parser_stack();
            }
            /* Here code is inserted which will execute if the parser
            ** stack ever overflows */
            return;
        }
        $yytos = new FqlParseyyStackEntry;
        $yytos->stateno = $yyNewState;
        $yytos->major = $yyMajor;
        $yytos->minor = $yypMinor;
        array_push($this->yystack, $yytos);
        if (self::$yyTraceFILE && $this->yyidx > 0) {
            fprintf(self::$yyTraceFILE, "%sShift %d\n", self::$yyTracePrompt,
                $yyNewState);
            fprintf(self::$yyTraceFILE, "%sStack:", self::$yyTracePrompt);
            for($i = 1; $i <= $this->yyidx; $i++) {
                fprintf(self::$yyTraceFILE, " %s",
                    self::$yyTokenName[$this->yystack[$i]->major]);
            }
            fwrite(self::$yyTraceFILE,"\n");
        }
    }

    /**
     * The following table contains information about every rule that
     * is used during the reduce.
     *
     * <pre>
     * array(
     *  array(
     *   int $lhs;         Symbol on the left-hand side of the rule
     *   int $nrhs;     Number of right-hand side symbols in the rule
     *  ),...
     * );
     * </pre>
     */
    static public $yyRuleInfo = array(
  array( 'lhs' => 26, 'rhs' => 2 ),
  array( 'lhs' => 28, 'rhs' => 1 ),
  array( 'lhs' => 28, 'rhs' => 0 ),
  array( 'lhs' => 27, 'rhs' => 8 ),
  array( 'lhs' => 27, 'rhs' => 2 ),
  array( 'lhs' => 27, 'rhs' => 4 ),
  array( 'lhs' => 31, 'rhs' => 4 ),
  array( 'lhs' => 31, 'rhs' => 0 ),
  array( 'lhs' => 34, 'rhs' => 1 ),
  array( 'lhs' => 34, 'rhs' => 1 ),
  array( 'lhs' => 34, 'rhs' => 0 ),
  array( 'lhs' => 32, 'rhs' => 4 ),
  array( 'lhs' => 32, 'rhs' => 3 ),
  array( 'lhs' => 32, 'rhs' => 0 ),
  array( 'lhs' => 35, 'rhs' => 2 ),
  array( 'lhs' => 35, 'rhs' => 0 ),
  array( 'lhs' => 30, 'rhs' => 1 ),
  array( 'lhs' => 30, 'rhs' => 3 ),
  array( 'lhs' => 36, 'rhs' => 1 ),
  array( 'lhs' => 36, 'rhs' => 3 ),
  array( 'lhs' => 33, 'rhs' => 3 ),
  array( 'lhs' => 33, 'rhs' => 5 ),
  array( 'lhs' => 33, 'rhs' => 5 ),
  array( 'lhs' => 33, 'rhs' => 4 ),
  array( 'lhs' => 33, 'rhs' => 3 ),
  array( 'lhs' => 33, 'rhs' => 1 ),
  array( 'lhs' => 37, 'rhs' => 3 ),
  array( 'lhs' => 37, 'rhs' => 1 ),
  array( 'lhs' => 40, 'rhs' => 3 ),
  array( 'lhs' => 40, 'rhs' => 3 ),
  array( 'lhs' => 40, 'rhs' => 1 ),
  array( 'lhs' => 41, 'rhs' => 3 ),
  array( 'lhs' => 41, 'rhs' => 2 ),
  array( 'lhs' => 41, 'rhs' => 1 ),
  array( 'lhs' => 39, 'rhs' => 1 ),
  array( 'lhs' => 39, 'rhs' => 1 ),
  array( 'lhs' => 39, 'rhs' => 4 ),
  array( 'lhs' => 39, 'rhs' => 3 ),
  array( 'lhs' => 42, 'rhs' => 1 ),
  array( 'lhs' => 42, 'rhs' => 1 ),
  array( 'lhs' => 38, 'rhs' => 1 ),
  array( 'lhs' => 38, 'rhs' => 3 ),
  array( 'lhs' => 29, 'rhs' => 1 ),
  array( 'lhs' => 29, 'rhs' => 3 ),
  array( 'lhs' => 29, 'rhs' => 1 ),
  array( 'lhs' => 29, 'rhs' => 3 ),
    );

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     * 
     * If a rule is not set, it has no handler.
     */
    static public $yyReduceMap = array(
        0 => 0,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        13 => 7,
        15 => 7,
        8 => 8,
        9 => 9,
        10 => 9,
        11 => 11,
        12 => 12,
        14 => 14,
        16 => 14,
        18 => 14,
        25 => 14,
        27 => 14,
        30 => 14,
        33 => 14,
        35 => 14,
        17 => 17,
        19 => 19,
        20 => 20,
        21 => 21,
        22 => 22,
        23 => 23,
        24 => 24,
        26 => 26,
        28 => 26,
        29 => 26,
        31 => 31,
        32 => 32,
        34 => 34,
        36 => 36,
        37 => 37,
        38 => 38,
        39 => 39,
        40 => 40,
        44 => 40,
        41 => 41,
        45 => 41,
        42 => 42,
        43 => 42,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 67 "fql-parser.y"
    function yy_r0(){ $this->result = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1019 "fql-parser.php"
#line 73 "fql-parser.y"
    function yy_r3(){ $this->_retvalue = new FQLStatement($this->yystack[$this->yyidx + -6]->minor, $this->yystack[$this->yyidx + -4]->minor, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor, $this->user, $this->app_id);     }
#line 1022 "fql-parser.php"
#line 75 "fql-parser.y"
    function yy_r4(){ throw new ParserErrorException('FROM and WHERE clauses are required.');     }
#line 1025 "fql-parser.php"
#line 77 "fql-parser.y"
    function yy_r5(){ throw new ParserErrorException('WHERE clause is required.');     }
#line 1028 "fql-parser.php"
#line 79 "fql-parser.y"
    function yy_r6(){ $this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1031 "fql-parser.php"
#line 80 "fql-parser.y"
    function yy_r7(){ $this->_retvalue = null;     }
#line 1034 "fql-parser.php"
#line 81 "fql-parser.y"
    function yy_r8(){ $this->_retvalue = true;     }
#line 1037 "fql-parser.php"
#line 82 "fql-parser.y"
    function yy_r9(){ $this->_retvalue = false;     }
#line 1040 "fql-parser.php"
#line 85 "fql-parser.y"
    function yy_r11(){ $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1043 "fql-parser.php"
#line 86 "fql-parser.y"
    function yy_r12(){ $this->_retvalue = array($this->yystack[$this->yyidx + 0]->minor, $this->yystack[$this->yyidx + -1]->minor);     }
#line 1046 "fql-parser.php"
#line 88 "fql-parser.y"
    function yy_r14(){ $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1049 "fql-parser.php"
#line 92 "fql-parser.y"
    function yy_r17(){
  if ($this->yystack[$this->yyidx + -2]->minor instanceof FQLDisjunction) {
    $this->yystack[$this->yyidx + -2]->minor->append($this->yystack[$this->yyidx + 0]->minor);
    $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
  } else {
    $this->_retvalue = new FQLDisjunction($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
  }
    }
#line 1059 "fql-parser.php"
#line 102 "fql-parser.y"
    function yy_r19(){
  if ($this->yystack[$this->yyidx + -2]->minor instanceof FQLConjunction) {
    $this->yystack[$this->yyidx + -2]->minor->append($this->yystack[$this->yyidx + 0]->minor);
    $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
  } else {
    $this->_retvalue = new FQLConjunction($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);
  }
    }
#line 1069 "fql-parser.php"
#line 111 "fql-parser.y"
    function yy_r20(){ $this->_retvalue = new FQLComparisonExpression($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1072 "fql-parser.php"
#line 112 "fql-parser.y"
    function yy_r21(){ $this->_retvalue = new FQLInStatement($this->yystack[$this->yyidx + -4]->minor, $this->yystack[$this->yyidx + -1]->minor);     }
#line 1075 "fql-parser.php"
#line 113 "fql-parser.y"
    function yy_r22(){ $this->_retvalue = new FQLInArray($this->yystack[$this->yyidx + -4]->minor, $this->yystack[$this->yyidx + -1]->minor);     }
#line 1078 "fql-parser.php"
#line 114 "fql-parser.y"
    function yy_r23(){ $this->_retvalue = new FQLInArray($this->yystack[$this->yyidx + -3]->minor, array());     }
#line 1081 "fql-parser.php"
#line 115 "fql-parser.y"
    function yy_r24(){ $this->_retvalue = new FQLInArray($this->yystack[$this->yyidx + -2]->minor, array($this->yystack[$this->yyidx + 0]->minor));     }
#line 1084 "fql-parser.php"
#line 118 "fql-parser.y"
    function yy_r26(){ $this->_retvalue = new FQLArithmeticExpression($this->yystack[$this->yyidx + -1]->minor, $this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor);     }
#line 1087 "fql-parser.php"
#line 124 "fql-parser.y"
    function yy_r31(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;     }
#line 1090 "fql-parser.php"
#line 125 "fql-parser.y"
    function yy_r32(){ $this->_retvalue = new FQLNotExpression($this->yystack[$this->yyidx + 0]->minor);     }
#line 1093 "fql-parser.php"
#line 128 "fql-parser.y"
    function yy_r34(){ $this->_retvalue = new FQLFieldExpression($this->yystack[$this->yyidx + 0]->minor);     }
#line 1096 "fql-parser.php"
#line 130 "fql-parser.y"
    function yy_r36(){ $this->_retvalue = new FQLFunction($this->yystack[$this->yyidx + -3]->minor, $this->yystack[$this->yyidx + -1]->minor);     }
#line 1099 "fql-parser.php"
#line 131 "fql-parser.y"
    function yy_r37(){ $this->_retvalue = new FQLFunction($this->yystack[$this->yyidx + -2]->minor, array());     }
#line 1102 "fql-parser.php"
#line 133 "fql-parser.y"
    function yy_r38(){ $this->_retvalue = new FQLConstantExpression($this->yystack[$this->yyidx + 0]->minor);     }
#line 1105 "fql-parser.php"
#line 134 "fql-parser.y"
    function yy_r39(){ $this->_retvalue = new FQLConstantExpression(stripslashes(substr($this->yystack[$this->yyidx + 0]->minor, 1, -1)));     }
#line 1108 "fql-parser.php"
#line 136 "fql-parser.y"
    function yy_r40(){ $this->_retvalue = array($this->yystack[$this->yyidx + 0]->minor);     }
#line 1111 "fql-parser.php"
#line 137 "fql-parser.y"
    function yy_r41(){ $this->yystack[$this->yyidx + -2]->minor []= $this->yystack[$this->yyidx + 0]->minor;  $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;     }
#line 1114 "fql-parser.php"
#line 139 "fql-parser.y"
    function yy_r42(){ throw new SelectStarException();     }
#line 1117 "fql-parser.php"

    /**
     * placeholder for the left hand side in a reduce operation.
     * 
     * For a parser with a rule like this:
     * <pre>
     * rule(A) ::= B. { A = 1; }
     * </pre>
     * 
     * The parser will translate to something like:
     * 
     * <code>
     * function yy_r0(){$this->_retvalue = 1;}
     * </code>
     */
    private $_retvalue;

    /**
     * Perform a reduce action and the shift that must immediately
     * follow the reduce.
     * 
     * For a rule such as:
     * 
     * <pre>
     * A ::= B blah C. { dosomething(); }
     * </pre>
     * 
     * This function will first call the action, if any, ("dosomething();" in our
     * example), and then it will pop three states from the stack,
     * one for each entry on the right-hand side of the expression
     * (B, blah, and C in our example rule), and then push the result of the action
     * back on to the stack with the resulting state reduced to (as described in the .out
     * file)
     * @param int Number of the rule by which to reduce
     */
    function yy_reduce($yyruleno)
    {
        //int $yygoto;                     /* The next state */
        //int $yyact;                      /* The next action */
        //mixed $yygotominor;        /* The LHS of the rule reduced */
        //FqlParseyyStackEntry $yymsp;            /* The top of the parser's stack */
        //int $yysize;                     /* Amount to pop the stack */
        $yymsp = $this->yystack[$this->yyidx];
        if (self::$yyTraceFILE && $yyruleno >= 0 
              && $yyruleno < count(self::$yyRuleName)) {
            fprintf(self::$yyTraceFILE, "%sReduce (%d) [%s].\n",
                self::$yyTracePrompt, $yyruleno,
                self::$yyRuleName[$yyruleno]);
        }

        $this->_retvalue = $yy_lefthand_side = null;
        if (array_key_exists($yyruleno, self::$yyReduceMap)) {
            // call the action
            $this->_retvalue = null;
            $this->{'yy_r' . self::$yyReduceMap[$yyruleno]}();
            $yy_lefthand_side = $this->_retvalue;
        }
        $yygoto = self::$yyRuleInfo[$yyruleno]['lhs'];
        $yysize = self::$yyRuleInfo[$yyruleno]['rhs'];
        $this->yyidx -= $yysize;
        for($i = $yysize; $i; $i--) {
            // pop all of the right-hand side parameters
            array_pop($this->yystack);
        }
        $yyact = $this->yy_find_reduce_action($this->yystack[$this->yyidx]->stateno, $yygoto);
        if ($yyact < self::YYNSTATE) {
            /* If we are not debugging and the reduce action popped at least
            ** one element off the stack, then we can push the new element back
            ** onto the stack here, and skip the stack overflow test in yy_shift().
            ** That gives a significant speed improvement. */
            if (!self::$yyTraceFILE && $yysize) {
                $this->yyidx++;
                $x = new FqlParseyyStackEntry;
                $x->stateno = $yyact;
                $x->major = $yygoto;
                $x->minor = $yy_lefthand_side;
                $this->yystack[$this->yyidx] = $x;
            } else {
                $this->yy_shift($yyact, $yygoto, $yy_lefthand_side);
            }
        } elseif ($yyact == self::YYNSTATE + self::YYNRULE + 1) {
            $this->yy_accept();
        }
    }

    /**
     * The following code executes when the parse fails
     * 
     * Code from %parse_fail is inserted here
     */
    function yy_parse_failed()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sFail!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser fails */
#line 52 "fql-parser.y"

  throw new ParserErrorException('unknown parser failure.');
#line 1222 "fql-parser.php"
    }

    /**
     * The following code executes when a syntax error first occurs.
     * 
     * %syntax_error code is inserted here
     * @param int The major type of the error token
     * @param mixed The minor type of the error token
     */
    function yy_syntax_error($yymajor, $TOKEN)
    {
#line 55 "fql-parser.y"

  if ($TOKEN === 0) {
    throw new ParserErrorException('unexpected end of query.');
  } else {
    throw new ParserErrorException("unexpected '$TOKEN' at position " .
                                   ($this->tokenizer->pos - strlen($TOKEN)) . '.');
  }
#line 1243 "fql-parser.php"
    }

    /**
     * The following is executed when the parser accepts
     * 
     * %parse_accept code is inserted here
     */
    function yy_accept()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sAccept!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $stack = $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser accepts */
#line 63 "fql-parser.y"

  //error_log("accepted.  result:\n" . print_r($this->result, true));
#line 1265 "fql-parser.php"
    }

    /**
     * The main parser program.
     * 
     * The first argument is the major token number.  The second is
     * the token value string as scanned from the input.
     *
     * @param int the token number
     * @param mixed the token value
     * @param mixed any extra arguments that should be passed to handlers
     */
    function doParse($yymajor, $yytokenvalue)
    {
//        $yyact;            /* The parser action. */
//        $yyendofinput;     /* True if we are at the end of input */
        $yyerrorhit = 0;   /* True if yymajor has invoked an error */
        
        /* (re)initialize the parser, if necessary */
        if ($this->yyidx === null || $this->yyidx < 0) {
            /* if ($yymajor == 0) return; // not sure why this was here... */
            $this->yyidx = 0;
            $this->yyerrcnt = -1;
            $x = new FqlParseyyStackEntry;
            $x->stateno = 0;
            $x->major = 0;
            $this->yystack = array();
            array_push($this->yystack, $x);
        }
        $yyendofinput = ($yymajor==0);
        
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sInput %s\n",
                self::$yyTracePrompt, self::$yyTokenName[$yymajor]);
        }
        
        do {
            $yyact = $this->yy_find_shift_action($yymajor);
            if ($yymajor < self::YYERRORSYMBOL &&
                  !$this->yy_is_expected_token($yymajor)) {
                // force a syntax error
                $yyact = self::YY_ERROR_ACTION;
            }
            if ($yyact < self::YYNSTATE) {
                $this->yy_shift($yyact, $yymajor, $yytokenvalue);
                $this->yyerrcnt--;
                if ($yyendofinput && $this->yyidx >= 0) {
                    $yymajor = 0;
                } else {
                    $yymajor = self::YYNOCODE;
                }
            } elseif ($yyact < self::YYNSTATE + self::YYNRULE) {
                $this->yy_reduce($yyact - self::YYNSTATE);
            } elseif ($yyact == self::YY_ERROR_ACTION) {
                if (self::$yyTraceFILE) {
                    fprintf(self::$yyTraceFILE, "%sSyntax Error!\n",
                        self::$yyTracePrompt);
                }
                if (self::YYERRORSYMBOL) {
                    /* A syntax error has occurred.
                    ** The response to an error depends upon whether or not the
                    ** grammar defines an error token "ERROR".  
                    **
                    ** This is what we do if the grammar does define ERROR:
                    **
                    **  * Call the %syntax_error function.
                    **
                    **  * Begin popping the stack until we enter a state where
                    **    it is legal to shift the error symbol, then shift
                    **    the error symbol.
                    **
                    **  * Set the error count to three.
                    **
                    **  * Begin accepting and shifting new tokens.  No new error
                    **    processing will occur until three tokens have been
                    **    shifted successfully.
                    **
                    */
                    if ($this->yyerrcnt < 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $yymx = $this->yystack[$this->yyidx]->major;
                    if ($yymx == self::YYERRORSYMBOL || $yyerrorhit ){
                        if (self::$yyTraceFILE) {
                            fprintf(self::$yyTraceFILE, "%sDiscard input token %s\n",
                                self::$yyTracePrompt, self::$yyTokenName[$yymajor]);
                        }
                        $this->yy_destructor($yymajor, $yytokenvalue);
                        $yymajor = self::YYNOCODE;
                    } else {
                        while ($this->yyidx >= 0 &&
                                 $yymx != self::YYERRORSYMBOL &&
        ($yyact = $this->yy_find_shift_action(self::YYERRORSYMBOL)) >= self::YYNSTATE
                              ){
                            $this->yy_pop_parser_stack();
                        }
                        if ($this->yyidx < 0 || $yymajor==0) {
                            $this->yy_destructor($yymajor, $yytokenvalue);
                            $this->yy_parse_failed();
                            $yymajor = self::YYNOCODE;
                        } elseif ($yymx != self::YYERRORSYMBOL) {
                            $u2 = 0;
                            $this->yy_shift($yyact, self::YYERRORSYMBOL, $u2);
                        }
                    }
                    $this->yyerrcnt = 3;
                    $yyerrorhit = 1;
                } else {
                    /* YYERRORSYMBOL is not defined */
                    /* This is what we do if the grammar does not define ERROR:
                    **
                    **  * Report an error message, and throw away the input token.
                    **
                    **  * If the input token is $, then fail the parse.
                    **
                    ** As before, subsequent error messages are suppressed until
                    ** three input tokens have been successfully shifted.
                    */
                    if ($this->yyerrcnt <= 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $this->yyerrcnt = 3;
                    $this->yy_destructor($yymajor, $yytokenvalue);
                    if ($yyendofinput) {
                        $this->yy_parse_failed();
                    }
                    $yymajor = self::YYNOCODE;
                }
            } else {
                $this->yy_accept();
                $yymajor = self::YYNOCODE;
            }            
        } while ($yymajor != self::YYNOCODE && $this->yyidx >= 0);
    }
}
