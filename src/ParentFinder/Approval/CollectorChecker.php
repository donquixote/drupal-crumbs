<?php

namespace Drupal\crumbs\ParentFinder\Approval;

class CollectorChecker implements CheckerInterface {

  /**
   * @var string[][]
   */
  private $collectedPaths = array();

  /**
   * @return string[][]
   */
  public function getCollectedPaths() {
    return $this->collectedPaths;
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
    $this->collectedPaths[] = array($path, $key);
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
    $this->collectedPaths[] = array($routerItem['link_path'], $key);
  }
}
