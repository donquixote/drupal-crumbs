<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface;
use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;
use Drupal\crumbs\PluginSystem\Wrapper\PluginWrapperUtil;

class EngineFactoryUtil {

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $tree
   * @param string|null $route
   *
   * @return \Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine[]|\Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine[]
   */
  static function createEnginesFromTree(TreeNodeInterface $tree, $route = NULL) {
    $wrappers_by_route = PluginWrapperUtil::treeCollectPluginWrappers($tree);
    $wrappers_by_route = PluginWrapperUtil::mergeWrappersByRoute($wrappers_by_route);
    return static::wrappersCreateEngines($wrappers_by_route, $tree->getPluginType());
  }

  /**
   * @param mixed[][] $wrappers_by_route
   *   Format: $[$route][$key] = $pluginWrapper
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return \Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine[]|\Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine[]
   */
  static function wrappersCreateEngines(array $wrappers_by_route, PluginTypeInterface $pluginType) {
    $engines_by_route = array();
    if ($pluginType instanceof ParentPluginType) {
      foreach ($wrappers_by_route as $route => $wrappers) {
        $engines_by_route[$route] = new ParentFinderEngine($wrappers);
      }
    }
    else {
      foreach ($wrappers_by_route as $route => $wrappers) {
        $engines_by_route[$route] = new TitleFinderEngine($wrappers);
      }
    }
    return $engines_by_route;
  }

}
