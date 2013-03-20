<?php

/**
 * Little brother of a dependency injection container (DIC)
 */
class crumbs_ServiceCache {

  protected $factory;
  protected $services = array();

  function __construct($factory) {
    $this->factory = $factory;
  }

  function __get($key) {
    if (!isset($this->services[$key])) {
      $this->services[$key] = $this->factory->$key($this);
      if (!isset($this->services[$key])) {
        $this->services[$key] = FALSE;
      }
    }
    return $this->services[$key];
  }

  function reset($key) {
    $this->services[$key] = NULL;
  }
}
