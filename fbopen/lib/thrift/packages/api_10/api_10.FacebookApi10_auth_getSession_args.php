<?php
class api10_FacebookApi10_auth_getSession_args {
  public $auth_token = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['auth_token'])) {
        $this->auth_token = $vals['auth_token'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_auth_getSession_args';
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
              $this->auth_token = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->auth_token);
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
    $xfer += $output->writeStructBegin('FacebookApi10_auth_getSession_args');
    if ($this->auth_token !== null) {
      $xfer += $output->writeFieldBegin('auth_token', TType::STRING, 1);
      $xfer += $output->writeString($this->auth_token);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
