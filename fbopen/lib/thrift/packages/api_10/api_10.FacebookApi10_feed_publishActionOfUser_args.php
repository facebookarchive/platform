<?php
class api10_FacebookApi10_feed_publishActionOfUser_args {
  public $title = null;
  public $body = null;
  public $image_1 = null;
  public $image_1_link = null;
  public $image_2 = null;
  public $image_2_link = null;
  public $image_3 = null;
  public $image_3_link = null;
  public $image_4 = null;
  public $image_4_link = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['title'])) {
        $this->title = $vals['title'];
      }
      if (isset($vals['body'])) {
        $this->body = $vals['body'];
      }
      if (isset($vals['image_1'])) {
        $this->image_1 = $vals['image_1'];
      }
      if (isset($vals['image_1_link'])) {
        $this->image_1_link = $vals['image_1_link'];
      }
      if (isset($vals['image_2'])) {
        $this->image_2 = $vals['image_2'];
      }
      if (isset($vals['image_2_link'])) {
        $this->image_2_link = $vals['image_2_link'];
      }
      if (isset($vals['image_3'])) {
        $this->image_3 = $vals['image_3'];
      }
      if (isset($vals['image_3_link'])) {
        $this->image_3_link = $vals['image_3_link'];
      }
      if (isset($vals['image_4'])) {
        $this->image_4 = $vals['image_4'];
      }
      if (isset($vals['image_4_link'])) {
        $this->image_4_link = $vals['image_4_link'];
      }
    }
  }

  public function getName() {
    return 'FacebookApi10_feed_publishActionOfUser_args';
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
              $this->title = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->title);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->body = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->body);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_1 = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_1);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_1_link = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_1_link);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_2 = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_2);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 6:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_2_link = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_2_link);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 7:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_3 = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_3);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 8:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_3_link = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_3_link);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 9:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_4 = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_4);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 10:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->image_4_link = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->image_4_link);
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
    $xfer += $output->writeStructBegin('FacebookApi10_feed_publishActionOfUser_args');
    if ($this->title !== null) {
      $xfer += $output->writeFieldBegin('title', TType::STRING, 1);
      $xfer += $output->writeString($this->title);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->body !== null) {
      $xfer += $output->writeFieldBegin('body', TType::STRING, 2);
      $xfer += $output->writeString($this->body);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_1 !== null) {
      $xfer += $output->writeFieldBegin('image_1', TType::STRING, 3);
      $xfer += $output->writeString($this->image_1);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_1_link !== null) {
      $xfer += $output->writeFieldBegin('image_1_link', TType::STRING, 4);
      $xfer += $output->writeString($this->image_1_link);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_2 !== null) {
      $xfer += $output->writeFieldBegin('image_2', TType::STRING, 5);
      $xfer += $output->writeString($this->image_2);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_2_link !== null) {
      $xfer += $output->writeFieldBegin('image_2_link', TType::STRING, 6);
      $xfer += $output->writeString($this->image_2_link);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_3 !== null) {
      $xfer += $output->writeFieldBegin('image_3', TType::STRING, 7);
      $xfer += $output->writeString($this->image_3);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_3_link !== null) {
      $xfer += $output->writeFieldBegin('image_3_link', TType::STRING, 8);
      $xfer += $output->writeString($this->image_3_link);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_4 !== null) {
      $xfer += $output->writeFieldBegin('image_4', TType::STRING, 9);
      $xfer += $output->writeString($this->image_4);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->image_4_link !== null) {
      $xfer += $output->writeFieldBegin('image_4_link', TType::STRING, 10);
      $xfer += $output->writeString($this->image_4_link);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
