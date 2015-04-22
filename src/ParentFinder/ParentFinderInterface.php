<?php

namespace Drupal\crumbs\ParentFinder;

interface ParentFinderInterface {

  /**
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   */
  function findParent($path, array $item);

}
