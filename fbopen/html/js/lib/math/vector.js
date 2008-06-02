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
 *  @requires event-extensions
 *  @provides vector
 */

/**
 *  A two-dimensional (x,y) vector which belongs to some coordinate domain.
 *  This class provides a consistent, reliable mechanism for acquiring,
 *  manipulating, and acting upon position and dimension informations within
 *  a rendered document.
 *
 *  All vectors are fourth-quadrant with an inverted "Y" axis -- that is, (0,0)
 *  is the upper left corner of the relevant coordinate system, and increasing
 *  X and Y values represent points farther toward the right and bottom,
 *  respectively.
 *
 *  Vectors belong to one of three coordinate domains:
 *
 *    pure
 *      A pure vector is a raw numeric vector which does not exist in any
 *      coordinate system. It has some X and Y coordinate, but does not
 *      represent any position on a rendered canvas.
 *
 *    document
 *      A document vector represents a position on a rendered canvas relative
 *      to the upper left corner of the canvas itself -- that is, the entire
 *      renderable area of the canvas, including parts which may not currently
 *      be visible because of the scroll position. The canvas point represented
 *      by a document vector is not affected by scrolling.
 *
 *    viewport
 *      A viewport vector represents a position on the visible area of the
 *      canvas, relative to the upper left corner of the current scroll area.
 *      That is, (0, 0) is the top left visible point, but not necessarily
 *      the top left point in the document (for instance, if the user has
 *      scrolled down the page). Note that vectors in the viewport coordinate
 *      system may legitimately contain negative elements; they represent
 *      points above and/or to the left of the visible area of the document.
 *
 *
 *  When you acquire a position vector, e.g. with Vector2.getEventPosition(),
 *  you MUST provide a coordinate system to represent it in. Methods which act
 *  on vectors MUST first convert them to the expected coordinate system.
 *  Following these rules consistently will prevent code from exhibiting
 *  unexpected behaviors which are a function of the scroll position.
 *
 *  @task canvas Getting Canvas and Event Vectors
 *  @task vector Manipulating Vectors
 *  @task convert Converting Vector Coordinate Domains
 *  @task actions Performing Actions with Vectors
 *  @task internal Internal
 *
 *  @author epriestley
 */
function /* class */ Vector2( x, y, domain) {
  copy_properties(this, {
         x : parseFloat(x),
         y : parseFloat(y),
    domain : domain || 'pure'
  });
};

copy_properties(Vector2.prototype, {


  /**
   *  Convert a vector into a string.
   *
   *  @task vector
   *  @access public
   *  @author epriestley
   */
  toString : function( ) {
    return '('+this.x+', '+this.y+')';
  },


  /**
   *  Add a vector to the caller, returning a new vector. You may pass either
   *  a Vector2, or (x, y) coordinates as numbers:
   *
   *    var u = new Vector2(1, 2);
   *    u.add(new Vector2(2, 3));   // Fine.
   *    u.add(2, 3);                // Also fine.
   *
   *  The resulting vector will have the same coordinate system as the calling
   *  vector!
   *
   *  @param  Vector2|int Vector2, or the X component of a vector.
   *  @param  null|int    Nothing (if specifying a vector) or the Y component of
   *                      a vector.
   *
   *  @returns Vector2 Vectors  sum of the caller and argument.
   *
   *  @task vector
   *  @access public
   *  @author epriestley
   */
  add : function(vx, vy) {

    var x = this.x,
        y = this.y,
        l = arguments.length;

    if (l == 1) {
      if (vx.domain != 'pure') {
        vx = vx.convertTo(this.domain);
      }
      x += vx.x;
      y += vx.y;
    } else if (l == 2) {
      x += parseFloat(vx);
      y += parseFloat(arguments[1]);
    } else {
      Util.warn(
        'Vector2.add called with %d arguments, should be one (a vector) or '   +
        'two (x and y coordinates).',
        l);
    }

    return new Vector2(x, y, this.domain);
  },


  /**
   *  Multiply a vector by a single scalar, or two scalar components.
   *
   *    vect.mul(3);    //  Scale the vector 3x.
   *    vect.mul(1, 2); //  Scale `y' only, by 2x.
   *    vect.mul(1, 0); //  Isolate the `x' component of a vector.
   *
   *  @param  Number    A scalar value to multiply the x coordinate by, or, if
   *                    only one scalar is provided, the x and y coordinates.
   *  @param  Number    An optional scalar to multiply the y coordinate by.
   *
   *  @return Vector2   A result vector.
   *
   *  @task   vector
   *  @access public
   *  @author epriestley
   */
  mul : function(sx, sy) {
    if (typeof(sy) == "undefined") {
      sy = sx;
    }

    return new Vector2(this.x*sx, this.y*sy, this.domain);
  },


  /**
   *  Subtract a vector from the caller, returning a new vector. You may pass
   *  either a Vector2, or (x, y) coordinates as numbers. The resulting vector
   *  will have the same coordinate system as the calling vector!
   *
   *  @task vector
   *  @access public
   *  @author epriestley
   */
  sub : function(v) {
    var x = this.x,
        y = this.y,
        l = arguments.length;

    if (l == 1) {
      if (v.domain != 'pure') {
        v = v.convertTo(this.domain);
      }
      x -= v.x;
      y -= v.y;
    } else if (l == 2) {
      x -= parseFloat(v);
      y -= parseFloat(arguments[1]);
    } else {
      Util.warn(
        'Vector2.add called with %d arguments, should be one (a vector) or '   +
        'two (x and y coordinates).',
        l);
    }

    return new Vector2(x, y, this.domain);
  },


  /**
   *  Return the distance between two vectors.
   *
   *  @task vector
   *  @access public
   *  @author epriestley
   */
  distanceTo : function(v) {
    return this.sub(v).magnitude( );
  },


  /**
   *  Return the magnitude (length) of a vector.
   *
   *  @task vector
   *  @access public
   *  @author epriestley
   */
  magnitude : function( ) {
    return Math.sqrt((this.x*this.x) + (this.y*this.y));
  },


  /**
   *  Convert a vector to viewport coordinates.
   *
   *  @task convert
   *  @access public
   *  @author epriestley
   */
  toViewportCoordinates : function( ) {
    return this.convertTo( 'viewport' );
  },


  /**
   *  Convert a vector to document coordinates.
   *
   *  @task convert
   *  @access public
   *  @author epriestley
   */
  toDocumentCoordinates : function( ) {
    return this.convertTo( 'document' );
  },


  /**
   *  Convert a vector to the specified coordinate system. `viewport' and
   *  `document' vectors may be freely converted, and any vector may be
   *  converted to its own domain or to the `pure' domain. However, it is
   *  impossible to convert a `pure' vector into either the `viewport' or
   *  `document' coordinate systems.
   *
   *  @task convert
   *  @access public
   *  @author epriestley
   */
  convertTo : function(newDomain) {

    if (newDomain != 'pure'     &&
        newDomain != 'viewport' &&
        newDomain != 'document') {
      Util.error(
        'Domain %q is not valid; legitimate coordinate domains are %q, %q, '   +
        '%q.',
        newDomain,
        'pure',
        'viewport',
        'document');
      return new Vector2(0, 0);
    }

    if (newDomain == this.domain) {
      return new Vector2(this.x, this.y, this.domain);
    }

    if (newDomain == 'pure') {
      return new Vector2(this.x, this.y);
    }

    if (this.domain == 'pure') {
      Util.error(
        'Unable to covert a pure vector to %q coordinates; a pure vector is '  +
        'abstract and does not exist in any document coordinate system. If '   +
        'you need to hack around this, create the vector explicitly in some '  +
        'document coordinate domain, by passing a third argument to the '      +
        'constructor. But you probably don\'t, and are just using the class '  +
        'wrong. Stop doing that.',
        newDomain);
      return new Vector2(0, 0);
    }

    // Note that we can't use add/sub here because they call convertTo and
    // we end up with a big mess.
    var o = Vector2.getScrollPosition('document');
    var x = this.x, y = this.y;
    if (this.domain == 'document') {
      // Convert document coords to viewport coords by subtracting the scroll
      // position. This can produce negative values, because document
      // coordinates could be above or to the left of the viewport.
      x -= o.x;
      y -= o.y;
    } else {
      // Convert viewport coords to document coords by adding the scroll
      // position. This can not produce negative values.
      x += o.x;
      y += o.y;
    }

    return new Vector2(x, y, newDomain);
  },

  /**
   *  Set an element's position to the vector position. This is a convenience
   *  method for setting the `top' and `left' style properties of a DOM
   *  element.
   *
   *  @task actions
   *
   *  @param  Node A DOM element to reposition.
   *  @return this
   *
   *  @author epriestley
   */
  setElementPosition : function(el) {
    var p = this.convertTo('document');
    el.style.left = parseInt(p.x) + 'px';
    el.style.top  = parseInt(p.y) + 'px';

    return this;
  },


  /**
   *  Set an element's dimensions to the vector size. This is a convenience
   *  method for setting the `width' and `height' style properties of a DOM
   *  element.
   *
   *  @task actions
   *
   *  @param Node A DOM element to resize.
   *  @return this
   *
   *  @author epriestley
   */
  setElementDimensions : function(el) {
    el.style.width  = parseInt(this.x) + 'px';
    el.style.height = parseInt(this.y) + 'px';

    return this;
  },

  setElementWidth : function(el) {
    el.style.width  = this.x + 'px';

    return this;
  }

}); // End Vector2 Methods



copy_properties(Vector2, {

  compass : {
         east : 'e',
         west : 'w',
        north : 'n',
        south : 's',
       center : 'center',
    northeast : 'ne',
    northwest : 'nw',
    southeast : 'se',
    southwest : 'sw'
  },


  /**
   *  Throw a domain error.
   *
   *  @task internal
   *
   *  @access protected
   *  @author epriestley
   */
  domainError : function( ) {
    Util.error(
      'You MUST provide a coordinate system domain to Vector2.* functions. '   +
      'Available domains are %q and %q. See the documentation for more '       +
      'information.',
      'document',
      'viewport');
  },


  /**
   *  Returns the position of the event (generally, a mouse event) in the
   *  specified domain's coordinate system.
   *
   *  @task canvas
   *  @author epriestley
   */
  getEventPosition : function(e, domain) {
    domain = domain || 'document';
    e = event_get(e);

    var x = e.pageX || (e.clientX +
            (document.documentElement.scrollLeft || document.body.scrollLeft));
    var y = e.pageY || (e.clientY +
            (document.documentElement.scrollTop || document.body.scrollTop));

    return (new Vector2(x, y, 'document')
      .convertTo(domain));
  },


  /**
   *  Returns the current scroll position, in the specified domain's coordinate
   *  system. Note that the scroll position is ALWAYS (0,0) in the viewport
   *  coordinate system, by definition.
   *
   *  @task canvas
   *  @author epriestley
   */
  getScrollPosition : function(domain) {
    domain = domain || 'document';

    var x = document.body.scrollLeft || document.documentElement.scrollLeft;
    var y = document.body.scrollTop  || document.documentElement.scrollTop;

    return (new Vector2(x, y, 'document').convertTo(domain));
  },


  /**
   *  Returns an element's position, in the specified coordinate system. The
   *  returned vector represents the position of its top left point.
   *
   *  @task canvas
   *  @author epriestley
   */
  getElementPosition : function(el, domain) {
    domain = domain || 'document';

    return (new Vector2(elementX(el), elementY(el), 'document')
      .convertTo(domain));
  },


  /**
   *  Returns the dimensions of an element (dimension vectors are pure vectors
   *  and do not require a domain).
   *
   *  @task canvas
   *  @author epriestley
   */
  getElementDimensions : function(el) {

    //  Safari can't figure out the dimensions of a table row, so derive them
    //  from the corners of the first and last cells. This should really grab
    //  TH's, too.

    if (ua.safari() && el.nodeName == 'TR') {
      var tds = el.getElementsByTagName('td');
      var dimensions =
        Vector2
          .getElementCompassPoint(
            tds[tds.length-1],
            Vector2.compass.southeast)
          .sub(Vector2.getElementPosition(tds[0]));

      return dimensions;
    }

    var x = el.offsetWidth   || 0;
    var y = el.offsetHeight  || 0;

    return new Vector2(x, y);
  },


  /**
   *  Returns a compass point on an element. Valid compass points live in
   *  Vector2.compass, and are: northwest, northeast, southeast, southwest,
   *  center, north, east, south, and west.
   *
   *    Vector2.getElementCompassPoint(element, Vector2.compass.northeast);
   *
   *
   *  @param    Element   Element to get the compass point of.
   *  @param    enum      Compass point to retrieve, defined in
   *                      Vector2.compass.
   *
   *  @return   Vector2   The specified compass point of the element.
   *
   *  @task     canvas
   *  @access   public
   *  @author   epriestley
   */
  getElementCompassPoint : function(el, which) {
    which = which || Vector2.compass.southeast;

    var p = Vector2.getElementPosition(el);
    var d = Vector2.getElementDimensions(el);
    var c = Vector2.compass;

    switch (which) {
      case c.east:        return p.add(d.x, d.y*.5);
      case c.west:        return p.add(0, d.y*.5);
      case c.north:       return p.add(d.x*.5, 0);
      case c.south:       return p.add(d.x*.5, d.y);
      case c.center:      return p.add(d.mul(.5));
      case c.northwest:   return p;
      case c.northeast:   return p.add(d.x, 0);
      case c.southwest:   return p.add(0, d.y);
      case c.southeast:   return p.add(d);
    }

    Util.error('Unknown compass point %s.', which);

    return p;
   },


  /**
   *  Returns the dimensions of the viewport (that is, the area of the window
   *  in which page content is visible). Dimension vectors are `pure' vectors
   *  and do not belong to document or viewport domains.
   *
   *  @task canvas
   *  @author epriestley
   */
  getViewportDimensions : function( ) {

    var x =
      (window && window.innerWidth)                                           ||
      (document && document.documentElement
                && document.documentElement.clientWidth)                      ||
      (document && document.body && document.body.clientWidth)                ||
      0;

    var y =
      (window && window.innerHeight)                                          ||
      (document && document.documentElement
                && document.documentElement.clientHeight)                     ||
      (document && document.body && document.body.clientHeight)               ||
      0;

    return new Vector2(x, y);
  },


  /**
   *  Returns the dimensions of the entire document canvas. This includes
   *  whatever page content may not be visible in the current viewport. Like all
   *  dimension vectors, this one exists in the `pure' coordinate system.
   *
   *  @task canvas
   *  @author epriestley
   */
  getDocumentDimensions : function( ) {
    var x =
      (document && document.body && document.body.scrollWidth)                ||
      (document && document.documentElement
                && document.documentElement.scrollWidth)                      ||
      0;

    var y =
      (document && document.body && document.body.scrollHeight)               ||
      (document && document.documentElement
                && document.documentElement.scrollHeight)                     ||
      0;

    return new Vector2(x, y);
  },


  /**
   *  Scroll the document to the specified position.
   *
   *  This could probably be put somewhere better. It would be really nice to
   *  tween this, too, but I'm not going to touch it for now.
   *
   *  @param Vector2 Position to scroll to.
   *
   *  @task actions
   *  @author epriestley
   */
  scrollTo : function(v) {
    if (!(v instanceof Vector2)) {
      v = new Vector2(
        Vector2.getScrollPosition( ).x,
        Vector2.getElementPosition($(v)).y,
        'document');
    }

    v = v.toDocumentCoordinates( );
    if (window.scrollTo) {
      window.scrollTo(v.x, v.y);
    }
  }

}); // End Vector2 Static Methods


var mouseX            = function(e) { return Vector2.getEventPosition(e).x; }
var mouseY            = function(e) { return Vector2.getEventPosition(e).y; }
var pageScrollX       = function() { return Vector2.getScrollPosition().x; }
var pageScrollY       = function() { return Vector2.getScrollPosition().y; }
var getViewportWidth  = function() { return Vector2.getViewportDimensions().x; }
var getViewportHeight = function() { return Vector2.getViewportDimensions().y; }

// Used to fix Opera bug 165620, "scrollLeft, scrollTop on inline elements
// return distances from edges of viewport (transmenu)" (fixed in Opera 9.5).
var operaIgnoreScroll = {'table': true, 'inline-table': true, 'inline': true};

function elementX(obj) {

  if (ua.safari() < 500 && obj.tagName == 'TR') {
    obj = obj.firstChild;
  }

  var left = obj.offsetLeft;
  var op = obj.offsetParent;

  while (obj.parentNode && document.body != obj.parentNode) {
    obj = obj.parentNode;
    if (!(ua.opera() < 9.50) || !operaIgnoreScroll[window.getComputedStyle(obj, '').getPropertyValue('display')]) {
      left -= obj.scrollLeft;
    }
    if (op == obj) {
      // Safari 2.0 doesn't support offset* for table rows
      if (ua.safari() < 500 && obj.tagName == 'TR') {
        left += obj.firstChild.offsetLeft;
      } else {
        left += obj.offsetLeft;
      }
      op = obj.offsetParent;
    }
  }
  return left;
}

function elementY(obj) {

  if (ua.safari() < 500 && obj.tagName == 'TR') {
    obj = obj.firstChild;
  }

  var top = obj.offsetTop;
  var op = obj.offsetParent;
  while (obj.parentNode && document.body != obj.parentNode) {
    obj = obj.parentNode;
    if (!isNaN(obj.scrollTop)) {
      if (!(ua.opera() < 9.50) || !operaIgnoreScroll[window.getComputedStyle(obj, '').getPropertyValue('display')]) {
        top -= obj.scrollTop;
      }
    }
    if (op == obj) {
      // Safari 2.0 doesn't support offset* for table rows
      if (ua.safari() < 500 && obj.tagName == 'TR') {
        top += obj.firstChild.offsetTop;
      } else {
        top += obj.offsetTop;
      }
      op = obj.offsetParent;
    }
  }
  return top;
}
