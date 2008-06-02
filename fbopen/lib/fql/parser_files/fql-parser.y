/**
 * Grammar for FQL.
 * Note: if you make changes to the grammar, please update:
 * http://wiki.developers.facebook.com/index.php/FQL_Grammar
 */
%name FqlParse
%token_prefix TOK_
%declare_class { class FqlParser }
%include_class {
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
}

%parse_failure {
  throw new ParserErrorException('unknown parser failure.');
}
%syntax_error {
  if ($TOKEN === 0) {
    throw new ParserErrorException('unexpected end of query.');
  } else {
    throw new ParserErrorException("unexpected '$TOKEN' at position " .
                                   ($this->tokenizer->pos - strlen($TOKEN)) . '.');
  }
}
%parse_accept {
  //error_log("accepted.  result:\n" . print_r($this->result, true));
}

start ::= statement(A) opt_semicolon. { $this->result = A; }

opt_semicolon ::= SEMICOLON.
opt_semicolon ::= .

statement(A) ::= SELECT star_catching_expr_list(B) FROM IDENT(C) WHERE disjunction(D) opt_order(E) opt_limit(F).
  { A = new FQLStatement(B, C, D, E, F, $this->user, $this->app_id); }
statement ::= SELECT star_catching_expr_list.
  { throw new ParserErrorException('FROM and WHERE clauses are required.'); }
statement ::= SELECT star_catching_expr_list FROM IDENT.
  { throw new ParserErrorException('WHERE clause is required.'); }

opt_order(A) ::= ORDER BY expression(B) opt_desc(C). { A = array(B, C); }
opt_order(A) ::= . { A = null; }
opt_desc(A)  ::= DESC. { A = true; }
opt_desc(A)  ::= ASC. { A = false; }
opt_desc(A)  ::= . { A = false; }

opt_limit(A)  ::= LIMIT INT(B) COMMA INT(C). { A = array(B, C); }
opt_limit(A)  ::= LIMIT INT(B) opt_offset(C). { A = array(C, B); }
opt_limit(A)  ::= . { A = null; }
opt_offset(A) ::= OFFSET INT(B). { A = B; }
opt_offset(A) ::= . { A = null; }

disjunction(A) ::= conjunction(B). { A = B; }
disjunction(A) ::= disjunction(B) OR conjunction(C).  {
  if (B instanceof FQLDisjunction) {
    B->append(C);
    A = B;
  } else {
    A = new FQLDisjunction(B, C);
  }
}

conjunction(A) ::= expression(B). { A = B; }
conjunction(A) ::= conjunction(B) AND expression(C). {
  if (B instanceof FQLConjunction) {
    B->append(C);
    A = B;
  } else {
    A = new FQLConjunction(B, C);
  }
}

expression(A) ::= expression(B) CMP(C) sum(D). { A = new FQLComparisonExpression(C, B, D); }
expression(A) ::= sum(B) IN OPEN_PAREN statement(C) CLOSE_PAREN. { A = new FQLInStatement(B, C); }
expression(A) ::= sum(B) IN OPEN_PAREN expression_list(C) CLOSE_PAREN. { A = new FQLInArray(B, C); }
expression(A) ::= sum(B) IN OPEN_PAREN CLOSE_PAREN. { A = new FQLInArray(B, array()); }
expression(A) ::= sum(B) IN term(C). { A = new FQLInArray(B, array(C)); }
expression(A) ::= sum(B). { A = B; }

sum(A) ::= sum(B) ADD_OP(C) prod(D). { A = new FQLArithmeticExpression(C, B, D); }
sum(A) ::= prod(B). { A = B; }
prod(A) ::= prod(B) STAR(C) big_term(D). { A = new FQLArithmeticExpression(C, B, D); }
prod(A) ::= prod(B) SLASH(C) big_term(D). { A = new FQLArithmeticExpression(C, B, D); }
prod(A) ::= big_term(B). { A = B; }

big_term(A) ::= OPEN_PAREN disjunction(B) CLOSE_PAREN. { A = B; }
big_term(A) ::= NOT big_term(B). { A = new FQLNotExpression(B); }
big_term(A) ::= term(B). { A = B; }

term(A) ::= IDENT(B). { A = new FQLFieldExpression(B); }
term(A) ::= constant(B). { A = B; }
term(A) ::= IDENT(B) OPEN_PAREN expression_list(C) CLOSE_PAREN. { A = new FQLFunction(B, C); }
term(A) ::= IDENT(B) OPEN_PAREN CLOSE_PAREN. { A = new FQLFunction(B, array()); }

constant(A) ::= INT(B). { A = new FQLConstantExpression(B); }
constant(A) ::= STRING(B). { A = new FQLConstantExpression(stripslashes(substr(B, 1, -1))); }

expression_list(A) ::= disjunction(B). { A = array(B); }
expression_list(A) ::= expression_list(B) COMMA disjunction(C). { B []= C;  A = B; }

star_catching_expr_list ::= STAR. { throw new SelectStarException(); }
star_catching_expr_list ::= star_catching_expr_list COMMA STAR. { throw new SelectStarException(); }
star_catching_expr_list(A) ::= disjunction(B). { A = array(B); }
star_catching_expr_list(A) ::= star_catching_expr_list(B) COMMA disjunction(C). { B []= C;  A = B; }
