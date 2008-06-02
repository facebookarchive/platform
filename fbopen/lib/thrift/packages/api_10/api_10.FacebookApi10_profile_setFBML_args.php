<?php
class api10_FacebookApi10_profile_setFBML_args {
  public $markup = null;
  public $uid = null;
  public $profile = null;
  public $profile_action = null;
  public $mobile_profile = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['markup'])) {
        $this->markup = $vals['markup'];
      }
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['profile'])) {
        $this->profile = $vals['profile'];
      }
      if (isset($vals['profile_action'])) {
        $this->profile_action = $vals['profile_action'];
      }
      if (isset($vals['mobile_profile'])) {
        $this->mobile_profile = $vals['mobile_profile'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_profile_setFBML_args';
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
              $this->markup = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->markup);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I64) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->uid = thrift_protocol_binary_deserialize(TType::I64, $input);
            } else {
              $xfer += $input->readI64($this->uid);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->profile = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->profile);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->profile_action = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->profile_action);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->mobile_profile = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->mobile_profile);
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
    $xfer += $output->writeStructBegin('FacebookApi10_profile_setFBML_args');
    if ($this->markup !== null) {
      $xfer += $output->writeFieldBegin('markup', TType::STRING, 1);
      $xfer += $output->writeString($this->markup);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::I64, 2);
      $xfer += $output->writeI64($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->profile !== null) {
      $xfer += $output->writeFieldBegin('profile', TType::STRING, 3);
      $xfer += $output->writeString($this->profile);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->profile_action !== null) {
      $xfer += $output->writeFieldBegin('profile_action', TType::STRING, 4);
      $xfer += $output->writeString($this->profile_action);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->mobile_profile !== null) {
      $xfer += $output->writeFieldBegin('mobile_profile', TType::STRING, 5);
      $xfer += $output->writeString($this->mobile_profile);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
