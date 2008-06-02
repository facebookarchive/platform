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
 *  @author   epriestley
 *
 *  @requires function-extensions control-textinput vector
 *  @provides control-textarea
 */

function /* class */ TextAreaControl(textarea) {

  copy_properties(this, {
          autogrow : false,
            shadow : null,
    originalHeight : null,
      metricsValue : null
  });

  this.parent.construct(this, textarea);
};

TextAreaControl.extend(TextInputControl);

copy_properties(TextAreaControl.prototype, {

  setAutogrow : function(autogrow) {
    this.autogrow = autogrow;
    this.refreshShadow();
    return this;
  },

  onupdate : function() {
    this.parent.onupdate();

    var r = this.getRoot();
    if (this.autogrow && r.value != this.metricsValue) {
      this.metricsValue = r.value;

      copy_properties(this.shadow.style, {
          fontSize : parseInt(CSS.getStyle(r, 'fontSize'), 10) + 'px',
        fontFamily : CSS.getStyle(r, 'fontFamily') + 'px',
             width : (Vector2.getElementDimensions(r).x - 8) + 'px'
      });

      DOM.setContent(this.shadow, HTML(htmlize(r.value)));
      r.style.height = Math.max(
        this.originalHeight,
        Vector2.getElementDimensions(this.shadow).y + 15) + 'px';
    }
  },

  refreshShadow : function() {
    if (this.autogrow) {
      this.shadow = $N('div', {className: 'DOMControl_shadow'});
      document.body.appendChild(this.shadow);
      var r = this.getRoot();
      this.originalHeight = parseInt(CSS.getStyle(r, 'height'))
        || Vector2.getElementDimensions(this.getRoot()).y;
    } else {
      if (this.shadow) {
        DOM.remove(this.shadow);
      }
      this.shadow = null;
    }
  }


});


/* -(  Deprecated Textarea APIs  )------------------------------------------- */


function autogrow_textarea(element) {
  element = $(element);
  if (!element._hascontrol) {
    element._hascontrol = true;
    new TextAreaControl(element).setAutogrow(true);
  }
}

function textarea_maxlength(element, length) {
  element = $(element);
  if (!element._hascontrol) {
    element._hascontrol = true;
    new TextAreaControl(element).setMaxLength(length);
  }
}
