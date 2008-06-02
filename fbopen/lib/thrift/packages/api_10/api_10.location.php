<?php
class api10_location {
  public $street = null;
  public $city = null;
  public $state = null;
  public $country = null;
  public $zip = null;
  public $latitude = null;
  public $longitude = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['street'])) {
        $this->street = $vals['street'];
      }
      if (isset($vals['city'])) {
        $this->city = $vals['city'];
      }
      if (isset($vals['state'])) {
        $this->state = $vals['state'];
      }
      if (isset($vals['country'])) {
        $this->country = $vals['country'];
      }
      if (isset($vals['zip'])) {
        $this->zip = $vals['zip'];
      }
      if (isset($vals['latitude'])) {
        $this->latitude = $vals['latitude'];
      }
      if (isset($vals['longitude'])) {
        $this->longitude = $vals['longitude'];
      }
    }
  }

  public function getName() {
    return 'location';
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
              $this->street = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->street);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->city = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->city);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->state = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->state);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->country = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->country);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->zip = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->zip);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 6:
          if ($ftype == TType::DOUBLE) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->latitude = thrift_protocol_binary_deserialize(TType::DOUBLE, $input);
            } else {
              $xfer += $input->readDouble($this->latitude);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 7:
          if ($ftype == TType::DOUBLE) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->longitude = thrift_protocol_binary_deserialize(TType::DOUBLE, $input);
            } else {
              $xfer += $input->readDouble($this->longitude);
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
    $xfer += $output->writeStructBegin('location');
    if ($this->street !== null) {
      $xfer += $output->writeFieldBegin('street', TType::STRING, 1);
      $xfer += $output->writeString($this->street);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->city !== null) {
      $xfer += $output->writeFieldBegin('city', TType::STRING, 2);
      $xfer += $output->writeString($this->city);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->state !== null) {
      $xfer += $output->writeFieldBegin('state', TType::STRING, 3);
      $xfer += $output->writeString($this->state);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->country !== null) {
      $xfer += $output->writeFieldBegin('country', TType::STRING, 4);
      $xfer += $output->writeString($this->country);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->zip !== null) {
      $xfer += $output->writeFieldBegin('zip', TType::STRING, 5);
      $xfer += $output->writeString($this->zip);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->latitude !== null) {
      $xfer += $output->writeFieldBegin('latitude', TType::DOUBLE, 6);
      $xfer += $output->writeDouble($this->latitude);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->longitude !== null) {
      $xfer += $output->writeFieldBegin('longitude', TType::DOUBLE, 7);
      $xfer += $output->writeDouble($this->longitude);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
