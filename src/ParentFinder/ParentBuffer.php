<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;

/**
 * Runtime cache for parent-finding.
 */
class ParentBuffer extends ParentFinderDecoratorBase {

  /**
   * @var (array|NULL)[]
   *   Format: $[$path] = $parentRouterItem
   */
  private $cache = array();

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return array|NULL
   *   The parent router item, or NULL.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {
    $path = $routerItem['link_path'];
    return isset($this->cache[$path])
      ? $this->cache[$path]
      : $this->cache[$path] = $this->decorated->findParentRouterItem($routerItem, $checker);
  }
}
