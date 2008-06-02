<?php
class api10_FacebookApi10_application_getPublicInfo_args {
  public $application_id = null;
  public $application_api_key = null;
  public $application_canvas_name = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['application_id'])) {
        $this->application_id = $vals['application_id'];
      }
      if (isset($vals['application_api_key'])) {
        $this->application_api_key = $vals['application_api_key'];
      }
      if (isset($vals['application_canvas_name'])) {
        $this->application_canvas_name = $vals['application_canvas_name'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_application_getPublicInfo_args';
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
          if ($ftype == TType::I64) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->application_id = thrift_protocol_binary_deserialize(TType::I64, $input);
            } else {
              $xfer += $input->readI64($this->application_id);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->application_api_key = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->application_api_key);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->application_canvas_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->application_canvas_name);
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
    $xfer += $output->writeStructBegin('FacebookApi10_application_getPublicInfo_args');
    if ($this->application_id !== null) {
      $xfer += $output->writeFieldBegin('application_id', TType::I64, 1);
      $xfer += $output->writeI64($this->application_id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->application_api_key !== null) {
      $xfer += $output->writeFieldBegin('application_api_key', TType::STRING, 2);
      $xfer += $output->writeString($this->application_api_key);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->application_canvas_name !== null) {
      $xfer += $output->writeFieldBegin('application_canvas_name', TType::STRING, 3);
      $xfer += $output->writeString($this->application_canvas_name);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
