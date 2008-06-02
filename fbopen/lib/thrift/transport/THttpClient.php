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
 * HTTP client for Thrift
 *
 * @package thrift.transport
 * @author Mark Slee <mcslee@facebook.com>
 */
class THttpClient extends TTransport {

  /**
   * The host to connect to
   *
   * @var string
   */
  protected $host_;

  /**
   * The port to connect on
   *
   * @var int
   */
  protected $port_;

  /**
   * The URI to request
   *
   * @var string
   */
  protected $uri_;

  /**
   * Buffer for the HTTP request data
   *
   * @var string
   */
  protected $buf_;

  /**
   * Input socket stream.
   *
   * @var resource
   */
  protected $handle_;

  /**
   * Read timeout
   *
   * @var float
   */
  protected $timeout_;

  /**
   * Make a new HTTP client.
   *
   * @param string $host
   * @param int    $port
   * @param string $uri
   */
  public function __construct($host, $port=80, $uri='') {
    if ((strlen($uri) > 0) && ($uri{0} != '/')) {
      $uri = '/'.$uri;
    }
    $this->host_ = $host;
    $this->port_ = $port;
    $this->uri_ = $uri;
    $this->buf_ = '';
    $this->handle_ = null;
    $this->timeout_ = null;
  }

  /**
   * Set read timeout
   *
   * @param float $timeout
   */
  public function setTimeoutSecs($timeout) {
    $this->timeout_ = $timeout;
  }

  /**
   * Whether this transport is open.
   *
   * @return boolean true if open
   */
  public function isOpen() {
    return true;
  }

  /**
   * Open the transport for reading/writing
   *
   * @throws TTransportException if cannot open
   */
  public function open() {}

  /**
   * Close the transport.
   */
  public function close() {
    if ($this->handle_) {
      @fclose($this->handle_);
      $this->handle_ = null;
    }
  }

  /**
   * Read some data into the array.
   *
   * @param int    $len How much to read
   * @return string The data that has been read
   * @throws TTransportException if cannot read any more data
   */
  public function read($len) {
    $data = @fread($this->handle_, $len);
    if ($data === FALSE || $data === '') {
      $md = stream_get_meta_data($this->handle_);
      if ($md['timed_out']) {
        throw new TTransportException('THttpClient: timed out reading '.$len.' bytes from '.$this->host_.':'.$this->port_.'/'.$this->uri_, TTransportException::TIMED_OUT);
      } else {
        throw new TTransportException('THttpClient: Could not read '.$len.' bytes from '.$this->host_.':'.$this->port_.'/'.$this->uri_, TTransportException::UNKNOWN);
      }
    }
    return $data;
  }

  /**
   * Writes some data into the pending buffer
   *
   * @param string $buf  The data to write
   * @throws TTransportException if writing fails
   */
  public function write($buf) {
    $this->buf_ .= $buf;
  }

  /**
   * Opens and sends the actual request over the HTTP connection
   *
   * @throws TTransportException if a writing error occurs
   */
  public function flush() {
    // God, PHP really has some esoteric ways of doing simple things.
    $host = $this->host_.($this->port_ != 80 ? ':'.$this->port_ : '');

    $headers = array('Host: '.$host,
                     'Accept: application/x-thrift',
                     'User-Agent: PHP/THttpClient',
                     'Content-Type: application/x-thrift',
                     'Content-Length: '.strlen($this->buf_));

    $options = array('method' => 'POST',
                     'header' => implode("\r\n", $headers),
                     'max_redirects' => 1,
                     'content' => $this->buf_);
    if ($this->timeout_ > 0) {
      $options['timeout'] = $this->timeout_;
    }
    $this->buf_ = '';

    $contextid = stream_context_create(array('http' => $options));
    $this->handle_ = @fopen('http://'.$host.$this->uri_, 'r', false, $contextid);

    // Connect failed?
    if ($this->handle_ === FALSE) {
      $this->handle_ = null;
      $error = 'THttpClient: Could not connect to '.$host.$this->uri_;
      throw new TTransportException($error, TTransportException::NOT_OPEN);
    }
  }

}

?>
