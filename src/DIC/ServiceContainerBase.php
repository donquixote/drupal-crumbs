<?php

namespace Drupal\crumbs\DIC;

use Exception;

/**
 * Dependency injection container for lazy-instantiated services.
 */
abstract class ServiceContainerBase {

  /**
   * @var object[]
   */
  private $services = array();

  /**
   * Magic method that is triggered when someone calls $container->$name.
   *
   * @param string $name
   *   The machine name of the service.
   *   Must be a valid PHP identifier, without commas and such.
   *
   * @return object
   */
  function __get($name) {
    return isset($this->services[$name])
      ? $this->services[$name]
      // Create the service, if it does not already exist.
      : $this->services[$name] = $this->createService($name);
  }

  /**
   * @param string $name
   *
   * @return object
   *   The service.
   * @throws Exception
   */
  private function createService($name) {
    // Method to be implemented in a subclass.
    $method = $name;
    if (!method_exists($this, $method)) {
      $trace = debug_backtrace(FALSE, 3);
      $line = $trace[1]['line'];
      $file = $trace[1]['file'];
      if (!isset($trace[2]['function'])) {
        $caller = '??';
      }
      elseif (!isset($trace[2]['class'])) {
        $caller = $trace[2]['function'] . '()';
      }
      else {
        $caller = $trace[2]['class'] . $trace[2]['type'] . $trace[2]['function'] . '()';
      }
      throw new \Exception("Unknown service '$name' requested in $caller. (Line $line of $file).");
    }
    return $this->$method();
  }

}
