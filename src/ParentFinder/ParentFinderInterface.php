<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;

interface ParentFinderInterface {

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return bool
   *   TRUE, if it was found.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker);

}
