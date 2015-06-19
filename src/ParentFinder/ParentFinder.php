<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollection;
use Drupal\crumbs\PluginSystem\Engine\FactoryUtil;
use Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs\Router\RouterInterface;

class ParentFinder implements ParentFinderInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine[]
   */
  private $routePluginEngines;

  /**
   * @var \Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine
   */
  private $fallbackPluginEngine;

  /**
   * @param \Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine[] $routePluginEngines
   * @param \Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine $fallbackPluginEngine
   */
  function __construct(array $routePluginEngines, ParentFinderEngine $fallbackPluginEngine) {
    $this->routePluginEngines = $routePluginEngines;
    $this->fallbackPluginEngine = $fallbackPluginEngine;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollection $pluginCollection
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $statusMap
   * @param \Drupal\crumbs\Router\RouterInterface $router
   *
   * @return static
   */
  public static function create(
    PluginCollection $pluginCollection,
    PluginStatusWeightMap $statusMap,
    RouterInterface $router
  ) {
    $fallbackPluginsByWeight = FactoryUtil::groupParentPluginsByWeight(
      $pluginCollection->getRoutelessPlugins(),
      $statusMap
    );

    $fallbackPluginsSorted = FactoryUtil::flattenPluginsByWeight($fallbackPluginsByWeight);

    $fallbackPluginEngine = new ParentFinderEngine($fallbackPluginsSorted, $router);

    $routePluginEngines = array();
    foreach ($pluginCollection->getRoutePluginsByRoute() as $route => $plugins) {
      $pluginsByWeight = FactoryUtil::groupParentPluginsByWeight($plugins, $statusMap);
      foreach ($fallbackPluginsByWeight as $weight => $fallbackPlugins) {
        if (isset($pluginsByWeight[$weight])) {
          $pluginsByWeight[$weight] += $fallbackPlugins;
        }
        else {
          $pluginsByWeight[$weight] = $fallbackPlugins;
        }
      }
      $routePluginsSorted = FactoryUtil::flattenPluginsByWeight($pluginsByWeight);
      $routePluginEngines[$route] = new ParentFinderEngine($routePluginsSorted, $router);
    }

    return new static($routePluginEngines, $fallbackPluginEngine);
  }

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return array|NULL
   *   The parent router item, or NULL.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {
    $route = $routerItem['route'];
    return isset($this->routePluginEngines[$route])
      ? $this->routePluginEngines[$route]->findParentRouterItem($routerItem, $checker)
      : $this->fallbackPluginEngine->findParentRouterItem($routerItem, $checker);
  }

}
