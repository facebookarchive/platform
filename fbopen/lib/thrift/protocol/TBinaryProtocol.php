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

include_once $GLOBALS['THRIFT_ROOT'].'/transport/TBufferedTransport.php';

/**
 * Copyright (c) 2006- Facebook
 * Distributed under the Thrift Software License
 *
 * See accompanying file LICENSE or visit the Thrift site at:
 * http://developers.facebook.com/thrift/
 *
 * @package thrift.protocol
 * @author Mark Slee <mcslee@facebook.com>
 */

/**
 * Binary implementation of the Thrift protocol.
 *
 * @author Mark Slee <mcslee@facebook.com>
 * @author Marc Kwiatkowski <marc@facebook.com>
 */
class TBinaryProtocol extends TProtocol {

  const VERSION_MASK = 0xffff0000;
  const VERSION_1 = 0x80010000;

  private $strictRead_ = false;
  private $strictWrite_ = true;

  // NOTE: Facebook version diverges from Thrift trunk here, Thrift trunk has
  // strictWrite set to true!
  public function __construct($trans, $strictRead=false, $strictWrite=false) {
    parent::__construct($trans);
    $this->strictRead_ = $strictRead;
    $this->strictWrite_ = $strictWrite;
  }

  public function writeMessageBegin($name, $type, $seqid) {
    if ($this->strictWrite_) {
      $version = self::VERSION_1 | $type;
      return
        $this->writeI32($version) +
        $this->writeString($name) +
        $this->writeI32($seqid);
    } else {
      return
        $this->writeString($name) +
        $this->writeByte($type) +
        $this->writeI32($seqid);
    }
  }

  public function writeMessageEnd() {
    return 0;
  }

  public function writeStructBegin($name) {
    return 0;
  }

  public function writeStructEnd() {
    return 0;
  }

  public function writeFieldBegin($fieldName, $fieldType, $fieldId) {
    return
      $this->writeByte($fieldType) +
      $this->writeI16($fieldId);
  }

  public function writeFieldEnd() {
    return 0;
  }

  public function writeFieldStop() {
    return
      $this->writeByte(TType::STOP);
  }

  public function writeMapBegin($keyType, $valType, $size) {
    return
      $this->writeByte($keyType) +
      $this->writeByte($valType) +
      $this->writeI32($size);
  }

  public function writeMapEnd() {
    return 0;
  }

  public function writeListBegin($elemType, $size) {
    return
      $this->writeByte($elemType) +
      $this->writeI32($size);
  }

  public function writeListEnd() {
    return 0;
  }

  public function writeSetBegin($elemType, $size) {
    return
      $this->writeByte($elemType) +
      $this->writeI32($size);
  }

  public function writeSetEnd() {
    return 0;
  }

  public function writeBool($value) {
    $data = pack('c', $value ? 1 : 0);
    $this->trans_->write($data, 1);
    return 1;
  }

  public function writeByte($value) {
    $data = pack('c', $value);
    $this->trans_->write($data, 1);
    return 1;
  }

  public function writeI16($value) {
    $data = pack('n', $value);
    $this->trans_->write($data, 2);
    return 2;
  }

  public function writeI32($value) {
    $data = pack('N', $value);
    $this->trans_->write($data, 4);
    return 4;
  }

  public function writeI64($value) {
    // If we are on a 32bit architecture we have to explicitly deal with
    // 64-bit twos-complement arithmetic since PHP wants to treat all ints
    // as signed and any int over 2^31 - 1 as a float
    if (PHP_INT_SIZE == 4) {
      $neg = $value < 0;

      if ($neg) {
        $value *= -1;
      }

      $hi = (int)($value / 4294967296);
      $lo = (int)$value;

      if ($neg) {
        $hi = ~$hi;
        $lo = ~$lo;
        if (($lo & (int)0xffffffff) == (int)0xffffffff) {
          $lo = 0;
          $hi++;
        } else {
          $lo++;
        }
      }
      $data = pack('N2', $hi, $lo);

    } else {
      $hi = $value >> 32;
      $lo = $value & 0xFFFFFFFF;
      $data = pack('N2', $hi, $lo);
    }

    $this->trans_->write($data, 8);
    return 8;
  }

  public function writeDouble($value) {
    $data = pack('d', $value);
    $this->trans_->write(strrev($data), 8);
    return 8;
  }

  public function writeString($value) {
    $len = strlen($value);
    $result = $this->writeI32($len);
    if ($len) {
      $this->trans_->write($value, $len);
    }
    return $result + $len;
  }

  public function readMessageBegin(&$name, &$type, &$seqid) {
    $result = $this->readI32($sz);
    if ($sz < 0) {
      $version = $sz & self::VERSION_MASK;
      if ($version != self::VERSION_1) {
        throw new TProtocolException('Bad version identifier: '.$sz, TProtocolException::BAD_VERSION);
      }
      $type = $sz & 0x000000ff;
      $result +=
        $this->readString($name) +
        $this->readI32($seqid);
    } else {
      if ($this->strictRead_) {
        throw new TProtocolException('No version identifier, old protocol client?', TProtocolException::BAD_VERSION);
      } else {
        // Handle pre-versioned input
        $name = $this->trans_->readAll($sz);
        $result +=
          $sz +
          $this->readByte($type) +
          $this->readI32($seqid);
      }
    }
    return $result;
  }

  public function readMessageEnd() {
    return 0;
  }

  public function readStructBegin(&$name) {
    $name = '';
    return 0;
  }

  public function readStructEnd() {
    return 0;
  }

  public function readFieldBegin(&$name, &$fieldType, &$fieldId) {
    $result = $this->readByte($fieldType);
    if ($fieldType == TType::STOP) {
      $fieldId = 0;
      return $result;
    }
    $result += $this->readI16($fieldId);
    return $result;
  }

  public function readFieldEnd() {
    return 0;
  }

  public function readMapBegin(&$keyType, &$valType, &$size) {
    return
      $this->readByte($keyType) +
      $this->readByte($valType) +
      $this->readI32($size);
  }

  public function readMapEnd() {
    return 0;
  }

  public function readListBegin(&$elemType, &$size) {
    return
      $this->readByte($elemType) +
      $this->readI32($size);
  }

  public function readListEnd() {
    return 0;
  }

  public function readSetBegin(&$elemType, &$size) {
    return
      $this->readByte($elemType) +
      $this->readI32($size);
  }

  public function readSetEnd() {
    return 0;
  }

  public function readBool(&$value) {
    $data = $this->trans_->readAll(1);
    $arr = unpack('c', $data);
    $value = $arr[1] == 1;
    return 1;
  }

  public function readByte(&$value) {
    $data = $this->trans_->readAll(1);
    $arr = unpack('c', $data);
    $value = $arr[1];
    return 1;
  }

  public function readI16(&$value) {
    $data = $this->trans_->readAll(2);
    $arr = unpack('n', $data);
    $value = $arr[1];
    if ($value > 0x7fff) {
      $value = 0 - (($value - 1) ^ 0xffff);
    }
    return 2;
  }

  public function readI32(&$value) {
    $data = $this->trans_->readAll(4);
    $arr = unpack('N', $data);
    $value = $arr[1];
    if ($value > 0x7fffffff) {
      $value = 0 - (($value - 1) ^ 0xffffffff);
    }
    return 4;
  }

  public function readI64(&$value) {
    $data = $this->trans_->readAll(8);

    $arr = unpack('N2', $data);

    // If we are on a 32bit architecture we have to explicitly deal with
    // 64-bit twos-complement arithmetic since PHP wants to treat all ints
    // as signed and any int over 2^31 - 1 as a float
    if (PHP_INT_SIZE == 4) {

      $hi = $arr[1];
      $lo = $arr[2];
      $isNeg = $hi  < 0;

      // Check for a negative
      if ($isNeg) {
        $hi = ~$hi & (int)0xffffffff;
        $lo = ~$lo & (int)0xffffffff;

        if ($lo == (int)0xffffffff) {
          $hi++;
          $lo = 0;
        } else {
          $lo++;
        }
      }

      // Force 32bit words in excess of 2G to pe positive - we deal wigh sign
      // explicitly below

      if ($hi & (int)0x80000000) {
        $hi &= (int)0x7fffffff;
        $hi += 0x80000000;
      }

      if ($lo & (int)0x80000000) {
        $lo &= (int)0x7fffffff;
        $lo += 0x80000000;
      }

      $value = $hi * 4294967296 + $lo;

      if ($isNeg) {
        $value = 0 - $value;
      }
    } else {

      // Upcast negatives in LSB bit
      if ($arr[2] & 0x80000000) {
        $arr[2] = $arr[2] & 0xffffffff;
      }

      // Check for a negative
      if ($arr[1] & 0x80000000) {
        $arr[1] = $arr[1] & 0xffffffff;
        $arr[1] = $arr[1] ^ 0xffffffff;
        $arr[2] = $arr[2] ^ 0xffffffff;
        $value = 0 - $arr[1]*4294967296 - $arr[2] - 1;
      } else {
        $value = $arr[1]*4294967296 + $arr[2];
      }
    }

    return 8;
  }

  public function readDouble(&$value) {
    $data = strrev($this->trans_->readAll(8));
    $arr = unpack('d', $data);
    $value = $arr[1];
    return 8;
  }

  public function readString(&$value) {
    $result = $this->readI32($len);
    if ($len) {
      $value = $this->trans_->readAll($len);
    } else {
      $value = '';
    }
    return $result + $len;
  }
}

/**
 * Binary Protocol Factory
 */
class TBinaryProtocolFactory implements TProtocolFactory {
  private $strictRead_ = false;
  private $strictWrite_ = false;

  public function __construct($strictRead=false, $strictWrite=false) {
    $this->strictRead_ = $strictRead;
    $this->strictWrite_ = $strictWrite;
  }

  public function getProtocol($trans) {
    return new TBinaryProtocol($trans, $this->strictRead, $this->strictWrite);
  }
}

/**
 * Accelerated binary protocol: used in conjunction with the thrift_protocol
 * extension for faster deserialization
 */
class TBinaryProtocolAccelerated extends TBinaryProtocol {
  public function __construct($trans, $strictRead=false, $strictWrite=false) {
    // If the transport doesn't implement putBack, wrap it in a
    // TBufferedTransport (which does)
    if (!method_exists($trans, 'putBack')) {
      $trans = new TBufferedTransport($trans);
    }
    parent::__construct($trans, $strictRead, $strictWrite);
  }
}

?>
