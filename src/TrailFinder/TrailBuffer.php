<?php

namespace Drupal\crumbs\TrailFinder;

class TrailBuffer extends TrailFinderDecoratorBase {

  /**
   * @var array[][]
   */
  private $cache = array();

  /**
   * @param string $path
   *
   * @return array[]
   */
  function buildTrail($path) {
    return isset($this->cache[$path])
      ? $this->cache[$path]
      : $this->cache[$path] = $this->decorated->buildTrail($path);
  }
}
