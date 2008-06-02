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
 *  @requires function-extensions
 *  @provides uri
 */

/**
 *  URI parsing and manipulation. The URI class breaks a URI down into its
 *  component parts and allows you to manipulate and rebuild them. It also
 *  allows you to interconvert query strings and objects, and perform
 *  same-origin analysis and coersion.
 *
 *  To analyze a URI:
 *
 *    var uri = new URI('http://www.facebook.com:1234/asdf.php?a=b#anchor');
 *    uri.getProtocol( );   //  http
 *    uri.getDomain( );     //  www.facebook.com
 *    uri.getPort( );       //  1234
 *    uri.getPath( );       //  asdf.php
 *    uri.getQueryData( );  //  {a:'b'}
 *    uri.getFragment( );   //  anchor
 *
 *  To change a URI:
 *
 *    var uri = new URI('http://www.facebook.com/');
 *    uri.setProtocol('gopher');
 *    uri.toString( );    //  gopher://www.facebook.com/
 *
 *  The `URI' class deals with query data by unserializing it into an object,
 *  which acts as a map from query parameter names to values. Two functions
 *  are provided to allow you to use this facility externally: explodeQuery()
 *  and implodeQuery(). The former converts a query string into an object, and
 *  the latter reverses the transformation.
 *
 *  @task   read          Analyzing a URI
 *  @task   write         Changing URIs
 *  @task   query         Managing Query Strings
 *  @task   sameorigin    Working with the Same Origin Policy
 *
 *  @author epriestley
 */
function /* class */ URI(uri) {
  if (uri === window) {
    Util.error('what the hell are you doing');
    return;
  }

  if (this === window) {
    return new URI(uri||window.location.href);
  }

  this.parse(uri||'');
}

copy_properties(URI, {


  /**
   * Returns a URI object for the current window.location.
   */
  getRequestURI : function() {
    return new URI(window.location.href);
  },


  /**
   *  Regular expression describing a URI.
   *
   *  @access protected
   *  @author epriestley
   */
  expression :
    /(((\w+):\/\/)([^\/:]*)(:(\d+))?)?([^#?]*)(\?([^#]*))?(#(.*))?/,


  /**
   *  Convert an HTTP querystring into a Javascript object. This function
   *  is the inverse of implodeQuery().
   *
   *  Note: this doesn't currently support array query syntax. We haven't
   *  needed it yet; write it if you do.
   *
   *  @param  String  HTTP query string, like 'cow=quack&duck=moo'.
   *  @return Object  Map of query keys to values.
   *
   *  @task   query
   *
   *  @access public
   *  @author epriestley
   */
  explodeQuery : function(q) {
    if (!q) {
      return {};
    }
    var ii,t,r = {}; q=q.split('&');
    for (ii = 0, l = q.length; ii < l; ii++) {
      t = q[ii].split('=');
      r[decodeURIComponent(t[0])] = (typeof(t[1])=='undefined')
        ? ''
        : decodeURIComponent(t[1]);
    }
    return r;
  },


  /**
   *  Convert a Javascript object into an HTTP query string. This function is
   *  the inverse of explodeQuery().
   *
   *  @param  Object  Map of query keys to values.
   *  @return String  HTTP query string, like 'cow=quack&duck=moo'.
   *
   *  @task   query
   *
   *  @access public
   *  @author marcel
   */
  implodeQuery : function(obj, name) {
    name = name || '';

    var r = [];

    if (obj instanceof Array) {
      for (var ii = 0; ii < obj.length; ii++) {
        try {
          r.push(URI.implodeQuery(obj[ii], name ? name+'['+ii+']' : ii));
        } catch (ignored) {
          //  Don't care.
        }
      }
    } else if (typeof(obj) == 'object') {
      if (is_node(obj)) {
        r.push('{node}');
      } else {
        for (var k in obj) {
          try {
            r.push(URI.implodeQuery(obj[k], name ? name+'['+k+']' : k));
          } catch (ignored) {
            //  Don't care.
          }
        }
      }
    } else if (name && name.length) {
      r.push(encodeURIComponent(name)+'='+encodeURIComponent(obj));
    } else {
      r.push(encodeURIComponent(obj));
    }

    return r.join('&');
  }

}); // End URI Static Methods

copy_properties(URI.prototype,{


  /**
   *  Set the object's value by parsing a URI.
   *
   *  @param  String  A URI or URI fragment to parse.
   *  @return this
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  parse : function(uri) {
    var m = uri.toString( ).match(URI.expression);
    copy_properties(this,{
      protocol : m[3]||'',
        domain : m[4]||'',
          port : m[6]||'',
          path : m[7]||'',
         query : URI.explodeQuery(m[9]||''),
      fragment : m[11]||''
    });

    return this;
  },


  /**
   *  Set the protocol for a URI.
   *
   *  @param  String  The new protocol.
   *  @return this
   *
   *  @task   write
   *
   *  @access public
   *  @author epriestley
   */
  setProtocol : function(p) {
    this.protocol = p;
    return this;
  },


  /**
   *  Get the protocol of a URI.
   *
   *  @return String  The current protocol.
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  getProtocol : function( ) {
    return this.protocol;
  },


  /**
   *  Replace existing query data with new query data.
   *
   *  @param  Object  Map of query data.
   *  @return this
   *
   *  @task   write
   *
   *  @access public
   *  @author epriestley
   */
  setQueryData : function(o) {
    this.query = o;
    return this;
  },


  /**
   *  Adds some data to the query string of a URI. Note that if you provide
   *  the same key twice, this function will overwrite the old value. This
   *  is a generally useful behavior and makes implementation trivial, but it
   *  makes it technically impossible to construct all legal query strings.
   *
   *  @param  Object  A map of query keys to values.
   *  @return this
   *
   *  @task   write
   *
   *  @access public
   *  @author epriestley
   */
  addQueryData : function(o) {
    return this.setQueryData(copy_properties(this.query, o));
  },


  /**
   *  Retrieves a URI's query data as an object. Use implodeQuery to convert
   *  this to a query string, if necessary.
   *
   *  @return Object  A map of query keys to values.
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  getQueryData : function( ) {
    return this.query;
  },


  /**
   *  Set the fragment of a URI.
   *
   *  @param  String  The new fragment.
   *  @return this
   *
   *  @task   write
   *
   *  @access public
   *  @author epriestley
   */
  setFragment : function(f) {
    this.fragment = f;
    return this;
  },


  /**
   *  Get the (possibly empty) fragment of a URI.
   *
   *  @return String  The current fragment.
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  getFragment : function( ) {
    return this.fragment;
  },


  /**
   *  Set the domain of a URI.
   *
   *  @param  String  The new domain.
   *  @return this
   *
   *  @task   write
   *
   *  @access public
   *  @author epriestley
   */
  setDomain : function(d) {
    this.domain = d;
    return this;
  },


  /**
   *  Get the domain of a URI.
   *
   *  @return String  The current domain.
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  getDomain : function( ) {
    return this.domain;
  },


  /**
   *  Set the port of a URI.
   *
   *  @param  Number  New port number.
   *  @return this
   *
   *  @task   write
   *
   *  @access public
   *  @author epriestley
   */
  setPort : function(p) {
    this.port = p;
    return this;
  },


  /**
   *  Retrieve the port component (which may be empty) of a URI. This will
   *  only give you explicit ports, so you won't get `80' back from a URI like
   *  `http://www.facebook.com/'.
   *
   *  @return String  The current port.
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  getPort : function( ) {
    return this.port;
  },


  /**
   *  Set the path component of a URI.
   *
   *  @param  String  The new path.
   *  @return this
   *
   *  @task   write
   *
   *  @access public
   *  @author epriestley
   */
  setPath : function(p) {
    this.path = p;
    return this;
  },


  /**
   *  Retrieve the path component of a URI (which may be empty).
   *
   *  @return String  The current path.
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  getPath : function( ) {
    return this.path;
  },


  /**
   *  Convert the URI object to a URI string.
   *
   *  @return String  The URI as a string.
   *
   *  @task   read
   *
   *  @access public
   *  @author epriestley
   */
  toString : function( ) {

    var r = '';
    var q = URI.implodeQuery(this.query);

    this.protocol && (r += this.protocol + '://');
    this.domain   && (r += this.domain);
    this.port     && (r += ':' + this.port);

    if (this.domain && !this.path) {
      r += '/';
    }

    this.path     && (r += this.path);
    q             && (r += '?' + q);
    this.fragment && (r += '#' + this.fragment);

    return r;
  },


  /**
   * Returns another URI object that contains only the path, query string,
   * and fragment.
   *
   * @author jrosenstein
   */
  getUnqualifiedURI : function() {
    return new URI(this).setProtocol(null).setDomain(null).setPort(null);
  },


  /**
   * Converts a URI like '/profile.php' into 'http://facebook.com/profile.php'.
   * If the URI already has a domain, then just returns a copy of this.
   *
   * @author jrosenstein
   */
  getQualifiedURI : function() {
    var current = URI();
    var uri = new URI(this);
    if (!uri.getDomain()) {
      uri.setProtocol(current.getProtocol())
         .setDomain(current.getDomain())
         .setPort(current.getPort());
    }
    return uri;
  },


  /**
   *  Check if two URIs belong to the same origin, so that making an XMLHTTP
   *  request from one to the other would satisfy the Same Origin Policy. This
   *  function will assume that URIs which fail to specify a domain or protocol
   *  have the effective correct same-origin value.
   *
   *  @param  URI|String  Optionally, a URI to compare the origin of the caller
   *                      to. If none is provided, the current window location
   *                      will be used.
   *  @return bool        True if the caller has the same origin as the target.
   *
   *  @task   sameorigin
   *
   *  @access public
   *  @author epriestley
   */
  isSameOrigin : function(asThisURI) {
    var uri = asThisURI || window.location.href;
    if (!(uri instanceof URI)) {
      uri = new URI(uri.toString());
    }

    if (this.getProtocol() && this.getProtocol() != uri.getProtocol()) {
      return false;
    }

    if (this.getDomain() && this.getDomain() != uri.getDomain()) {
      return false;
    }

    return true;
  },


  /**
   *  For some URIs, we can coerce them so they satisfy the same origin policy.
   *  For example, `college-a.facebook.com' can safely be converted to a request
   *  to `college-b.facebook.com'. This function attempts to coerce a URI so
   *  that it satisfies the same origin policy.
   *
   *  This function will never coerce protocols, so a HTTPS URI can never be
   *  coerced into an HTTP URI. This is almost certainly the best behavior, but
   *  we may have some cases where we actually do need to do this.
   *
   *  @param  URI|String  Optionally, a target URI to try to coerce this URI
   *                      into having the same origin as. If none is provided
   *                      the current window location will be used.
   *  @return bool        True if the caller has been coerced to the same origin
   *                      as the target.
   *
   *  @task   sameorigin
   *
   *  @access public
   *  @author epriestley
   */
  coerceToSameOrigin : function(targetURI) {
    var uri = targetURI || window.location.href;
    if (!(uri instanceof URI)) {
      uri = new URI(uri.toString( ));
    }

    if (this.isSameOrigin(uri)) {
      return true;
    }

    if (this.getProtocol() != uri.getProtocol()) {
      return false;
    }

    var dst = uri.getDomain().split('.');
    var src = this.getDomain().split('.');

    if (dst.pop( ) == 'com' && src.pop( ) == 'com') {
      if (dst.pop( ) == 'facebook' && src.pop( ) == 'facebook') {

        //  Possibly, we need special casing here for some domains which we
        //  won't be able to coerce, like `m', `register', etc.

        this.setDomain(uri.getDomain( ));
        return true;
      }
    }

    return false;
  }

}); // End URI Methods
