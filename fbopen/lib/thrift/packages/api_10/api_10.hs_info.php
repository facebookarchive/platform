<?php
class api10_hs_info {
  public $hs1_name = null;
  public $hs2_name = null;
  public $grad_year = null;
  public $hs1_id = null;
  public $hs2_id = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['hs1_name'])) {
        $this->hs1_name = $vals['hs1_name'];
      }
      if (isset($vals['hs2_name'])) {
        $this->hs2_name = $vals['hs2_name'];
      }
      if (isset($vals['grad_year'])) {
        $this->grad_year = $vals['grad_year'];
      }
      if (isset($vals['hs1_id'])) {
        $this->hs1_id = $vals['hs1_id'];
      }
      if (isset($vals['hs2_id'])) {
        $this->hs2_id = $vals['hs2_id'];
      }
    }
  }

  public function getName() {
    return 'hs_info';
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
              $this->hs1_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->hs1_name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->hs2_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->hs2_name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->grad_year = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->grad_year);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->hs1_id = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->hs1_id);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->hs2_id = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->hs2_id);
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
    $xfer += $output->writeStructBegin('hs_info');
    if ($this->hs1_name !== null) {
      $xfer += $output->writeFieldBegin('hs1_name', TType::STRING, 1);
      $xfer += $output->writeString($this->hs1_name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->hs2_name !== null) {
      $xfer += $output->writeFieldBegin('hs2_name', TType::STRING, 2);
      $xfer += $output->writeString($this->hs2_name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->grad_year !== null) {
      $xfer += $output->writeFieldBegin('grad_year', TType::I32, 3);
      $xfer += $output->writeI32($this->grad_year);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->hs1_id !== null) {
      $xfer += $output->writeFieldBegin('hs1_id', TType::I32, 4);
      $xfer += $output->writeI32($this->hs1_id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->hs2_id !== null) {
      $xfer += $output->writeFieldBegin('hs2_id', TType::I32, 5);
      $xfer += $output->writeI32($this->hs2_id);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
