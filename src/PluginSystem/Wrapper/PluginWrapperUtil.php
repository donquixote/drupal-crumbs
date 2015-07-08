<?php

namespace Drupal\crumbs\PluginSystem\Wrapper;

use Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface;
use Drupal\crumbs\PluginSystem\Tree\TreeUtil;
use Drupal\crumbs\PluginSystem\Weights\TrivialWeightsFamily;
use Drupal\crumbs\PluginSystem\Weights\WeightsFamilyUtil;
use Drupal\crumbs\PluginSystem\Wrapper\Parent\ParentMonoPluginWrapper;
use Drupal\crumbs\PluginSystem\Wrapper\Parent\ParentMultiPluginWrapper;
use Drupal\crumbs\PluginSystem\Wrapper\Parent\TrivialParentMultiPluginWrapper;
use Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface;
use Drupal\crumbs\PluginSystem\Wrapper\Title\TitleMonoPluginWrapper;
use Drupal\crumbs\PluginSystem\Wrapper\Title\TitleMultiPluginWrapper;
use Drupal\crumbs\PluginSystem\Wrapper\Title\TrivialTitleMultiPluginWrapper;

class PluginWrapperUtil {

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $tree
   *
   * @return mixed[][]
   *   Format: $[$route][$key] = $pluginWrapper|null
   */
  static function treeCollectPluginWrappers(TreeNodeInterface $tree) {
    $wrappers_by_route = array('#' => array());
    foreach (TreeUtil::treeCollectPluginPositions($tree) as $position) {
      $key = $position->getKey();
      $wrapper = NULL;
      if ($treeNode = $position->getTreeNode()) {
        if ($plugin = $treeNode->getPlugin()) {
          $wrapper = static::createPluginWrapper($plugin, $position);
        }
        foreach ($treeNode->getRoutePlugins() as $route => $plugin) {
          $wrappers_by_route[$route][$key] = static::createPluginWrapper($plugin, $position);
        }
      }
      $wrappers_by_route['#'][$key] = $wrapper;
    }

    return $wrappers_by_route;
  }

  /**
   * @param mixed[][] $wrappers_by_route
   *   Format: $[$route][$key] = $pluginWrapper|null
   *
   * @return mixed[][]
   *   Format: $[$route][$key] = $pluginWrapper
   */
  static function mergeWrappersByRoute(array $wrappers_by_route) {
    $wrappers_routeless = $wrappers_by_route['#'];
    unset($wrappers_by_route['#']);
    foreach ($wrappers_by_route as $route => $wrappers) {
      $wrappers_new = $wrappers_routeless;
      foreach ($wrappers as $key => $wrapper) {
        // Overwrite generic plugin wrapper, but keep the order.
        $wrappers_new[$key] = $wrapper;
      }
      $wrappers_by_route[$route] = array_filter($wrappers_new);
    }
    $wrappers_by_route['#'] = array_filter($wrappers_routeless);
    return $wrappers_by_route;
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $position
   *
   * @return object
   * @throws \InvalidArgumentException
   */
  static function createPluginWrapper(\crumbs_PluginInterface $plugin, TreePositionInterface $position) {

    if ($plugin instanceof \crumbs_MultiPlugin) {
      $keyPrefix = $position->getKeyPrefix();
      $weightsFamily = WeightsFamilyUtil::createFromTreePosition($position);
      if ($plugin instanceof \crumbs_MultiPlugin_FindParentInterface) {
        if ($weightsFamily instanceof TrivialWeightsFamily) {
          return new TrivialParentMultiPluginWrapper($keyPrefix, $plugin, $weightsFamily->getBestWeight());
        }
        return new ParentMultiPluginWrapper($keyPrefix, $plugin, $weightsFamily);
      }
      elseif ($plugin instanceof \crumbs_MultiPlugin_FindTitleInterface) {
        if ($weightsFamily instanceof TrivialWeightsFamily) {
          return new TrivialTitleMultiPluginWrapper($keyPrefix, $plugin, $weightsFamily->getBestWeight());
        }
        return new TitleMultiPluginWrapper($keyPrefix, $plugin, $weightsFamily);
      }
    }
    elseif ($plugin instanceof \crumbs_MonoPlugin) {
      $key = $position->getKey();
      $weight = $position->getWeightOrFalse();
      if ($plugin instanceof \crumbs_MonoPlugin_FindParentInterface) {
        return new ParentMonoPluginWrapper($key, $plugin, $weight);
      }
      elseif ($plugin instanceof \crumbs_MonoPlugin_FindTitleInterface) {
        return new TitleMonoPluginWrapper($key, $plugin, $weight);
      }
    }

    throw new \InvalidArgumentException('Unsupported plugin type.');
  }

}
