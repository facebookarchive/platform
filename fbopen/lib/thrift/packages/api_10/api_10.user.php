<?php
class api10_user {
  public $about_me = null;
  public $activities = null;
  public $affiliations = null;
  public $birthday = null;
  public $books = null;
  public $current_location = null;
  public $education_history = null;
  public $first_name = null;
  public $hometown_location = null;
  public $hs_info = null;
  public $interests = null;
  public $is_app_user = null;
  public $last_name = null;
  public $meeting_for = null;
  public $meeting_sex = null;
  public $movies = null;
  public $music = null;
  public $name = null;
  public $notes_count = null;
  public $pic = null;
  public $pic_big = null;
  public $pic_small = null;
  public $political = null;
  public $profile_update_time = null;
  public $quotes = null;
  public $relationship_status = null;
  public $religion = null;
  public $sex = null;
  public $significant_other_id = null;
  public $status = null;
  public $timezone = null;
  public $tv = null;
  public $uid = null;
  public $wall_count = null;
  public $work_history = null;
  public $pic_square = null;
  public $has_added_app = null;

  public function __construct($vals=null) {
    if (is_array($vals)) {
      if (isset($vals['about_me'])) {
        $this->about_me = $vals['about_me'];
      }
      if (isset($vals['activities'])) {
        $this->activities = $vals['activities'];
      }
      if (isset($vals['affiliations'])) {
        $this->affiliations = $vals['affiliations'];
      }
      if (isset($vals['birthday'])) {
        $this->birthday = $vals['birthday'];
      }
      if (isset($vals['books'])) {
        $this->books = $vals['books'];
      }
      if (isset($vals['current_location'])) {
        $this->current_location = $vals['current_location'];
      }
      if (isset($vals['education_history'])) {
        $this->education_history = $vals['education_history'];
      }
      if (isset($vals['first_name'])) {
        $this->first_name = $vals['first_name'];
      }
      if (isset($vals['hometown_location'])) {
        $this->hometown_location = $vals['hometown_location'];
      }
      if (isset($vals['hs_info'])) {
        $this->hs_info = $vals['hs_info'];
      }
      if (isset($vals['interests'])) {
        $this->interests = $vals['interests'];
      }
      if (isset($vals['is_app_user'])) {
        $this->is_app_user = $vals['is_app_user'];
      }
      if (isset($vals['last_name'])) {
        $this->last_name = $vals['last_name'];
      }
      if (isset($vals['meeting_for'])) {
        $this->meeting_for = $vals['meeting_for'];
      }
      if (isset($vals['meeting_sex'])) {
        $this->meeting_sex = $vals['meeting_sex'];
      }
      if (isset($vals['movies'])) {
        $this->movies = $vals['movies'];
      }
      if (isset($vals['music'])) {
        $this->music = $vals['music'];
      }
      if (isset($vals['name'])) {
        $this->name = $vals['name'];
      }
      if (isset($vals['notes_count'])) {
        $this->notes_count = $vals['notes_count'];
      }
      if (isset($vals['pic'])) {
        $this->pic = $vals['pic'];
      }
      if (isset($vals['pic_big'])) {
        $this->pic_big = $vals['pic_big'];
      }
      if (isset($vals['pic_small'])) {
        $this->pic_small = $vals['pic_small'];
      }
      if (isset($vals['political'])) {
        $this->political = $vals['political'];
      }
      if (isset($vals['profile_update_time'])) {
        $this->profile_update_time = $vals['profile_update_time'];
      }
      if (isset($vals['quotes'])) {
        $this->quotes = $vals['quotes'];
      }
      if (isset($vals['relationship_status'])) {
        $this->relationship_status = $vals['relationship_status'];
      }
      if (isset($vals['religion'])) {
        $this->religion = $vals['religion'];
      }
      if (isset($vals['sex'])) {
        $this->sex = $vals['sex'];
      }
      if (isset($vals['significant_other_id'])) {
        $this->significant_other_id = $vals['significant_other_id'];
      }
      if (isset($vals['status'])) {
        $this->status = $vals['status'];
      }
      if (isset($vals['timezone'])) {
        $this->timezone = $vals['timezone'];
      }
      if (isset($vals['tv'])) {
        $this->tv = $vals['tv'];
      }
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
      if (isset($vals['wall_count'])) {
        $this->wall_count = $vals['wall_count'];
      }
      if (isset($vals['work_history'])) {
        $this->work_history = $vals['work_history'];
      }
      if (isset($vals['pic_square'])) {
        $this->pic_square = $vals['pic_square'];
      }
      if (isset($vals['has_added_app'])) {
        $this->has_added_app = $vals['has_added_app'];
      }
    }
  }

  public function getName() {
    return 'user';
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
              $this->about_me = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->about_me);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->activities = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->activities);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->affiliations = thrift_protocol_binary_deserialize(TType::LST, $input, 'affiliation');
            }
            else
            {
              $this->affiliations = array();
              $_size7 = 0;
              $_etype10 = 0;
              $xfer += $input->readListBegin($_etype10, $_size7);
              for ($_i11 = 0; $_i11 < $_size7; ++$_i11)
              {
                $elem12 = null;
                $elem12 = new api10_affiliation();
                $xfer += $elem12->read($input);
                $this->affiliations []= $elem12;
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
              $this->birthday = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->birthday);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->books = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->books);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 6:
          if ($ftype == TType::STRUCT) {
            $this->current_location = new api10_location();
            $xfer += $this->current_location->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 7:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->education_history = thrift_protocol_binary_deserialize(TType::LST, $input, 'education_info');
            }
            else
            {
              $this->education_history = array();
              $_size13 = 0;
              $_etype16 = 0;
              $xfer += $input->readListBegin($_etype16, $_size13);
              for ($_i17 = 0; $_i17 < $_size13; ++$_i17)
              {
                $elem18 = null;
                $elem18 = new api10_education_info();
                $xfer += $elem18->read($input);
                $this->education_history []= $elem18;
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
              $this->first_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->first_name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 9:
          if ($ftype == TType::STRUCT) {
            $this->hometown_location = new api10_location();
            $xfer += $this->hometown_location->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 10:
          if ($ftype == TType::STRUCT) {
            $this->hs_info = new api10_hs_info();
            $xfer += $this->hs_info->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 11:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->interests = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->interests);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 12:
          if ($ftype == TType::BOOL) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->is_app_user = thrift_protocol_binary_deserialize(TType::BOOL, $input);
            } else {
              $xfer += $input->readBool($this->is_app_user);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 13:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->last_name = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->last_name);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 14:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->meeting_for = thrift_protocol_binary_deserialize(TType::LST, $input);
            }
            else
            {
              $this->meeting_for = array();
              $_size19 = 0;
              $_etype22 = 0;
              $xfer += $input->readListBegin($_etype22, $_size19);
              for ($_i23 = 0; $_i23 < $_size19; ++$_i23)
              {
                $elem24 = null;
                if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
                  $elem24 = thrift_protocol_binary_deserialize(TType::STRING, $input);
                } else {
                  $xfer += $input->readString($elem24);
                  }
                $this->meeting_for []= $elem24;
              }
              $xfer += $input->readListEnd();
            }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 15:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->meeting_sex = thrift_protocol_binary_deserialize(TType::LST, $input);
            }
            else
            {
              $this->meeting_sex = array();
              $_size25 = 0;
              $_etype28 = 0;
              $xfer += $input->readListBegin($_etype28, $_size25);
              for ($_i29 = 0; $_i29 < $_size25; ++$_i29)
              {
                $elem30 = null;
                if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
                  $elem30 = thrift_protocol_binary_deserialize(TType::STRING, $input);
                } else {
                  $xfer += $input->readString($elem30);
                  }
                $this->meeting_sex []= $elem30;
              }
              $xfer += $input->readListEnd();
            }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 16:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->movies = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->movies);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 17:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->music = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->music);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 18:
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
        case 19:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->notes_count = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->notes_count);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 20:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->pic = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->pic);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 21:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->pic_big = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->pic_big);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 22:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->pic_small = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->pic_small);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 23:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->political = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->political);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 24:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->profile_update_time = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->profile_update_time);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 25:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->quotes = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->quotes);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 26:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->relationship_status = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->relationship_status);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 27:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->religion = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->religion);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 28:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->sex = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->sex);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 29:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->significant_other_id = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->significant_other_id);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 30:
          if ($ftype == TType::STRUCT) {
            $this->status = new api10_user_status();
            $xfer += $this->status->read($input);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 31:
          if ($ftype == TType::DOUBLE) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->timezone = thrift_protocol_binary_deserialize(TType::DOUBLE, $input);
            } else {
              $xfer += $input->readDouble($this->timezone);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 32:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->tv = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->tv);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 33:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->uid = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->uid);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 34:
          if ($ftype == TType::I32) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->wall_count = thrift_protocol_binary_deserialize(TType::I32, $input);
            } else {
              $xfer += $input->readI32($this->wall_count);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 35:
          if ($ftype == TType::LST) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize'))
            {
              $this->work_history = thrift_protocol_binary_deserialize(TType::LST, $input, 'work_info');
            }
            else
            {
              $this->work_history = array();
              $_size31 = 0;
              $_etype34 = 0;
              $xfer += $input->readListBegin($_etype34, $_size31);
              for ($_i35 = 0; $_i35 < $_size31; ++$_i35)
              {
                $elem36 = null;
                $elem36 = new api10_work_info();
                $xfer += $elem36->read($input);
                $this->work_history []= $elem36;
              }
              $xfer += $input->readListEnd();
            }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 36:
          if ($ftype == TType::STRING) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->pic_square = thrift_protocol_binary_deserialize(TType::STRING, $input);
            } else {
              $xfer += $input->readString($this->pic_square);
              }
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 37:
          if ($ftype == TType::BOOL) {
            if (($input instanceof TProtocol::$TBINARYPROTOCOLACCELERATED) && function_exists('thrift_protocol_binary_deserialize')) {
              $this->has_added_app = thrift_protocol_binary_deserialize(TType::BOOL, $input);
            } else {
              $xfer += $input->readBool($this->has_added_app);
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
    $xfer += $output->writeStructBegin('user');
    if ($this->about_me !== null) {
      $xfer += $output->writeFieldBegin('about_me', TType::STRING, 1);
      $xfer += $output->writeString($this->about_me);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->activities !== null) {
      $xfer += $output->writeFieldBegin('activities', TType::STRING, 2);
      $xfer += $output->writeString($this->activities);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->affiliations !== null) {
      $xfer += $output->writeFieldBegin('affiliations', TType::LST, 3);
      {
        $output->writeListBegin(TType::STRUCT, count($this->affiliations));
        {
          foreach ($this->affiliations as $iter37)
          {
            $xfer += $iter37->write($output);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->birthday !== null) {
      $xfer += $output->writeFieldBegin('birthday', TType::STRING, 4);
      $xfer += $output->writeString($this->birthday);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->books !== null) {
      $xfer += $output->writeFieldBegin('books', TType::STRING, 5);
      $xfer += $output->writeString($this->books);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->current_location !== null) {
      $xfer += $output->writeFieldBegin('current_location', TType::STRUCT, 6);
      $xfer += $this->current_location->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->education_history !== null) {
      $xfer += $output->writeFieldBegin('education_history', TType::LST, 7);
      {
        $output->writeListBegin(TType::STRUCT, count($this->education_history));
        {
          foreach ($this->education_history as $iter38)
          {
            $xfer += $iter38->write($output);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->first_name !== null) {
      $xfer += $output->writeFieldBegin('first_name', TType::STRING, 8);
      $xfer += $output->writeString($this->first_name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->hometown_location !== null) {
      $xfer += $output->writeFieldBegin('hometown_location', TType::STRUCT, 9);
      $xfer += $this->hometown_location->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->hs_info !== null) {
      $xfer += $output->writeFieldBegin('hs_info', TType::STRUCT, 10);
      $xfer += $this->hs_info->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->interests !== null) {
      $xfer += $output->writeFieldBegin('interests', TType::STRING, 11);
      $xfer += $output->writeString($this->interests);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->is_app_user !== null) {
      $xfer += $output->writeFieldBegin('is_app_user', TType::BOOL, 12);
      $xfer += $output->writeBool($this->is_app_user);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->last_name !== null) {
      $xfer += $output->writeFieldBegin('last_name', TType::STRING, 13);
      $xfer += $output->writeString($this->last_name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->meeting_for !== null) {
      $xfer += $output->writeFieldBegin('meeting_for', TType::LST, 14);
      {
        $output->writeListBegin(TType::STRING, count($this->meeting_for));
        {
          foreach ($this->meeting_for as $iter39)
          {
            $xfer += $output->writeString($iter39);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->meeting_sex !== null) {
      $xfer += $output->writeFieldBegin('meeting_sex', TType::LST, 15);
      {
        $output->writeListBegin(TType::STRING, count($this->meeting_sex));
        {
          foreach ($this->meeting_sex as $iter40)
          {
            $xfer += $output->writeString($iter40);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->movies !== null) {
      $xfer += $output->writeFieldBegin('movies', TType::STRING, 16);
      $xfer += $output->writeString($this->movies);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->music !== null) {
      $xfer += $output->writeFieldBegin('music', TType::STRING, 17);
      $xfer += $output->writeString($this->music);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->name !== null) {
      $xfer += $output->writeFieldBegin('name', TType::STRING, 18);
      $xfer += $output->writeString($this->name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->notes_count !== null) {
      $xfer += $output->writeFieldBegin('notes_count', TType::I32, 19);
      $xfer += $output->writeI32($this->notes_count);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->pic !== null) {
      $xfer += $output->writeFieldBegin('pic', TType::STRING, 20);
      $xfer += $output->writeString($this->pic);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->pic_big !== null) {
      $xfer += $output->writeFieldBegin('pic_big', TType::STRING, 21);
      $xfer += $output->writeString($this->pic_big);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->pic_small !== null) {
      $xfer += $output->writeFieldBegin('pic_small', TType::STRING, 22);
      $xfer += $output->writeString($this->pic_small);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->political !== null) {
      $xfer += $output->writeFieldBegin('political', TType::STRING, 23);
      $xfer += $output->writeString($this->political);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->profile_update_time !== null) {
      $xfer += $output->writeFieldBegin('profile_update_time', TType::I32, 24);
      $xfer += $output->writeI32($this->profile_update_time);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->quotes !== null) {
      $xfer += $output->writeFieldBegin('quotes', TType::STRING, 25);
      $xfer += $output->writeString($this->quotes);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->relationship_status !== null) {
      $xfer += $output->writeFieldBegin('relationship_status', TType::STRING, 26);
      $xfer += $output->writeString($this->relationship_status);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->religion !== null) {
      $xfer += $output->writeFieldBegin('religion', TType::STRING, 27);
      $xfer += $output->writeString($this->religion);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->sex !== null) {
      $xfer += $output->writeFieldBegin('sex', TType::STRING, 28);
      $xfer += $output->writeString($this->sex);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->significant_other_id !== null) {
      $xfer += $output->writeFieldBegin('significant_other_id', TType::I32, 29);
      $xfer += $output->writeI32($this->significant_other_id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->status !== null) {
      $xfer += $output->writeFieldBegin('status', TType::STRUCT, 30);
      $xfer += $this->status->write($output);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->timezone !== null) {
      $xfer += $output->writeFieldBegin('timezone', TType::DOUBLE, 31);
      $xfer += $output->writeDouble($this->timezone);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->tv !== null) {
      $xfer += $output->writeFieldBegin('tv', TType::STRING, 32);
      $xfer += $output->writeString($this->tv);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::I32, 33);
      $xfer += $output->writeI32($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->wall_count !== null) {
      $xfer += $output->writeFieldBegin('wall_count', TType::I32, 34);
      $xfer += $output->writeI32($this->wall_count);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->work_history !== null) {
      $xfer += $output->writeFieldBegin('work_history', TType::LST, 35);
      {
        $output->writeListBegin(TType::STRUCT, count($this->work_history));
        {
          foreach ($this->work_history as $iter41)
          {
            $xfer += $iter41->write($output);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->pic_square !== null) {
      $xfer += $output->writeFieldBegin('pic_square', TType::STRING, 36);
      $xfer += $output->writeString($this->pic_square);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->has_added_app !== null) {
      $xfer += $output->writeFieldBegin('has_added_app', TType::BOOL, 37);
      $xfer += $output->writeBool($this->has_added_app);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


?>
