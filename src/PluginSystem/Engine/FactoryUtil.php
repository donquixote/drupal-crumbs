<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection;
use Drupal\crumbs\ParentFinder\ParentFinder;
use Drupal\crumbs\PluginSystem\Plugin\FindParentMultiPluginOffset;
use Drupal\crumbs\PluginSystem\Plugin\FindTitleMultiPluginOffset;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs\Router\RouterInterface;
use Drupal\crumbs\TitleFinder\TitleFinder;

class FactoryUtil {

  /**
   * @param RawPluginCollection $pluginCollection
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $statusMap
   * @param \crumbs_Router|\Drupal\crumbs\Router\RouterInterface $router
   *
   * @return \Drupal\crumbs\ParentFinder\ParentFinder
   * @throws \Exception
   */
  static function createParentFinder(
    RawPluginCollection $pluginCollection,
    PluginStatusWeightMap $statusMap,
    RouterInterface $router
  ) {
    $fallbackPluginsByWeight = static::groupParentPluginsByWeight(
      $pluginCollection->getRoutelessPlugins(),
      $statusMap);

    $fallbackPluginsSorted = static::flattenPluginsByWeight($fallbackPluginsByWeight);

    $fallbackPluginEngine = new ParentFinderEngine($fallbackPluginsSorted, $router);

    $routePluginEngines = array();
    foreach ($pluginCollection->getRoutePluginsByRoute() as $route => $plugins) {
      $pluginsByWeight = static::groupParentPluginsByWeight($plugins, $statusMap);
      foreach ($fallbackPluginsByWeight as $weight => $fallbackPlugins) {
        if (isset($pluginsByWeight[$weight])) {
          $pluginsByWeight[$weight] += $fallbackPlugins;
        }
        else {
          $pluginsByWeight[$weight] = $fallbackPlugins;
        }
      }
      $routePluginsSorted = static::flattenPluginsByWeight($pluginsByWeight);
      $routePluginEngines[$route] = new ParentFinderEngine($routePluginsSorted, $router);
    }

    return new ParentFinder($routePluginEngines, $fallbackPluginEngine);
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $pluginCollection
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $statusMap
   *
   * @return \Drupal\crumbs\TitleFinder\TitleFinder
   * @throws \Exception
   */
  static function createTitleFinder(
    RawPluginCollection $pluginCollection,
    PluginStatusWeightMap $statusMap
  ) {
    $fallbackPluginsByWeight = static::groupTitlePluginsByWeight(
      $pluginCollection->getRoutelessPlugins(),
      $statusMap);

    $fallbackPluginsSorted = static::flattenPluginsByWeight($fallbackPluginsByWeight);

    $fallbackPluginEngine = new TitleFinderEngine($fallbackPluginsSorted);

    $routePluginEngines = array();
    foreach ($pluginCollection->getRoutePluginsByRoute() as $route => $plugins) {
      $pluginsByWeight = static::groupTitlePluginsByWeight($plugins, $statusMap);
      foreach ($fallbackPluginsByWeight as $weight => $fallbackPlugins) {
        if (isset($pluginsByWeight[$weight])) {
          $pluginsByWeight[$weight] += $fallbackPlugins;
        }
        else {
          $pluginsByWeight[$weight] = $fallbackPlugins;
        }
      }
      $routePluginsSorted = static::flattenPluginsByWeight($pluginsByWeight);
      $routePluginEngines[$route] = new TitleFinderEngine($routePluginsSorted);
    }

    return new TitleFinder($routePluginEngines, $fallbackPluginEngine);
  }

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
