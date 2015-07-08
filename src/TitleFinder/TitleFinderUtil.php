<?php

namespace Drupal\crumbs\TitleFinder;

use Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface;
use Drupal\crumbs\PluginSystem\Engine\EngineFactoryUtil;

class TitleFinderUtil {

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $tree
   *
   * @return \Drupal\crumbs\TitleFinder\TitleFinder
   */
  public static function createTitleFinder(TreeNodeInterface $tree) {
    $routePluginEngines = EngineFactoryUtil::createEnginesFromTree($tree);
    $fallbackPluginEngine = $routePluginEngines['#'];
    unset($routePluginEngines['#']);

    return new TitleFinder($routePluginEngines, $fallbackPluginEngine);
  }

}
