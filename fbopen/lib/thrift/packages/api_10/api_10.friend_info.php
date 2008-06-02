<?php
class api10_friend_info {
  public $uid1 = null;
  public $uid2 = null;
  public $are_friends = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['uid1'])) {
        $this->uid1 = $vals['uid1'];
      }
      if (isset($vals['uid2'])) {
        $this->uid2 = $vals['uid2'];
      }
      if (isset($vals['are_friends'])) {
        $this->are_friends = $vals['are_friends'];
      }
    }
  }

  public function getName() {
    return 'friend_info';
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
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->uid1 = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->uid1);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->uid2 = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->uid2);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::BOOL) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->are_friends = thrift_protocol_binary_deserialize(TType::BOOL, $input);
            } else {
              $xfer += $input->readBool($this->are_friends);
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
    $xfer += $output->writeStructBegin('friend_info');
    if ($this->uid1 !== null) {
      $xfer += $output->writeFieldBegin('uid1', TType::I32, 1);
      $xfer += $output->writeI32($this->uid1);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid2 !== null) {
      $xfer += $output->writeFieldBegin('uid2', TType::I32, 2);
      $xfer += $output->writeI32($this->uid2);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->are_friends !== null) {
      $xfer += $output->writeFieldBegin('are_friends', TType::BOOL, 3);
      $xfer += $output->writeBool($this->are_friends);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
