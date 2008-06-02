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


function editor_two_level_change(selector, subtypes_array, sublabels_array)
{
  selector = ge(selector);
  if ( selector.getAttribute("typefor") )
    subselector = ge(selector.getAttribute("typefor"));

  if ( selector && subselector ) {
    // Clear Old Options
    subselector.options.length = 1;
    type_value = selector.options[selector.selectedIndex].value;

    if ( type_value == "") {
      type_value = -1;
    }

    // Fill with New Options
    index = 1;
    suboptions = subtypes_array[type_value];
    if (typeof(suboptions) != "undefined") {
      for (var key = 0; key < suboptions.length; key++) {
        if (typeof(suboptions[key]) != "undefined") {
          subselector.options[index++] = new Option(suboptions[key], key);
        }
      }
    }

    if (sublabels_array)  {
        if (sublabels_array[type_value]) {
            subselector.options[0] = new Option(sublabels_array[type_value], "");
            subselector.options[0].selected = true;
        } else {
            subselector.options[0] = new Option("---", "");
            subselector.options[0].selected = true;
        }
    }

    // Potentially Disable Subtype Selector
    subselector.disabled = subselector.options.length <= 1;
  }
}

function editor_two_level_set_subselector(subselector, value)
{
  subselector = ge(subselector);
  if ( subselector ) {
    opts = subselector.options;
    for ( var index=0; index < opts.length; index++ ) {
      if ((opts[index].value == value) || ( value === null && opts[index].value == '' )) {
        subselector.selectedIndex = index;
      }
    }
  }
}

function editor_network_change(selector, prefix, orig_value) {
  selector = ge(selector);
  if ( selector && selector.value > 0 ) {
    // these values are hard-coded, which is not great. but it works, which is good.
    show('display_network_message');
  } else {
    hide('display_network_message');
  }
}

function editor_rel_change(selector, prefix, orig_value)
{
  selector = ge(selector);

  for ( var rel_type = 2; rel_type <= 6; rel_type++ ) {
    if ( rel_type == selector.value ) {
      show(prefix+'_new_partner_'+rel_type);
    } else {
      hide(prefix+'_new_partner_'+rel_type);
    }
  }

  // Show New Partner Box
  if ( selector && ge(prefix+'_new_partner') ) {
    if ( selector.value > 1 ) {
      show(prefix+'_new_partner');
    } else {
      hide(prefix+'_new_partner');
    }

  }

  // Cancel or Uncancel Relationship based on new status value
  if ( selector && ge(prefix+'_rel_uncancel') ) {
    if ( selector.value > 1 )
      editor_rel_uncancel(selector, prefix, selector.value);
    else
      editor_rel_cancel(selector, prefix);
  }

  // Toggle Awaiting
  editor_rel_toggle_awaiting(selector, prefix, orig_value);
}

function rel_typeahead_onsubmit() {
  return false;
}

function rel_typeahead_onselect(friend) {
  if (!friend)
    return;
  $('new_partner').value = friend.i;
}

function editor_rel_toggle_awaiting(selector, prefix, orig_value)
{
  // Toggle awaiting or required notices based on orig_value
  selector = ge(selector);
  if ( selector && ge(prefix+'_rel_required') ) {
    if ( selector.value == orig_value ) {
      hide(prefix+'_rel_required');
      show(prefix+'_rel_awaiting');
    }
    else {
      show(prefix+'_rel_required');
      hide(prefix+'_rel_awaiting');
    }
  }
}

function editor_rel_cancel(selector, prefix)
{
  if ( ge(prefix+'_rel_uncancel') )
    show(prefix+'_rel_uncancel');
  if ( ge(prefix+'_rel_cancel') )
    hide(prefix+'_rel_cancel');
  selector = ge(selector);
  if ( ge(selector) && $(selector).selectedIndex > 1 )
    editor_rel_set_value(selector, 1);
}

function editor_rel_uncancel(selector, prefix, rel_value)
{
  if ( ge(prefix+'_rel_uncancel') )
    hide(prefix+'_rel_uncancel');
  if ( ge(prefix+'_rel_cancel') )
    show(prefix+'_rel_cancel');

  if ( rel_value == 4 || rel_value == 5 ) {
    hide(prefix+'_rel_with');
    show(prefix+'_rel_to');
  } else if ( rel_value > 1 ) {
    show(prefix+'_rel_with');
    hide(prefix+'_rel_to');
  }

  if ( ge(selector) && $(selector).selectedIndex <= 1 )
    editor_rel_set_value(selector, rel_value);
  editor_rel_toggle_awaiting(selector, prefix, rel_value);
}

function editor_autocomplete_onselect(result) {
  var hidden=ge(/(.*)_/.exec(this.obj.name)[1] + '_id');
  if (result) {
    hidden.value=result.i==null ? result.t : result.i;
  }
  else {
    hidden.value=-1;
  }
}

function editor_rel_set_value(selector, value)
{
  selector = ge(selector);
  if ( selector ) {
    opts = selector.options;
    opts_length = opts.length;
    for ( var index=0; index < opts_length; index++ ) {
      if ((opts[index].value == value) || ( value === null && opts[index].value == '' )) {
        selector.selectedIndex = index;
      }
    }
  }
}

function enableDisable(gainFocus, loseFocus) {
    loseFocus = ge(loseFocus);
    if (loseFocus) {
        if (loseFocus.value) loseFocus.value = "";
        if (loseFocus.selectedIndex) loseFocus.selectedIndex= 0;
    }
}

function show_editor_error(error_text, exp_text)
{
    $('editor_error_text').innerHTML = error_text;
    $('editor_error_explanation').innerHTML = exp_text;
    show('error');
}

function make_explanation_list(list, num, type) {
  var exp = '';
  if (type == 'missing') {
    if (num == 1) {
      exp = tx('el01', {'thing-1': list[0]});
    } else if (num == 2) {
      exp = tx('el02', {'thing-1': list[0], 'thing-2': list[1]});
    } else if (num == 3) {
      exp = tx('el03', {'thing-1': list[0], 'thing-2': list[1], 'thing-3': list[2]});
    } else if (num == 4) {
      exp = tx('el04', {'thing-1': list[0], 'thing-2': list[1], 'thing-3': list[2], 'thing-4': list[3]});
    } else if (num > 4) {
      exp = tx('el05', {'thing-1': list[0], 'thing-2': list[1], 'thing-3': list[2], 'num': num-3});
    }
  } else if (type == 'bad') {
    if (num == 1) {
      exp = tx('el06', {'thing-1': list[0]});
    } else if (num == 2) {
      exp = tx('el07', {'thing-1': list[0], 'thing-2': list[1]});
    } else if (num == 3) {
      exp = tx('el08', {'thing-1': list[0], 'thing-2': list[1], 'thing-3': list[2]});
    } else if (num == 4) {
      exp = tx('el09', {'thing-1': list[0], 'thing-2': list[1], 'thing-3': list[2], 'thing-4': list[3]});
    } else if (num > 4) {
      exp = tx('el10', {'thing-1': list[0], 'thing-2': list[1], 'thing-3': list[2], 'num': num-3});
    }
  }
  return exp;
}

function TimeSpan(start_prefix, end_prefix, span, auto) {

    // Public Methods

    //gets the timestamp from the start date fields
    this.get_start_ts = function () {
        return _get_date_time_ts(_start_month, _start_day, _start_year,
                _start_hour, _start_min, _start_ampm);
    }

    //gets the current timestamp from the end date fields
    this.get_end_ts = function () {
        var start_ts = _get_date_time_ts(_start_month, _start_day, _start_year,
                _start_hour, _start_min, _start_ampm);
        var end_ts   = _get_date_time_ts(_end_month, _end_day, _end_year,
                _end_hour, _end_min, _end_ampm);
        if (start_ts > end_ts && !(_start_year && _end_year)) {
            //push end_ts to the future by a year
            var future_date = new Date();
            future_date.setTime(end_ts);
            future_date.setFullYear(future_date.getFullYear() + 1);
            return future_date.getTime();
        } else {
            return end_ts;
        }
    }

    // Private Variables and Methods

    var _start_month = ge(start_prefix+'_month');
    var _start_day = ge(start_prefix+'_day');
    var _start_hour = ge(start_prefix+'_hour');
    var _start_year = ge(start_prefix+'_year');
    var _start_min = ge(start_prefix+'_min');
    var _start_ampm = ge(start_prefix+'_ampm');

    var _end_month = ge(end_prefix+'_month');
    var _end_day = ge(end_prefix+'_day');
    var _end_year = ge(end_prefix+'_year');
    var _end_hour = ge(end_prefix+'_hour');
    var _end_min = ge(end_prefix+'_min');
    var _end_ampm = ge(end_prefix+'_ampm');

    var _bottom_touched;
    if (auto) {
        _bottom_touched = false;
    } else {
        _bottom_touched = true;
    }

    var _start_touched  = function() {
        if (!_bottom_touched) {
            _propogate_time_span(_start_month, _start_day, _start_year,
                    _start_hour, _start_min, _start_ampm);
        }
    }

    var _end_touched = function () {
        _bottom_touched = true;
    }

    var _propogate_time_span = function () {
        // 1) make the timestamp
        var start_ts = _get_date_time_ts(_start_month, _start_day, _start_year,
                                          _start_hour, _start_min, _start_ampm);

        // 2) make the offset timeSpan
        var end_ts = start_ts + span * 60000; //60,000 milis in minute

        // 3) propogate the endtime
        _set_date_time_from_ts(end_ts, _end_month, _end_day, _end_year,
                _end_hour, _end_min, _end_ampm);
    }

    var _get_date_time_ts = function (m, d, y, h, min, ampm) {

        var this_date = new Date();
        var date_this_day = this_date.getDate();
        var date_this_month = this_date.getMonth();
        var date_this_year = this_date.getFullYear();

        var month = m.value-1;
        var date = d.value;
        var hour;
        var minutes = min.value;
        var year;

        hour = parseInt(h.value);
        if (ampm.value != '') {
          // am or pm; otherwise this is a 24-hour time
          if (hour == 12) hour = 0;
          if (ampm.value == 'pm') {
              hour = hour + 12;
          }
        }

        //below infers the year from current time
        if (!y) {
            if (month < date_this_month) {
                year = date_this_year + 1;
            } else {
                if (month == date_this_month && date < date_this_day) {
                    year = date_this_year + 1;
                } else {
                    year = date_this_year;
                }
            }
        } else {
            year = y.value;
        }

        var new_date = new Date(year, month, date, hour, minutes, 0, 0);
        var ts = new_date.getTime();

        return ts;
    }

    var _set_date_time_from_ts = function (ts, m, d, y, h, min, ampm) {

        var new_date = new Date();
        new_date.setTime(ts);

        var old_month = m.value;

        var new_month   = new_date.getMonth() + 1; //not zero indexed
        var new_day     = new_date.getDate();
        var new_hour    = new_date.getHours();
        var new_minutes = new_date.getMinutes();
        var new_year    = new_date.getFullYear();
        var new_ampm;

        if (ampm.value != '') {
          if (new_hour > 11) {
              new_ampm = 'pm';
              if (new_hour > 12) {
                  new_hour = new_hour - 12;
              }
          } else {
              if (new_hour == 0) new_hour = 12;
              new_ampm = 'am';
          }
        } else {
          // 24-hour time
          new_ampm = '';
        }


        if (new_minutes < 10) {
            // handle case where new_minutes = "05"
            new_minutes = "0" + new_minutes;
        }

        m.value = new_month;
        d.value = new_day;
        if (y) {
            y.value = new_year;
        }
        h.value = new_hour;
        min.value = new_minutes;
        ampm.value = new_ampm;

        if (old_month != new_month) {
            //changing month, make sure our days are good
            editor_date_month_change(m, d, y ? y : false);
        }

    }

    var _start_month_touched = function() {
        _start_touched();
        editor_date_month_change(_start_month, _start_day, _start_year ? _start_year : false);
    }

    var _end_month_touched = function() {
        _end_touched();
        editor_date_month_change(_end_month, _end_day, _end_year ? _end_year : false);
    }

    //set the event handlers
    _start_month.onchange = _start_month_touched;
    _start_day.onchange = _start_touched;
    if (_start_year) {
        _start_year.onchange = _start_touched;
    }
    _start_hour.onchange = _start_touched;
    _start_min.onchange = _start_touched;
    _start_ampm.onchange = _start_touched;

    _end_month.onchange = _end_month_touched;
    _end_day.onchange = _end_touched;
    if (_end_year) {
        _end_year.onchange = _end_touched;
    }
    _end_hour.onchange = _end_touched;
    _end_min.onchange = _end_touched;
    _end_ampm.onchange = _end_touched;
}

function editor_date_month_change(month_el, day_el, year_el) {
  var month_el = ge(month_el);
  var day_el = ge(day_el);
  var year_el = year_el ? ge(year_el) : false;

  var new_num_days = month_get_num_days(month_el.value, year_el.value && year_el.value!=-1 ? year_el.value : false);
  var b = day_el.options[0].value==-1 ? 1 : 0; // if there's a blank day placeholder to worry about

  for (var i = day_el.options.length; i > new_num_days + b; i--) {
    remove_node(day_el.options[i - 1]);
  }
  for (var i = day_el.options.length; i < new_num_days + b; i++) {
    day_el.options[i] = new Option(i + (b ? 0 : 1));
  }
}

function editor_date_year_change(month, day, year) {
  editor_date_month_change(month, day, year);
}

/* Number of days in a given month and year.
 * If month or year aren't known, we err high (giving the user more days to choose from)
 * by returning 31 days for unknown month, and assuming a leap year for unknown year
 */
function month_get_num_days(month, year) {
  var temp_date;
  if (month == -1) {
    return 31;
  }
  temp_date = new Date(year ? year : 1912, month, 0);
  return temp_date.getDate();
}

function toggleEndWorkSpan(prefix) {
    if (shown(prefix+'_endspan')) {
        hide(prefix+'_endspan');
        show(prefix+'_present');
    } else {
        show(prefix+'_endspan');
        hide(prefix+'_present');
    }
}

function regionCountryChange(label_id, country_id, region_id, label_prefix) {
    switch (country_id) {
        case '326': //canada
            show(region_id);
            $(label_id).innerHTML = label_prefix + tx('el13');
        break;
        case '398': //usa
            show(region_id);
            $(label_id).innerHTML = label_prefix + tx('el12');
        break;
        default:
            $(label_id).innerHTML = label_prefix + tx('el11');
            hide(region_id);
        break;
    }
}

function regionCountryChange_twoLabels(country_label_id, region_label_id, country_id, region_id, label_prefix) {

    show(country_label_id);
    $(country_label_id).innerHTML = label_prefix + tx('el11');

    switch (country_id) {
        case '326': // canada
            show(region_id);
            show(region_label_id);
            $(region_label_id).innerHTML = label_prefix + tx('el13');
        break;
        case '':  // we still show US states when country is blank
        case '398': // usa
            show(region_id);
            show(region_label_id);
            $(region_label_id).innerHTML = label_prefix + tx('el12');
        break;
        default:
            $(region_label_id).innerHTML = label_prefix + tx('el12');
            $(region_id).disabled = true;
        break;
    }

}

// If a user picks a US state but a country isn't chosen, this will
// automatically set the country to US.
// This can happen because we default the country to empty, but still
// populate the region select with US states.
function regionCountyChange_setUSifStateChosen(country_select_id, region_select_id) {
  region_select = ge(region_select_id);
  country_select = ge(country_select_id);
  if (region_select.value != '' &&
      country_select.value == '') {
    country_select.value = 398;
  }
}

function regionCountryChange_restrictions(country_select_id, region_select_id) {
        country_select = ge(country_select_id);
        if (country_select.value == 398) {//ignore U.S. country query
            country_select.value = '';
         } else if (country_select.value == 326) {// ignore Canada country query if province is present
               region_select = ge(region_select_id);
               if (region_select.value) {
                    country_select.value = '';
               }
         }
}

function textLimit(ta, count) {
  var text = ge(ta);
  if (text.value.length > count) {
    text.value = text.value.substring(0,count);
    if (arguments.length>2) { // id of an error block is defined
      $(arguments[2]).style.display='block';
    }
  }
}

function textLimitStrict(text_id, limit, message_id, count_id, submit_id) {
  var text = ge(text_id);
  var len = text.value.length;
  var diff = len - limit;
  if (diff > 0) {
    if (diff > 25000) {
      text.value = text.value.substring(0, limit + 25000);
      diff = 25000;
    }
    $(message_id).style.display='block';
    $(count_id).innerHTML = diff;
    $(submit_id).disabled = true;
  } else if (len == 0) { //empty comment
    $(message_id).style.display = 'none';
    $(submit_id).disabled = true;
    $(count_id).innerHTML = 1;
  } else {
    if ($(count_id).innerHTML != 0) {
      $(count_id).innerHTML = 0;
      $(message_id).style.display = 'none';
      $(submit_id).disabled = false;
    }
  }
}

function calcAge(month_el, day_el, year_el) {
  bYear  = parseInt($(year_el).value);
  bMonth = parseInt($(month_el).value);
  bDay   = parseInt($(day_el).value);

  theDate = new Date();
  year    = theDate.getFullYear();
  month   = theDate.getMonth() + 1;
  day     = theDate.getDate();

  age = year - bYear;
  if ((bMonth > month) || (bMonth == month && day < bDay)) age--;

  return age;
}

function mobile_phone_nag(words, obj, anchor) {
  var nagged = false;
  var callback = function() {
    if (nagged) {
      return;
    }
    for (var i = 0; i < words.length; i++) {
      if ((new RegExp('\\b'+words[i]+'\\b', 'i')).test(obj.value)) {
        nagged = true;
        (new AsyncRequest())
          .setURI('/ajax/mobile_phone_nag.php')
          .setHandler(function(async) {
            var html = async.getPayload();
            if (html) {
              var div = document.createElement('div');
              div.innerHTML = html;
              div.className = 'mobile_nag';
              div.style.display = 'none';
              anchor.parentNode.insertBefore(div, anchor);
              animation(div).blind().show().from('height', 0).to('height', 'auto').go();
            }
          })
          .setReadOnly(true)
          .setOption('suppressErrorHandlerWarning', true)
          .send();
        break;
      }
    }
  }

  addEventBase(obj, 'keyup', callback);
  addEventBase(obj, 'change', callback);
}

function mobile_phone_nag_hide(obj) {
  while (obj.parentNode && obj.className != 'mobile_nag') {
    obj = obj.parentNode;
  }
  obj.parentNode.removeChild(obj);
}
