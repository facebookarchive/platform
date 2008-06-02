<?php
class api10_developer_info {
  public $uid = null;
  public $name = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['name'])) {
        $this->name = $vals['name'];
      }
    }
  }

  public function getName() {
    return 'developer_info';
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
              $this->uid = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->uid);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->name);
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
    $xfer += $output->writeStructBegin('developer_info');
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::I32, 1);
      $xfer += $output->writeI32($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->name !== null) {
      $xfer += $output->writeFieldBegin('name', TType::STRING, 2);
      $xfer += $output->writeString($this->name);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
