<?php
class api10_FacebookApi10_admin_setAppProperties_args {
  public $properties = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['properties'])) {
        $this->properties = $vals['properties'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_admin_setAppProperties_args';
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
              $this->properties = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->properties);
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
    $xfer += $output->writeStructBegin('FacebookApi10_admin_setAppProperties_args');
    if ($this->properties !== null) {
      $xfer += $output->writeFieldBegin('properties', TType::STRING, 1);
      $xfer += $output->writeString($this->properties);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
