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

var Suggest = function(rootEl, q, formEl, textBoxEl, idEl, uri, param, successHandler, instructions, networkType, placeholderText, defaultOptions, showNoMatches, override_resize) {
  this.onInputChange = function() {
    var currentInputValue = oThis.typeAheadObj.currentInputValue;
    var cache = oThis.getCache(currentInputValue);
    if (cache) {
      oThis.onSuggestRequestDone(currentInputValue, cache[0], cache[1], cache[2]);
    } else {
      var typeStr = "";

      var data = {};
      data[oThis.suggestParam] = currentInputValue;
      if (oThis.networkType) {
        data['t'] = oThis.networkType;
      }

      var asyncRequestGet = new AsyncRequest()
        .setURI(oThis.suggestURI)
        .setData(data)
        .setHandler(function(response) {
          var payload = response.payload;
          oThis.onSuggestRequestDone(currentInputValue, payload.suggestNames, payload.suggestIDs, payload.suggestLocs, oThis.typeAheadObj.pEvent);
        })
        .setErrorHandler(function(response) {
          new Dialog()
            .setTitle(tx('sh:error-occurred'))
            .setBody(tx('su01'))
            .setButtons(Dialog.OK)
            .show();
        })
        .setMethod('GET')
        .setReadOnly(true)
        .send();
    }
  }


  this.onSuggestRequestDone = function(key, names, ids, locs, pEvent) {
    this.setCache(key, names, ids, locs);
    if (this.typeAheadObj.displaySuggestList(names, ids, locs)) {
      this.typeAheadObj.pEvent = pEvent;
      this.typeAheadObj.onListChange();
    }
  }

  this.getCache = function(key) {
    return this.suggestCache[key.toUpperCase()];
  }

  this.setCache = function(key, names, ids, locs) {
    this.suggestCache[key.toUpperCase()] = new Array(names, ids, locs);
  }

  this.init = function() {
    this.suggestURI = uri;
    this.suggestParam = param;
    this.suggestCache = [];
    this.networkType = networkType;
    if (!instructions) {
      instructions = tx('su02');
    }

    textBoxEl.value = q;
    this.typeAheadObj = new TypeAhead(rootEl, formEl, textBoxEl, idEl, defaultOptions, instructions, 0, successHandler, this.onInputChange, null, null, null, placeholderText, showNoMatches, override_resize);
  }

  var oThis = this;
  this.init();
}

function debug(str) {
  document.getElementById("debug").innerHTML += str + "<BR>";
}
