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


// Facebook Copyright 2006 - 2008

/**
 * Custom exception class in place to help identify problems
 * that come up during parsing and rendering of FBML.
 */

class FBMLException extends Exception {

  /**
   * Constructs what's understood to be an exception
   * specific to the world of FBML parsing, precaching,
   * rendering, and serialization.
   *
   * @param error_msg a detailed error message describing
   *                  the nature of the exception.
   */

  public function __construct($error_msg = "Generic FBML Exception")
  {
    $this->_error_msg = $error_msg;
    parent::__construct();
  }

  /**
   * Self-explanatory method designed to return
   * the error message passed to the FBMLException
   * at the time the FBMLException was created (and
   * probably thrown).
   *
   * @return the error message describing the nature
   * of the exception.
   */

  public function get_fbml_error_text()
  {
    return $this->_error_msg;
  }

  private $_error_msg;
}

/**
 * General class of exceptions thrown when rendering FBML
 */
class FBMLRenderException extends Exception {
  public function get_fbml_error_text() {
    return $this->getMessage();
  }
}

?>
