<?php

namespace Drupal\crumbs\ParentFinder;

/**
 * Runtime cache for parent-finding.
 */
class ParentBuffer extends ParentFinderDecoratorBase {

  /**
   * @var (string|NULL)[]
   *   Format: $[$path] = $parentPath
   */
  private $cache = array();

  /**
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   *   The normalized parent path, or NULL.
   */
  function findParent($path, array $item) {
    return isset($this->cache[$path])
      ? $this->cache[$path]
      : $this->cache[$path] = $this->decorated->findParent($path, $item);
  }
}
