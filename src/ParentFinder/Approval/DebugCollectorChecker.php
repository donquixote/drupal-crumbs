<?php

namespace Drupal\crumbs\ParentFinder\Approval;



class DebugCollectorChecker implements CheckerInterface {

  /**
   * @var array[]
   */
  private $collected = array();

  /**
   * @return array[]
   */
  public function getCollected() {
    return $this->collected;
  }

  /**
   * @param string $path
   *   A parent path candidate.
   * @param string $key
   *   The plugin key for this path.
   *
   * @return array|NULL
   *   The router item for the given path, or NULL.
   */
  function checkParentPath($path, $key) {
    $this->collected[] = array($path, $key);
  }

  /**
   * @param array $routerItem
   *   A router item.
   * @param string $key
   *   The plugin key for this router item.
   *
   * @return bool
   *   TRUE, if the router item is accepted in the breadcrumb trail.
   */
  function checkRouterItem(array $routerItem, $key) {
    $this->collected[] = array($routerItem['link_path'], $key);
  }
}
