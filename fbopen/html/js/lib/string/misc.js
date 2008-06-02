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

/**
 *  @provides string-misc
 *  @author jwiseman
 */

/**
 * Given raw text, inserts word breaks ("<wbr/>") into continuous strings of
 * characters longer than wrap_limit.  This allows the text to be displayed
 * in a constrained area without overflowing.  The wrap_limit is character-
 * based, so unless you're using a fixed-width font, you'll have to be
 * conservative to make sure a string like "WWWWWWWW..." isn't too wide.
 * You may pass an optional processing function for the text.  It defaults to
 * htmlize, as you'll usually want to get rid of special HTML characters
 * (except any "<wbr/>" we added).  If for some reason you don't want to
 * htmlize, call the function as:
 *
 * var str_wrapped = html_wordwrap(str_raw, 30, id);
 *
 * If you need a version of wordwrap that takes htmlized-text, feel free to
 * write it.  You'll have to take care to treat "&gt;", etc. as a single
 * character, or else you might insert a "<wbr/>" right in the middle of it.
 *
 * @param  string   str         The string to word wrap
 * @param  int      wrap_limit  Defaults to 60
 * @param  function txt_fn      Optional processing function, defaults to htmlize
 * @return string               Wrapped string
 * @author jwiseman
 */
function html_wordwrap(str, wrap_limit, txt_fn) {
  if (typeof wrap_limit == 'undefined') {
    wrap_limit = 60;
  }
  if (typeof txt_fn != 'function') {
    txt_fn = htmlize;
  }

  // match continuous ranges of non-whitespace characters.
  var regex = new RegExp("\\S{"+(wrap_limit+1)+"}", 'g');

  var start = 0;
  var str_remaining = str;

  // build the return value as an array, then join.  it's faster than lots of
  // string concats.
  var ret_arr = [];

  var matches = str.match(regex);

  if (matches) {
    for (var i = 0; i < matches.length; i++) {
      var match = matches[i];
      var match_index = start + str_remaining.indexOf(match);

      // initial chunk
      var chunk = str.substring(start, match_index);
      if (chunk) {
        ret_arr.push(txt_fn(chunk));
      }

      // long chunk
      ret_arr.push(txt_fn(match) + '<wbr/>');

      // the rest
      start = match_index + match.length;
      str_remaining = str.substring(start);
    }
  }

  // add the rest
  if (str_remaining) {
    ret_arr.push(txt_fn(str_remaining));
  }

  return ret_arr.join('');
}

/**
 * Finds the URLs in a string.
 *
 * @param  string   str  The string to search
 * @return array         The URLs
 * @author jwiseman
 */
function text_get_hyperlinks(str) {
  if (typeof(str) != 'string') {
    return [];
  }
  return str.match(/(?:(?:ht|f)tps?):\/\/[^\s<]*[^\s<\.)]/ig);
}

/**
 * Given raw text, finds all URLs (based on text_get_hyperlinks) and replaces
 * them with anchor tags hyperlinked to the URLs.
 * You may optionally pass functions which process the text as it is parsed
 * by this function.
 * For example, if the resulting text is going to be inserted into the DOM,
 * you'll want to make sure it doesn't contain unintended special characters
 * (other than the anchor tags introduced by this function).  To do this,
 * call the function as:
 *
 * var str_for_display = html_hyperlink(str_raw, htmlize, htmlize);
 *
 * Note that this is the default behavior if you don't specify the processing
 * functions, as you'll almost always want to use htmlize.
 *
 * If you want to htmlize and word wrap the text for display in a small area,
 * call the function as:
 *
 * var process_fn = function(str) {
 *   return html_wordwrap(str, 20, htmlize);
 * };
 * var str_for_display = html_hyperlink(str_raw, process_fn, process_fn);
 *
 * If for some reason you don't want the text htmlized, you can call the
 * function as:
 *
 * var str_for_display = html_hyperlink(str_raw, id, id);
 *
 * @param  string   str     String to process
 * @param  function txt_fn  Optional function for processing chunks of text,
 *                          defaults to htmlize.
 * @param  function url_fn  Optional function for processing the url text
 *                          written inside the new anchor tags, defaults to
 *                          htmlize.
 * @return string           Hyperlinked and processed by the given functions
 * @author jwiseman
 */
function html_hyperlink(str, txt_fn, url_fn) {
  var accepted_delims = {'<':'>', '*':'*', '{':'}', '[':']', "'":"'", '"':'"',
                         '#':'#', '+':'+', '-':'-', '(':')'};

  if (typeof(str) == 'undefined' || !str.toString) {
    return '';
  }
  if (typeof txt_fn != 'function') {
    txt_fn = htmlize;
  }
  if (typeof url_fn != 'function') {
    url_fn = htmlize;
  }

  var str = str.toString();
  var http_matches = text_get_hyperlinks(str);

  var start = 0;
  var str_remaining = str;

  // build the return value as an array, then join.  it's faster than lots of
  // string concats.
  var ret_arr = [];

  var str_remaining = str;

  if (http_matches) {
    for (var i = 0; i < http_matches.length; i++) {
      var http_url = http_matches[i];
      var http_index = start + str_remaining.indexOf(http_url);
      var str_len = http_url.length;

      // NON URL PART
      var non_url = str.substring(start, http_index);
      if (non_url) {
        ret_arr.push(txt_fn(non_url));
      }

      // If the URL string has a delimeter char before it, and its
      // corresponding end char is in the URL, then the URL is actually
      // what's between these two chars.
      var trailing = '';
      if (http_index > 0) {
        var delim = str[http_index-1];
        if (typeof accepted_delims[delim] != 'undefined') {
          var end_delim = accepted_delims[delim];
          var end_delim_index = http_url.indexOf(end_delim);
          if (end_delim_index != -1) {
            trailing = txt_fn(http_url.substring(end_delim_index));
            http_url = http_url.substring(0, end_delim_index);
          }
        }
      }

      // URL PART
      http_str = url_fn(http_url);
      http_url_quote_escape = http_url.replace(/"/g, '%22');
      ret_arr.push('<a href="'+http_url_quote_escape+'" target="_blank" rel="nofollow">'+
                     http_str+
                   '</a>'+trailing);

      start = http_index + str_len;
      str_remaining = str.substring(start);
    }
  }

  // Leftover tail string
  if (str_remaining) {
    ret_arr.push(txt_fn(str_remaining));
  }

  return ret_arr.join('');
}

function nl2br(text) {

  if (typeof(text) == 'undefined' || !text.toString) {
    return '';
  }

  return text
    .toString( )
    .replace( /\n/g, '<br />' );
}

function is_email(email) {
  return /^([\w!.%+\-])+@([\w\-])+(?:\.[\w\-]+)+$/.test(email);
}
