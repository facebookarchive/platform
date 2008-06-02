<?php
class api10_FacebookApi10_users_getInfo_args {
  public $uids = null;
  public $fields = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['uids'])) {
        $this->uids = $vals['uids'];
      }
      if (isset($vals['fields'])) {
        $this->fields = $vals['fields'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_users_getInfo_args';
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
              $this->uids = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->uids);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->fields = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->fields);
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
    $xfer += $output->writeStructBegin('FacebookApi10_users_getInfo_args');
    if ($this->uids !== null) {
      $xfer += $output->writeFieldBegin('uids', TType::STRING, 1);
      $xfer += $output->writeString($this->uids);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->fields !== null) {
      $xfer += $output->writeFieldBegin('fields', TType::STRING, 2);
      $xfer += $output->writeString($this->fields);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
