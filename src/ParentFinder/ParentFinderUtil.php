<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface;
use Drupal\crumbs\PluginSystem\Engine\EngineFactoryUtil;

class ParentFinderUtil {

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $tree
   *
   * @return \Drupal\crumbs\ParentFinder\ParentFinder
   */
  public static function createParentFinder(TreeNodeInterface $tree) {
    $routePluginEngines = EngineFactoryUtil::createEnginesFromTree($tree);
    $fallbackPluginEngine = $routePluginEngines['#'];
    unset($routePluginEngines['#']);

    return new ParentFinder($routePluginEngines, $fallbackPluginEngine);
  }

}
