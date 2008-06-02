<?php
class api10_FacebookApiException extends TException {
  public $error_code = null;
  public $error_msg = null;
  public $request_args = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['error_code'])) {
        $this->error_code = $vals['error_code'];
      }
      if (isset($vals['error_msg'])) {
        $this->error_msg = $vals['error_msg'];
      }
      if (isset($vals['request_args'])) {
        $this->request_args = $vals['request_args'];
      }
    }
  }

  public function getName() {
    return 'FacebookApiException';
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
              $this->error_code = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->error_code);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->error_msg = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->error_msg);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->request_args = thrift_protocol_binary_deserialize(TType::LST, $input, 'arg');
            }
            else
            {
              $this->request_args = array();
              $_size49 = 0;
              $_etype52 = 0;
              $xfer += $input->readListBegin($_etype52, $_size49);
              for ($_i53 = 0; $_i53 < $_size49; ++$_i53)
              {
                $elem54 = null;
                $elem54 = new api10_arg();
                $xfer += $elem54->read($input);
                $this->request_args []= $elem54;
              }
              $xfer += $input->readListEnd();
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
    $xfer += $output->writeStructBegin('FacebookApiException');
    if ($this->error_code !== null) {
      $xfer += $output->writeFieldBegin('error_code', TType::I32, 1);
      $xfer += $output->writeI32($this->error_code);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->error_msg !== null) {
      $xfer += $output->writeFieldBegin('error_msg', TType::STRING, 2);
      $xfer += $output->writeString($this->error_msg);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->request_args !== null) {
      $xfer += $output->writeFieldBegin('request_args', TType::LST, 3);
      {
        $output->writeListBegin(TType::STRUCT, count($this->request_args));
        {
          foreach ($this->request_args as $iter55)
          {
            $xfer += $iter55->write($output);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
