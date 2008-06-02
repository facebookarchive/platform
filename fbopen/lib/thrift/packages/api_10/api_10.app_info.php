<?php
class api10_app_info {
  public $app_id = null;
  public $api_key = null;
  public $canvas_name = null;
  public $display_name = null;
  public $icon_url = null;
  public $logo_url = null;
  public $developers = null;
  public $company_name = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['app_id'])) {
        $this->app_id = $vals['app_id'];
      }
      if (isset($vals['api_key'])) {
        $this->api_key = $vals['api_key'];
      }
      if (isset($vals['canvas_name'])) {
        $this->canvas_name = $vals['canvas_name'];
      }
      if (isset($vals['display_name'])) {
        $this->display_name = $vals['display_name'];
      }
      if (isset($vals['icon_url'])) {
        $this->icon_url = $vals['icon_url'];
      }
      if (isset($vals['logo_url'])) {
        $this->logo_url = $vals['logo_url'];
      }
      if (isset($vals['developers'])) {
        $this->developers = $vals['developers'];
      }
      if (isset($vals['company_name'])) {
        $this->company_name = $vals['company_name'];
      }
    }
  }

  public function getName() {
    return 'app_info';
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
              $this->app_id = thrift_protocol_binary_deserialize(TType::I64, $input);
            } else {
              $xfer += $input->readI64($this->app_id);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->api_key = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->api_key);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->canvas_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->canvas_name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->display_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->display_name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->icon_url = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->icon_url);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 6:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->logo_url = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->logo_url);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 7:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->developers = thrift_protocol_binary_deserialize(TType::LST, $input, 'developer_info');
            }
            else
            {
              $this->developers = array();
              $_size42 = 0;
              $_etype45 = 0;
              $xfer += $input->readListBegin($_etype45, $_size42);
              for ($_i46 = 0; $_i46 < $_size42; ++$_i46)
              {
                $elem47 = null;
                $elem47 = new api10_developer_info();
                $xfer += $elem47->read($input);
                $this->developers []= $elem47;
              }
              $xfer += $input->readListEnd();
            }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 8:
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
    $xfer += $output->writeStructBegin('app_info');
    if ($this->app_id !== null) {
      $xfer += $output->writeFieldBegin('app_id', TType::I64, 1);
      $xfer += $output->writeI64($this->app_id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->api_key !== null) {
      $xfer += $output->writeFieldBegin('api_key', TType::STRING, 2);
      $xfer += $output->writeString($this->api_key);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->canvas_name !== null) {
      $xfer += $output->writeFieldBegin('canvas_name', TType::STRING, 3);
      $xfer += $output->writeString($this->canvas_name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->display_name !== null) {
      $xfer += $output->writeFieldBegin('display_name', TType::STRING, 4);
      $xfer += $output->writeString($this->display_name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->icon_url !== null) {
      $xfer += $output->writeFieldBegin('icon_url', TType::STRING, 5);
      $xfer += $output->writeString($this->icon_url);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->logo_url !== null) {
      $xfer += $output->writeFieldBegin('logo_url', TType::STRING, 6);
      $xfer += $output->writeString($this->logo_url);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->developers !== null) {
      $xfer += $output->writeFieldBegin('developers', TType::LST, 7);
      {
        $output->writeListBegin(TType::STRUCT, count($this->developers));
        {
          foreach ($this->developers as $iter48)
          {
            $xfer += $iter48->write($output);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->company_name !== null) {
      $xfer += $output->writeFieldBegin('company_name', TType::STRING, 8);
      $xfer += $output->writeString($this->company_name);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
