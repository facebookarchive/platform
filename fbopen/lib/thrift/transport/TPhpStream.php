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
 * @author Mark Slee <mcslee@facebook.com>
 */

/**
 * Php stream transport. Reads to and writes from the php standard streams
 * php://input and php://output
 *
 * @package thrift.transport
 * @author Mark Slee <mcslee@facebook.com>
 */
class TPhpStream extends TTransport {

  const MODE_R = 1;
  const MODE_W = 2;

  private $inStream_ = null;

  private $outStream_ = null;

  private $read_ = false;

  private $write_ = false;

  public function __construct($mode) {
    $this->read_ = $mode & self::MODE_R;
    $this->write_ = $mode & self::MODE_W;
  }

  public function open() {
    if ($this->read_) {
      $this->inStream_ = @fopen('php://input', 'r');
      if (!is_resource($this->inStream_)) {
        throw new TException('TPhpStream: Could not open php://input');
      }
    }
    if ($this->write_) {
      $this->outStream_ = @fopen('php://output', 'w');
      if (!is_resource($this->outStream_)) {
        throw new TException('TPhpStream: Could not open php://output');
      }
    }
  }

  public function close() {
    if ($this->read_) {
      @fclose($this->inStream_);
      $this->inStream_ = null;
    }
    if ($this->write_) {
      @fclose($this->outStream_);
      $this->outStream_ = null;
    }
  }

  public function isOpen() {
    return
      (!$this->read_ || is_resource($this->inStream_)) &&
      (!$this->write_ || is_resource($this->outStream_));
  }

  public function read($len) {
    $data = @fread($this->inStream_, $len);
    if ($data === FALSE || $data === '') {
      throw new TException('TPhpStream: Could not read '.$len.' bytes');
    }
    return $data;
  }

  public function write($buf) {
    while (strlen($buf) > 0) {
      $got = @fwrite($this->outStream_, $buf);
      if ($got === 0 || $got === FALSE) {
        throw new TException('TPhpStream: Could not write '.strlen($buf).' bytes');
      }
      $buf = substr($buf, $got);
    }
  }

  public function flush() {
    @fflush($this->outStream_);
  }

}

?>
