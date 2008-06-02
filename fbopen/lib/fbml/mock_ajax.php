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


/**
 * Returns an implementation based on data from the POST
 * @return   FBMLImplementation
 * @param string $s_context generally this would be $_POST['fb_mockajax_context']
 * @param string $h_context generally this would be $_POST['fb_mockajax_context_hash']
 */
function fbml_mock_ajax_get_impl($s_context, $h_context) {
  // null means get the latest implementation version
  $context = FBMLContext::unmarshall_($s_context, $h_context);

  // The user has presumably triggered this event so we can do
  // all sort of things like show iframes and do autoplay, etc.
  $flavor = $context->_flavor;
  $flavor->_fbml_env['user_triggered'] = true;
  $flavor->_fbml_env['image_cache'] = null;
  $flavor->_fbml_env['ajax_triggered'] = true;

  // This is only applicable to a mock-ajax call from a dialog box,
  // and if the return fbml contains dialog-response.
  if ($flavor->check('dialog_response')) {
    $flavor->_fbml_env['is_dialog_response'] = true;
  }

  return new FBJSEnabledFacebookImplementation($flavor);
}

function fbml_mock_ajax_render_required_js_and_css() {
  return render_required_css( ).render_required_js( );
}
