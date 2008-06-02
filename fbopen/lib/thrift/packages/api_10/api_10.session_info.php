<?php
class api10_session_info {
  public $session_key = null;
  public $uid = null;
  public $expires = null;
  public $secret = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['session_key'])) {
        $this->session_key = $vals['session_key'];
      }
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['expires'])) {
        $this->expires = $vals['expires'];
      }
      if (isset($vals['secret'])) {
        $this->secret = $vals['secret'];
      }
    }
  }

  public function getName() {
    return 'session_info';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->session_key = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->session_key);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->uid = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->uid);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->expires = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->expires);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->secret = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->secret);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('session_info');
    if ($this->session_key !== null) {
      $xfer += $output->writeFieldBegin('session_key', TType::STRING, 1);
      $xfer += $output->writeString($this->session_key);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::I32, 2);
      $xfer += $output->writeI32($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->expires !== null) {
      $xfer += $output->writeFieldBegin('expires', TType::I32, 3);
      $xfer += $output->writeI32($this->expires);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->secret !== null) {
      $xfer += $output->writeFieldBegin('secret', TType::STRING, 4);
      $xfer += $output->writeString($this->secret);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
