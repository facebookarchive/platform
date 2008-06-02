<?php
class api10_education_info {
  public $name = null;
  public $year = null;
  public $concentrations = null;
  public $degree = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['name'])) {
        $this->name = $vals['name'];
      }
      if (isset($vals['year'])) {
        $this->year = $vals['year'];
      }
      if (isset($vals['concentrations'])) {
        $this->concentrations = $vals['concentrations'];
      }
      if (isset($vals['degree'])) {
        $this->degree = $vals['degree'];
      }
    }
  }

  public function getName() {
    return 'education_info';
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
              $this->name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->year = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->year);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->concentrations = thrift_protocol_binary_deserialize(TType::LST, $input);
            }
            else
            {
              $this->concentrations = array();
              $_size0 = 0;
              $_etype3 = 0;
              $xfer += $input->readListBegin($_etype3, $_size0);
              for ($_i4 = 0; $_i4 < $_size0; ++$_i4)
              {
                $elem5 = null;
                if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
                  $elem5 = thrift_protocol_binary_deserialize(TType::STRING, $input);
                } else {
                  $xfer += $input->readString($elem5);
                  }
                $this->concentrations []= $elem5;
              }
              $xfer += $input->readListEnd();
            }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->degree = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->degree);
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
    $xfer += $output->writeStructBegin('education_info');
    if ($this->name !== null) {
      $xfer += $output->writeFieldBegin('name', TType::STRING, 1);
      $xfer += $output->writeString($this->name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->year !== null) {
      $xfer += $output->writeFieldBegin('year', TType::I32, 2);
      $xfer += $output->writeI32($this->year);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->concentrations !== null) {
      $xfer += $output->writeFieldBegin('concentrations', TType::LST, 3);
      {
        $output->writeListBegin(TType::STRING, count($this->concentrations));
        {
          foreach ($this->concentrations as $iter6)
          {
            $xfer += $output->writeString($iter6);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->degree !== null) {
      $xfer += $output->writeFieldBegin('degree', TType::STRING, 4);
      $xfer += $output->writeString($this->degree);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
