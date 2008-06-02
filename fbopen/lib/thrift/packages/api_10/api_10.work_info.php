<?php
class api10_work_info {
  public $location = null;
  public $company_name = null;
  public $position = null;
  public $description = null;
  public $start_date = null;
  public $end_date = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['location'])) {
        $this->location = $vals['location'];
      }
      if (isset($vals['company_name'])) {
        $this->company_name = $vals['company_name'];
      }
      if (isset($vals['position'])) {
        $this->position = $vals['position'];
      }
      if (isset($vals['description'])) {
        $this->description = $vals['description'];
      }
      if (isset($vals['start_date'])) {
        $this->start_date = $vals['start_date'];
      }
      if (isset($vals['end_date'])) {
        $this->end_date = $vals['end_date'];
      }
    }
  }

  public function getName() {
    return 'work_info';
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
          if ($ftype == TType::STRUCT) {
            $this->location = new api10_location();
            $xfer += $this->location->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->company_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->company_name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->position = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->position);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->description = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->description);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->start_date = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->start_date);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 6:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->end_date = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->end_date);
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
    $xfer += $output->writeStructBegin('work_info');
    if ($this->location !== null) {
      $xfer += $output->writeFieldBegin('location', TType::STRUCT, 1);
      $xfer += $this->location->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->company_name !== null) {
      $xfer += $output->writeFieldBegin('company_name', TType::STRING, 2);
      $xfer += $output->writeString($this->company_name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->position !== null) {
      $xfer += $output->writeFieldBegin('position', TType::STRING, 3);
      $xfer += $output->writeString($this->position);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->description !== null) {
      $xfer += $output->writeFieldBegin('description', TType::STRING, 4);
      $xfer += $output->writeString($this->description);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->start_date !== null) {
      $xfer += $output->writeFieldBegin('start_date', TType::STRING, 5);
      $xfer += $output->writeString($this->start_date);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->end_date !== null) {
      $xfer += $output->writeFieldBegin('end_date', TType::STRING, 6);
      $xfer += $output->writeString($this->end_date);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
