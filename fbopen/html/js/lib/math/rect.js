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
 *  @requires vector
 *  @provides rect
 */

/**
 *  A companion class to Vector2, Rect provides various methods for working
 *  with rectangular areas on screen. This class behaves in a method
 *  substantially similar to Vector2.
 *
 *  @author epriestley
 */
function /* class */ Rect(t, r, b, l, domain) {
  copy_properties(this, {
         t : t,
         r : r,
         b : b,
         l : l,
    domain : domain || 'pure'
  });
};

copy_properties(Rect.prototype, {

  w : function( ) { return this.r - this.l; },
  h : function( ) { return this.b - this.t; },

  area : function( ) {
    return this.w( ) * this.h( );
  },

  toString : function( ) {
    return '(('+this.l+', '+this.t+'), ('+this.r+', '+this.b+'))';
  },

  /**
   *  Returns true if the calling Rect intersects the argument Rect at all,
   *  even on an edge.
   */
  intersects : function(v) {
    v = v.convertTo(this.domain);
    var u = this;
    if (u.l > v.r || v.l > u.r || u.t > v.b || v.t > u.b) {
      return false;
    }
    return true;
  },

  /**
   *  Returns the intersecting area of two rectangles.
   */
  intersectingArea : function(v) {
    v = v.convertTo(this.domain);
    var u = this;

    if (!this.intersects(v)) {
      return null;
    }

    return new Rect(
      Math.max(u.t, v.t),
      Math.min(u.r, v.r),
      Math.min(u.b, v.b),
      Math.max(u.l, v.l)).area( );
  },

  /**
   *  Returns true if the caller completely contains the argument.
   */
  contains : function(v) {
    v = v.convertTo(this.domain);
    var u = this;

    if (v instanceof Vector2) {
      return (u.l <= v.x && u.r >= v.x && u.t <= v.y && u.b >= v.y);
    } else {
      return (u.l <= v.l && u.r >= u.r && u.t <= v.t && u.b >= v.b);
    }
  },

  /**
   *  Returns true if the caller is physically large enough in width and
   *  height to possibly contain the argument.
   */
  canContain : function(v) {
    v = v.convertTo(this.domain);
    return (v.h() <= this.h()) && (v.w() <= this.w());
  },

  /**
   *  If the caller and argument intersect, the caller will be shifted down
   *  vertically until it no longer intersects.
   */
  forceBelow : function(v, min) {
    min = min || 0;
    v = v.convertTo(this.domain);
    if (v.b > this.t) {
      return this.offset(0, (v.b - this.t) + min);
    }
    return this;
  },

  offset : function(x, y) {
    return new Rect(this.t+y, this.r+x, this.b+y, this.l+x, this.domain);
  },

  expand : function(x, y) {
    return new Rect(this.t, this.r+x, this.b+y, this.l, this.domain);
  },

  scale : function(x, y) {
    y = y || x;
    return new Rect(
      this.t,
      this.l+(this.w( )*x),
      this.t+(this.h( )*y),
      this.l,
      this.domain);
  },


  /**
   *  Change the size of a Rect without changing its position.
   */
  setDimensions : function(x, y) {
    return new Rect(
      this.t,
      this.l+x,
      this.t+y,
      this.l,
      this.domain);
  },

  /**
   *  Change the location of a Rect without changing its size.
   */
  setPosition : function(x, y) {
    return new Rect(
      x,
      this.w( ),
      this.h( ),
      y,
      this.domain);
  },

  boundWithin : function(v) {
    if (v.contains(this) || !v.canContain(this)) {
      return this;
    }

    var x = 0, y = 0;
    if (this.l < v.l) {
      x = v.l - this.l;
    } else if (this.r > v.r) {
      x = v.r - this.r;
    }

    if (this.t < v.t) {
      y = v.t - this.t;
    } else if (this.b > v.b) {
      y = v.b - this.b;
    }

    return this.offset(x, y);
  },

  setElementBounds : function(el) {
    this.getPositionVector( ).setElementPosition(el);
    this.getDimensionVector( ).setElementDimensions(el);
    return this;
  },

  getPositionVector : function( ) {
    return new Vector2(this.l, this.t, this.domain);
  },

  getDimensionVector : function( ) {
    return new Vector2(this.w( ), this.h( ), 'pure');
  },

  convertTo : function(newDomain) {
    if (this.domain == newDomain) {
      return this;
    }

    if (newDomain == 'pure') {
      return new Rect(this.t, this.r, this.b, this.l, 'pure');
    }

    if (this.domain == 'pure') {
      Util.error(
        'Unable to convert a pure rect to %q coordinates.',
        newDomain);
      return new Rect(0, 0, 0, 0);
    }

    var p = new Vector2(this.l, this.t, this.domain).convertTo(newDomain);

    return new Rect(p.y, p.x+this.w( ), p.y+this.h( ), p.x, newDomain);
  },

  constrict : function(x, y) {

    if (typeof(y) == 'undefined') {
      y = x;
    }

    x = x || 0;

    return new Rect(this.t + y, this.r - x, this.b - y, this.l + x, this.domain);
  },

  expandX : function( ) {
    return new Rect(this.t, Number.POSITIVE_INFINITY, this.b, Number.NEGATIVE_INFINITY);
  },

  expandY : function( ) {
    return new Rect(number.NEGATIVE_INFINITY, this.r, Number.POSITIVE_INFINITY, this.l);
  }


});


copy_properties(Rect, {
  newFromVectors : function(pos, dim) {
    return new Rect(pos.y, pos.x+dim.x, pos.y+dim.y, pos.x, pos.domain);
  },

  getElementBounds : function(el) {
    return Rect.newFromVectors(
      Vector2.getElementPosition(el),
      Vector2.getElementDimensions(el));
  },

  getViewportBounds : function( ) {
    return Rect.newFromVectors(
      Vector2.getScrollPosition(),
      Vector2.getViewportDimensions());
  },

  getDocumentBounds : function( ) {
    return Rect.newFromVectors(
      new Vector2(0, 0, 'document'),
      Vector2.getDocumentDimensions( ));
  }

});