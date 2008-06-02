<?php
class api10_FacebookApi10_friends_getAppUsers_result {
  public $success = null;
  public $error_response = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
      if (isset($vals['error_response'])) {
        $this->error_response = $vals['error_response'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_friends_getAppUsers_result';
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
        case 0:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->success = thrift_protocol_binary_deserialize(TType::LST, $input);
            }
            else
            {
              $this->success = array();
              $_size70 = 0;
              $_etype73 = 0;
              $xfer += $input->readListBegin($_etype73, $_size70);
              for ($_i74 = 0; $_i74 < $_size70; ++$_i74)
              {
                $elem75 = null;
                if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
                  $elem75 = thrift_protocol_binary_deserialize(TType::I32, $input);
                } else {
                  $xfer += $input->readI32($elem75);
                  }
                $this->success []= $elem75;
              }
              $xfer += $input->readListEnd();
            }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 1:
          if ($ftype == TType::STRUCT) {
            $this->error_response = new api10_FacebookApiException();
            $xfer += $this->error_response->read($input);
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
    $xfer += $output->writeStructBegin('FacebookApi10_friends_getAppUsers_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::LST, 0);
      {
        $output->writeListBegin(TType::I32, count($this->success));
        {
          foreach ($this->success as $iter76)
          {
            $xfer += $output->writeI32($iter76);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->error_response !== null) {
      $xfer += $output->writeFieldBegin('error_response', TType::STRUCT, 1);
      $xfer += $this->error_response->write($output);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
