<?php

namespace Drupal\crumbs\TrailFinder;

class TrailAccessFilter extends TrailFinderDecoratorBase {

  /**
   * @param string $path
   *
   * @return array[]
   */
  function buildTrail($path) {
    $trail = $this->decorated->buildTrail($path);
    foreach ($trail as $path => $item) {
      if (empty($item['access'])) {
        unset($trail[$path]);
      }
    }
    return $trail;
  }
}
