<?php

class Run {
  public $username;
  public $date;
  public $miles;
  public $route;


  function __construct($user, $params) {
    $this->date = $params['date'];
    $this->miles = $params['miles'];
    $this->route = $params['route'];
    $this->username = $user->username;
  }

  function save() {
    $ret = queryf("INSERT INTO runs (username, date, miles, route) VALUES (%s, %s, %s, %s)",
                  $this->username, $this->date, $this->miles, $this->route);
    return (bool) $ret;
  }
}

class Route {
  public $id;
  public $name;
  public $description;

  function __construct($params) {
    $this->name = $params['name'];
    $this->description = $params['description'];
  }

  function save() {
    $ret = queryf("INSERT INTO runs (username, date, miles, route) VALUES (%s, %s, %s, %s)",
                  $this->username, $this->date, $this->miles, $this->route);
    return (bool) $ret;
  }
}
