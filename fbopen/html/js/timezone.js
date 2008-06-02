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

/*
 * Obtains timezone in the form of offset from GMT in mins.
 * This offset is what needs to be added to the local time
 * to get to GMT (and subtracted from GMT to
 * get to local time)
 * @param timestamp - the server timestamp
 * @author eugene
 */
function tz_calculate( timestamp ) {
  var d = new Date();
  var raw_offset = d.getTimezoneOffset() / 30;

  var time_sec  = d.getTime() / 1000;
  // figure out when the user is manually setting the time
  // to deal with timezones ... tsk tsk
  var time_diff = Math.round( ( timestamp - time_sec ) / 1800 );

  var rounded_offset = Math.round( raw_offset + time_diff ) % 48;

  // confine to range [-28, 24], inclusive, corresponding to GMT-12 to GMT+14
  if (rounded_offset == 0) {
    return 0;
  } else if (rounded_offset > 24) {
    rounded_offset -= Math.ceil(rounded_offset / 48) * 48;
  } else if (rounded_offset < -28) {
    rounded_offset += Math.ceil(rounded_offset / -48) * 48;
  }

  return rounded_offset * 30;
}

/*
 * Given a timezone form, submits it, calling
 * tz_calculate to add the gmt_offset parameter
 * @param  tzForm form   timezone form DOM object
 * @author eugene
 */
function ajax_tz_set( tzForm ) {
  var timestamp   = tzForm.time.value;
  var gmt_off     = -tz_calculate(timestamp);

  var cur_gmt_off = tzForm.tz_gmt_off.value;
  if ( gmt_off != cur_gmt_off) {
    var ajaxUrl = '/ajax/autoset_timezone_ajax.php';
    new AsyncSignal( ajaxUrl,
                  { user: tzForm.user.value,
                    post_form_id: tzForm.post_form_id.value,
                    gmt_off: gmt_off
                  }
        ).send();
    // hmmm, what to do in case of failure
    // you can set a handler if you want to handle error
    // also, change ajax/autoset_timezone_ajax.php to setError
    // before sendAndExit
  }
}

/*
 * On-load handler for automatically setting a new user's timezone
 * @author eugene
 */
function tz_autoset() {
  var tz_form = ge('tz_autoset_form');
  if ( tz_form )
    ajax_tz_set( tz_form );
}

// onloadRegister( tz_autoset ); // apparently doesn't work in all browsers
