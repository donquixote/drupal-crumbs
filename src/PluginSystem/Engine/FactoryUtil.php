<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\PluginSystem\Plugin\FindParentMultiPluginOffset;
use Drupal\crumbs\PluginSystem\Plugin\FindTitleMultiPluginOffset;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;

class FactoryUtil {

  /**
   * @param \crumbs_PluginInterface[][] $pluginsByWeight
   *
   * @return \crumbs_PluginInterface[]
   */
  static function flattenPluginsByWeight(array $pluginsByWeight) {
    $pluginsSorted = array();
    foreach ($pluginsByWeight as $weight => $plugins) {
      $pluginsSorted += $plugins;
    }
    return $pluginsSorted;
  }

  /**
   * @param \crumbs_PluginInterface[] $plugins
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $statusMap
   *
   * @return \crumbs_MonoPlugin_FindParentInterface[][]
   *
   * @throws \Exception
   */
  static function groupParentPluginsByWeight(array $plugins, PluginStatusWeightMap $statusMap) {
    $pluginsByWeight = array();
    foreach ($plugins as $key => $plugin) {
      if ($plugin instanceof \crumbs_MonoPlugin_FindParentInterface) {
        if (FALSE !== $weight = $statusMap->keyGetWeightOrFalse($key)) {
          $pluginsByWeight[$weight][$key] = $plugin;
        }
      }
      elseif ($plugin instanceof \crumbs_MultiPlugin_FindParentInterface) {
        $localStatusMap = $statusMap->getLocalStatusMap($key);
        foreach ($localStatusMap->getDistinctWeights() as $weight) {
          $pluginsByWeight[$weight][$key] = new FindParentMultiPluginOffset($plugin, $key, $localStatusMap, $weight);
        }
      }
      else {
        throw new \Exception("Invalid plugin type.");
      }
    }
    return $pluginsByWeight;
  }

  /**
   * @param \crumbs_PluginInterface[] $plugins
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $statusMap
   *
   * @return \crumbs_MonoPlugin_FindTitleInterface[][]
   *
   * @throws \Exception
   */
  static function groupTitlePluginsByWeight(array $plugins, PluginStatusWeightMap $statusMap) {
    $pluginsByWeight = array();
    foreach ($plugins as $key => $plugin) {
      if ($plugin instanceof \crumbs_MonoPlugin_FindTitleInterface) {
        if (FALSE !== $weight = $statusMap->keyGetWeightOrFalse($key)) {
          $pluginsByWeight[$weight][$key] = $plugin;
        }
      }
      elseif ($plugin instanceof \crumbs_MultiPlugin_FindTitleInterface) {
        $localStatusMap = $statusMap->getLocalStatusMap($key);
        foreach ($localStatusMap->getDistinctWeights() as $weight) {
          $pluginsByWeight[$weight][$key] = new FindTitleMultiPluginOffset($plugin, $key, $localStatusMap, $weight);
        }
      }
      else {
        throw new \Exception("Invalid plugin type.");
      }
    }
    return $pluginsByWeight;
  }
}
