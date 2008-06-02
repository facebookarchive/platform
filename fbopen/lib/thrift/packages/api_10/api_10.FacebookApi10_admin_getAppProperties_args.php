<?php
class api10_FacebookApi10_admin_getAppProperties_args {
  public $properties = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['properties'])) {
        $this->properties = $vals['properties'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_admin_getAppProperties_args';
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
              $this->properties = thrift_protocol_binary_deserialize(TType::LST, $input);
            }
            else
            {
              $this->properties = array();
              $_size91 = 0;
              $_etype94 = 0;
              $xfer += $input->readListBegin($_etype94, $_size91);
              for ($_i95 = 0; $_i95 < $_size91; ++$_i95)
              {
                $elem96 = null;
                if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
                  $elem96 = thrift_protocol_binary_deserialize(TType::STRING, $input);
                } else {
                  $xfer += $input->readString($elem96);
                  }
                $this->properties []= $elem96;
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
    $xfer += $output->writeStructBegin('FacebookApi10_admin_getAppProperties_args');
    if ($this->properties !== null) {
      $xfer += $output->writeFieldBegin('properties', TType::LST, 1);
      {
        $output->writeListBegin(TType::STRING, count($this->properties));
        {
          foreach ($this->properties as $iter97)
          {
            $xfer += $output->writeString($iter97);
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
