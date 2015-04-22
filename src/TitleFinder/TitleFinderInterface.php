<?php

namespace Drupal\crumbs\TitleFinder;

interface TitleFinderInterface {

  /**
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   */
  function findTitle($path, array $item);

}
