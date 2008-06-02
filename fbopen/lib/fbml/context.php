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
 * A class for holding the context in which FBML is being evaluated
 *
 * "Context" basically means an FBML env + a flavor
 *
 */
class FBMLContext {

  public function __construct($flavor) {
    $this->_flavor = $flavor;
  }

  /**
   * Returns a context marshalled to a string
   * @return    string    String representation that can be used to
   *                      recreate this context
   */
  public function marshall() {
    if (!isset($this->_m)) {
      $this->_m = $this->_flavor->serialize();
    }
    return $this->_m;
  }

  //
  // Constants for generating the signature hash
  //
  const _HASH_SALT = 'Y0ur_s4lt_h3r3';
  const _HASH_SIZE = 12;

  /**
   * Computes the hash of a marshalled context
   * @param    string    Marshalled context
   * @return   string    Hash of that
   *
   * We use the hash both as a short identifier for the context and
   * also as a way to sign the context so that no one can trick us into
   * rendering FBML in a context that would allow them to see stuff they
   * shouldn't be able to.
   */
  public static function hash_($m) {
    return substr(md5($m . self::_HASH_SALT), 0, self::_HASH_SIZE);
  }

  /**
   * Computes the hash of this context
   * @return   string   Hash of marshalled string for this context
   */
  public function hash() {
    return self::hash_($this->marshall());
  }

  /**
   * Creates a context and marshalls it
   * @param    array         $fbml_env
   * @param    FBMLFlavor    $flavor
   * @return   string        Marshalled context
   */
  public static function marshall_($flavor) {
    $context = new FBMLContext($flavor);
    return array($context->marshall(), $context->hash());
  }

  /**
   * Returns a context based on a marshalled string and its hash
   * @param    string       $m_context   Marshalled context
   * @param    string       $hash        Hash of the marshalled context string
   * @return   FBMLContext  The context described by the string passed in
   *
   * We check the hash before actually unmarshalling the context so that
   * no one can even get us to create anything malicious, much less use it.
   */
  public static function unmarshall_($m_context, $hash) {

    if (self::hash_($m_context) == $hash) {
      return self::trusted_unmarshall_($m_context);
    }

    throw new FBMLContextBadSigException('Bad FBMLContext sig.  Someone is probably trying to hack the site. ' . $m_context . ' => ' . $hash .  '  Refpage: ' . $_SERVER['HTTP_REFERER']);

  }

  /**
   * Unmarshalls a context without requiring a hash
   * @param    string   $m_context   Marshalled context
   * @return   FBMLContext
   */
  public static function trusted_unmarshall_($m_context) {

    // We've PHP serialized this object so that the class name
    // and everything get preserved appropriately
    $flavor = unserialize($m_context);

    if ($flavor instanceof FBMLFlavor) {
      return new FBMLContext($flavor);
    } else {
      throw new FBMLContextNoFlavorException($m_context);
    }

  }

}

/**
 * An exception thrown if there is something wrong with the context
 */

class FBMLContextException extends FBMLException {
}

class FBMLContextBadSigException extends FBMLContextException {
}

class FBMLContextNoFlavorException extends FBMLContextException {
}
