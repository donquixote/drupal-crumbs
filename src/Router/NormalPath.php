<?php

namespace Drupal\crumbs\Router;

/**
 * Immutable data object for Drupal normal path.
 */
class NormalPath {

  /**
   * @var string
   */
  private $normalpath;

  /**
   * @param string $normalpath
   */
  function __construct($normalpath) {
    $this->normalpath = $normalpath;
  }
}
