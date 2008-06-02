<?php
class api10_FacebookApi10_batch_run_args {
  public $method_feed = null;
  public $serial_only = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['method_feed'])) {
        $this->method_feed = $vals['method_feed'];
      }
      if (isset($vals['serial_only'])) {
        $this->serial_only = $vals['serial_only'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_batch_run_args';
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
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->method_feed = thrift_protocol_binary_deserialize(TType::LST, $input);
            }
            else
            {
              $this->method_feed = array();
              $_size105 = 0;
              $_etype108 = 0;
              $xfer += $input->readListBegin($_etype108, $_size105);
              for ($_i109 = 0; $_i109 < $_size105; ++$_i109)
              {
                $elem110 = null;
                if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
                  $elem110 = thrift_protocol_binary_deserialize(TType::STRING, $input);
                } else {
                  $xfer += $input->readString($elem110);
                  }
                $this->method_feed []= $elem110;
              }
              $xfer += $input->readListEnd();
            }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::BOOL) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->serial_only = thrift_protocol_binary_deserialize(TType::BOOL, $input);
            } else {
              $xfer += $input->readBool($this->serial_only);
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
    $xfer += $output->writeStructBegin('FacebookApi10_batch_run_args');
    if ($this->method_feed !== null) {
      $xfer += $output->writeFieldBegin('method_feed', TType::LST, 1);
      {
        $output->writeListBegin(TType::STRING, count($this->method_feed));
        {
          foreach ($this->method_feed as $iter111)
          {
            $xfer += $output->writeString($iter111);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->serial_only !== null) {
      $xfer += $output->writeFieldBegin('serial_only', TType::BOOL, 2);
      $xfer += $output->writeBool($this->serial_only);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
