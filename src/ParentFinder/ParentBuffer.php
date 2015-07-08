<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\ParentFinder\Approval\AccessChecker;
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
   * @return bool
   *   TRUE, if it was found.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {
    $path = $routerItem['link_path'];
    if (isset($this->cache[$path])) {
      if ($checker->checkRouterItem($this->cache[$path], '.Cache')) {
        return TRUE;
      }
    }
    if ($this->decorated->findParentRouterItem($routerItem, $checker)) {
      if ($checker instanceof AccessChecker) {
        $this->cache[$path] = $checker->getParentRouterItem();
      }
      return TRUE;
    }
    return FALSE;
  }
}
