<?php

namespace Drupal\crumbs\ParentFinder\Approval;

interface CheckerInterface {

  /**
   * @param string $path
   *   A parent path candidate.
   * @param string $key
   *   The plugin key for this path.
   *
   * @return array|NULL
   *   The router item for the given path, or NULL.
   */
  function checkParentPath($path, $key);
}
