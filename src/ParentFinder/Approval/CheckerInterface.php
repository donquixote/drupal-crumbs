<?php

namespace Drupal\crumbs\ParentFinder\Approval;

interface CheckerInterface {

  /**
   * @param array $routerItem
   *   A router item.
   * @param string $key
   *   The plugin key for this router item.
   *
   * @return bool
   *   TRUE, if the router item is accepted in the breadcrumb trail.
   */
  function checkRouterItem(array $routerItem, $key);

  /**
   * @param string $path
   *   A parent path candidate.
   * @param string $key
   *   The plugin key for this path.
   *
   * @return bool
   *   TRUE, if the router item exists and is accepted in the breadcrumb trail.
   */
  function checkParentPath($path, $key);
}
