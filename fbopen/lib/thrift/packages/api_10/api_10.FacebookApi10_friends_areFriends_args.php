<?php
class api10_FacebookApi10_friends_areFriends_args {
  public $uids1 = null;
  public $uids2 = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['uids1'])) {
        $this->uids1 = $vals['uids1'];
      }
      if (isset($vals['uids2'])) {
        $this->uids2 = $vals['uids2'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_friends_areFriends_args';
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
              $this->uids1 = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->uids1);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->uids2 = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->uids2);
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
    $xfer += $output->writeStructBegin('FacebookApi10_friends_areFriends_args');
    if ($this->uids1 !== null) {
      $xfer += $output->writeFieldBegin('uids1', TType::STRING, 1);
      $xfer += $output->writeString($this->uids1);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uids2 !== null) {
      $xfer += $output->writeFieldBegin('uids2', TType::STRING, 2);
      $xfer += $output->writeString($this->uids2);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
