<?php

namespace Drupal\crumbs\PluginApi\Offset;

class IllegalTreeOffset implements TreeOffsetMetaInterface {

  /**
   * @var string
   */
  private $message;

  /**
   * @param string $message
   */
  function __construct($message) {
    $this->message = $message;
  }

  /**
   * @param string $description
   *
   * @return $this
   * @throws \Exception
   */
  function describe($description) {
    throw new \Exception($this->message);
  }

  /**
   * @param string $description
   * @param string[] $args
   *
   * @return $this
   * @throws \Exception
   */
  function translateDescription($description, $args = array()) {
    throw new \Exception($this->message);
  }

  /**
   * @return $this
   * @throws \Exception
   */
  function disabledByDefault() {
    throw new \Exception($this->message);
  }
}
