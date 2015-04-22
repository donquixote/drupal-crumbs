<?php

namespace Drupal\crumbs\TrailFinder;

class TrailUnreverse extends TrailFinderDecoratorBase {

  /**
   * @param string $path
   *
   * @return array[]
   */
  function buildTrail($path) {
    $reverse_trail = $this->decorated->buildTrail($path);
    return array_reverse($reverse_trail);
  }
}
