<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Parent;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;

interface ParentPluginWrapperInterface {

  /**
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestParent(CheckerInterface $checker, &$bestWeight, $path, array $routerItem);

  /**
   * @return int|false
   */
  function getBestWeight();
}
