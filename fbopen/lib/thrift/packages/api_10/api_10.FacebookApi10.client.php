<?php
class FacebookApi10Client implements FacebookApi10If {
  protected $input_ = null;
  protected $output_ = null;

  protected $seqid_ = 0;

  public function __construct($input, $output=null) {
    $this->input_ = $input;
    $this->output_ = $output ? $output : $input;
  }

  public function auth_createToken()
  {
    $this->send_auth_createToken();
    return $this->recv_auth_createToken();
  }

  public function send_auth_createToken()
  {
    $this->output_->writeMessageBegin('auth_createToken', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_auth_createToken_args();
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_auth_createToken()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_auth_createToken_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("auth_createToken failed: unknown result");
  }

  public function auth_getSession($auth_token)
  {
    $this->send_auth_getSession($auth_token);
    return $this->recv_auth_getSession();
  }

  public function send_auth_getSession($auth_token)
  {
    $this->output_->writeMessageBegin('auth_getSession', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_auth_getSession_args();
    $args->auth_token = $auth_token;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_auth_getSession()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_auth_getSession_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("auth_getSession failed: unknown result");
  }

  public function feed_publishStoryToUser($title, $body, $image_1, $image_1_link, $image_2, $image_2_link, $image_3, $image_3_link, $image_4, $image_4_link)
  {
    $this->send_feed_publishStoryToUser($title, $body, $image_1, $image_1_link, $image_2, $image_2_link, $image_3, $image_3_link, $image_4, $image_4_link);
    return $this->recv_feed_publishStoryToUser();
  }

  public function send_feed_publishStoryToUser($title, $body, $image_1, $image_1_link, $image_2, $image_2_link, $image_3, $image_3_link, $image_4, $image_4_link)
  {
    $this->output_->writeMessageBegin('feed_publishStoryToUser', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_feed_publishStoryToUser_args();
    $args->title = $title;
    $args->body = $body;
    $args->image_1 = $image_1;
    $args->image_1_link = $image_1_link;
    $args->image_2 = $image_2;
    $args->image_2_link = $image_2_link;
    $args->image_3 = $image_3;
    $args->image_3_link = $image_3_link;
    $args->image_4 = $image_4;
    $args->image_4_link = $image_4_link;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_feed_publishStoryToUser()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_feed_publishStoryToUser_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("feed_publishStoryToUser failed: unknown result");
  }

  public function feed_publishActionOfUser($title, $body, $image_1, $image_1_link, $image_2, $image_2_link, $image_3, $image_3_link, $image_4, $image_4_link)
  {
    $this->send_feed_publishActionOfUser($title, $body, $image_1, $image_1_link, $image_2, $image_2_link, $image_3, $image_3_link, $image_4, $image_4_link);
    return $this->recv_feed_publishActionOfUser();
  }

  public function send_feed_publishActionOfUser($title, $body, $image_1, $image_1_link, $image_2, $image_2_link, $image_3, $image_3_link, $image_4, $image_4_link)
  {
    $this->output_->writeMessageBegin('feed_publishActionOfUser', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_feed_publishActionOfUser_args();
    $args->title = $title;
    $args->body = $body;
    $args->image_1 = $image_1;
    $args->image_1_link = $image_1_link;
    $args->image_2 = $image_2;
    $args->image_2_link = $image_2_link;
    $args->image_3 = $image_3;
    $args->image_3_link = $image_3_link;
    $args->image_4 = $image_4;
    $args->image_4_link = $image_4_link;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_feed_publishActionOfUser()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_feed_publishActionOfUser_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("feed_publishActionOfUser failed: unknown result");
  }

  public function friends_areFriends($uids1, $uids2)
  {
    $this->send_friends_areFriends($uids1, $uids2);
    return $this->recv_friends_areFriends();
  }

  public function send_friends_areFriends($uids1, $uids2)
  {
    $this->output_->writeMessageBegin('friends_areFriends', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_friends_areFriends_args();
    $args->uids1 = $uids1;
    $args->uids2 = $uids2;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_friends_areFriends()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_friends_areFriends_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("friends_areFriends failed: unknown result");
  }

  public function friends_get($uid, $flid)
  {
    $this->send_friends_get($uid, $flid);
    return $this->recv_friends_get();
  }

  public function send_friends_get($uid, $flid)
  {
    $this->output_->writeMessageBegin('friends_get', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_friends_get_args();
    $args->uid = $uid;
    $args->flid = $flid;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_friends_get()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_friends_get_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("friends_get failed: unknown result");
  }

  public function friends_getAppUsers()
  {
    $this->send_friends_getAppUsers();
    return $this->recv_friends_getAppUsers();
  }

  public function send_friends_getAppUsers()
  {
    $this->output_->writeMessageBegin('friends_getAppUsers', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_friends_getAppUsers_args();
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_friends_getAppUsers()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_friends_getAppUsers_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("friends_getAppUsers failed: unknown result");
  }

  public function friends_getLists()
  {
    $this->send_friends_getLists();
    return $this->recv_friends_getLists();
  }

  public function send_friends_getLists()
  {
    $this->output_->writeMessageBegin('friends_getLists', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_friends_getLists_args();
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_friends_getLists()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_friends_getLists_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("friends_getLists failed: unknown result");
  }

  public function profile_setFBML($markup, $uid, $profile, $profile_action, $mobile_profile)
  {
    $this->send_profile_setFBML($markup, $uid, $profile, $profile_action, $mobile_profile);
    return $this->recv_profile_setFBML();
  }

  public function send_profile_setFBML($markup, $uid, $profile, $profile_action, $mobile_profile)
  {
    $this->output_->writeMessageBegin('profile_setFBML', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_profile_setFBML_args();
    $args->markup = $markup;
    $args->uid = $uid;
    $args->profile = $profile;
    $args->profile_action = $profile_action;
    $args->mobile_profile = $mobile_profile;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_profile_setFBML()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_profile_setFBML_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("profile_setFBML failed: unknown result");
  }

  public function profile_getFBML($uid)
  {
    $this->send_profile_getFBML($uid);
    return $this->recv_profile_getFBML();
  }

  public function send_profile_getFBML($uid)
  {
    $this->output_->writeMessageBegin('profile_getFBML', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_profile_getFBML_args();
    $args->uid = $uid;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_profile_getFBML()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_profile_getFBML_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("profile_getFBML failed: unknown result");
  }

  public function users_getInfo($uids, $fields)
  {
    $this->send_users_getInfo($uids, $fields);
    return $this->recv_users_getInfo();
  }

  public function send_users_getInfo($uids, $fields)
  {
    $this->output_->writeMessageBegin('users_getInfo', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_users_getInfo_args();
    $args->uids = $uids;
    $args->fields = $fields;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_users_getInfo()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_users_getInfo_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("users_getInfo failed: unknown result");
  }

  public function users_isAppAdded()
  {
    $this->send_users_isAppAdded();
    return $this->recv_users_isAppAdded();
  }

  public function send_users_isAppAdded()
  {
    $this->output_->writeMessageBegin('users_isAppAdded', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_users_isAppAdded_args();
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_users_isAppAdded()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_users_isAppAdded_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("users_isAppAdded failed: unknown result");
  }

  public function users_getLoggedInUser()
  {
    $this->send_users_getLoggedInUser();
    return $this->recv_users_getLoggedInUser();
  }

  public function send_users_getLoggedInUser()
  {
    $this->output_->writeMessageBegin('users_getLoggedInUser', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_users_getLoggedInUser_args();
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_users_getLoggedInUser()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_users_getLoggedInUser_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("users_getLoggedInUser failed: unknown result");
  }

  public function admin_getAppProperties($properties)
  {
    $this->send_admin_getAppProperties($properties);
    return $this->recv_admin_getAppProperties();
  }

  public function send_admin_getAppProperties($properties)
  {
    $this->output_->writeMessageBegin('admin_getAppProperties', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_admin_getAppProperties_args();
    $args->properties = $properties;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_admin_getAppProperties()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_admin_getAppProperties_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("admin_getAppProperties failed: unknown result");
  }

  public function admin_setAppProperties($properties)
  {
    $this->send_admin_setAppProperties($properties);
    return $this->recv_admin_setAppProperties();
  }

  public function send_admin_setAppProperties($properties)
  {
    $this->output_->writeMessageBegin('admin_setAppProperties', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_admin_setAppProperties_args();
    $args->properties = $properties;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_admin_setAppProperties()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_admin_setAppProperties_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("admin_setAppProperties failed: unknown result");
  }

  public function application_getPublicInfo($application_id, $application_api_key, $application_canvas_name)
  {
    $this->send_application_getPublicInfo($application_id, $application_api_key, $application_canvas_name);
    return $this->recv_application_getPublicInfo();
  }

  public function send_application_getPublicInfo($application_id, $application_api_key, $application_canvas_name)
  {
    $this->output_->writeMessageBegin('application_getPublicInfo', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_application_getPublicInfo_args();
    $args->application_id = $application_id;
    $args->application_api_key = $application_api_key;
    $args->application_canvas_name = $application_canvas_name;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_application_getPublicInfo()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_application_getPublicInfo_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("application_getPublicInfo failed: unknown result");
  }

  public function fql_query($query)
  {
    $this->send_fql_query($query);
    $this->recv_fql_query();
  }

  public function send_fql_query($query)
  {
    $this->output_->writeMessageBegin('fql_query', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_fql_query_args();
    $args->query = $query;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_fql_query()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_fql_query_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    return;
  }

  public function batch_run($method_feed, $serial_only)
  {
    $this->send_batch_run($method_feed, $serial_only);
    return $this->recv_batch_run();
  }

  public function send_batch_run($method_feed, $serial_only)
  {
    $this->output_->writeMessageBegin('batch_run', TMessageType::CALL, $this->seqid_);
    $args = new api10_FacebookApi10_batch_run_args();
    $args->method_feed = $method_feed;
    $args->serial_only = $serial_only;
    $args->write($this->output_);
    $this->output_->writeMessageEnd();
    $this->output_->getTransport()->flush();
  }

  public function recv_batch_run()
  {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $this->input_->readMessageBegin($fname, $mtype, $rseqid);
    if ($mtype == TMessageType::EXCEPTION) {
      $x = new TApplicationException();
      $x->read($this->input_);
      $this->input_->readMessageEnd();
      throw $x;
    }
    $result = new api10_FacebookApi10_batch_run_result();
    $result->read($this->input_);
    $this->input_->readMessageEnd();

    if ($result->success !== null) {
      return $result->success;
    }
    if ($result->error_response !== null) {
      throw $result->error_response;
    }
    throw new Exception("batch_run failed: unknown result");
  }

}


?>
