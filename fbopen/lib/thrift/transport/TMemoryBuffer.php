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
 * Copyright (c) 2006- Facebook
 * Distributed under the Thrift Software License
 *
 * See accompanying file LICENSE or visit the Thrift site at:
 * http://developers.facebook.com/thrift/
 *
 * @package thrift.transport
 * @author Levy Klots <lklots@facebook.com>
 */

/**
 * A memory buffer is a tranpsort that simply reads from and writes to an
 * in-memory string buffer. Anytime you call write on it, the data is simply
 * placed into a buffer, and anytime you call read, data is read from that
 * buffer.
 *
 * @package thrift.transport
 * @author Levy Klots <lklots@facebook.com>
 */
class TMemoryBuffer extends TTransport {

  /**
   * Constructor. Optionally pass an initial value
   * for the buffer.
   */
  public function __construct($buf = '') {
    $this->buf_ = $buf;
  }

  protected $buf_ = '';

  public function isOpen() {
    return true;
  }

  public function open() {}

  public function close() {}

  public function write($buf) {
    $this->buf_ .= $buf;
  }

  public function read($len) {
    if (strlen($this->buf_) === 0) {
      throw new TTransportException('TMemoryBuffer: Could not read ' .
                                    $len . ' bytes from buffer.',
                                    TTransportException::UNKNOWN);
    }

    if (strlen($this->buf_) <= $len) {
      $ret = $this->buf_;
      $this->buf_ = '';
      return $ret;
    }

    $ret = substr($this->buf_, 0, $len);
    $this->buf_ = substr($this->buf_, $len);

    return $ret;
  }

  function getBuffer() {
    return $this->buf_;
  }

  public function available() {
    return strlen($this->buf_);
  }
}

?>
