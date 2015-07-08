<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
use Drupal\crumbs\ParentFinder\ParentFinderInterface;

class ParentFinderEngine implements ParentFinderInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Wrapper\Parent\ParentPluginWrapperInterface[][]
   *   Format: $[$bestWeight][] = $wrapper
   */
  private $pluginWrappersGrouped = array();

  /**
   * @param \Drupal\crumbs\PluginSystem\Wrapper\Parent\ParentPluginWrapperInterface[] $pluginWrappers
   */
  function __construct(array $pluginWrappers) {
    foreach ($pluginWrappers as $wrapper) {
      $wrapperBestWeight = $wrapper->getBestWeight();
      if ($wrapperBestWeight !== FALSE) {
        $this->pluginWrappersGrouped[$wrapperBestWeight][] = $wrapper;
      }
    }
    ksort($this->pluginWrappersGrouped);
  }

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return bool
   *   TRUE, if something was found.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {
    $path = $routerItem['link_path'];
    $bestWeight = NULL;
    foreach ($this->pluginWrappersGrouped as $wrapperBestWeight => $wrappers) {
      foreach ($wrappers as $wrapper) {
        if (isset($bestWeight) && $wrapperBestWeight >= $bestWeight) {
          return TRUE;
        }
        $wrapper->findBestParent($checker, $bestWeight, $path, $routerItem);
      }
    }

    return isset($bestWeight);
  }

}
