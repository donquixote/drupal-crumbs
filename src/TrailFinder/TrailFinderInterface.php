<?php

namespace Drupal\crumbs\TrailFinder;

interface TrailFinderInterface {

  /**
   * @param string $path
   *
   * @return array[]
   */
  function buildTrail($path);
}
