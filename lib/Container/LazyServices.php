<?php

/**
 * Little brother of a dependency injection container (DIC)
 */
class crumbs_Container_LazyServices {

  /**
   * @var crumbs_ServiceFactory
   */
  protected $factory;

  /**
   * @var array
   *   Cached services
   */
  protected $services = array();

  /**
   * @param crumbs_ServiceFactory $factory
   */
  function __construct($factory) {
    $this->factory = $factory;
  }

  /**
   * @param string $key
   * @return mixed
   *   The service object for the given key.
   */
  function __get($key) {
    if (!isset($this->services[$key])) {
      $this->services[$key] = $this->factory->$key($this);
      if (!isset($this->services[$key])) {
        $this->services[$key] = FALSE;
      }
    }
    return $this->services[$key];
  }

  /**
   * @param string $key
   */
  function reset($key) {
    $this->services[$key] = NULL;
  }
}
