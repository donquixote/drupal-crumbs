<?php

namespace Drupal\crumbs\TitleFinder;

use Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollection;
use Drupal\crumbs\PluginSystem\Engine\FactoryUtil;
use Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;

class TitleFinder implements TitleFinderInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine[]
   */
  private $routePluginEngines;

  /**
   * @var \Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine
   */
  private $fallbackPluginEngine;

  /**
   * @param \Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine[] $routePluginEngines
   * @param \Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine $fallbackPluginEngine
   */
  function __construct(array $routePluginEngines, TitleFinderEngine $fallbackPluginEngine) {
    $this->routePluginEngines = $routePluginEngines;
    $this->fallbackPluginEngine = $fallbackPluginEngine;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollection $pluginCollection
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $statusMap
   *
   * @return static
   */
  public static function create(
    PluginCollection $pluginCollection,
    PluginStatusWeightMap $statusMap
  ) {
    $fallbackPluginsByWeight = FactoryUtil::groupTitlePluginsByWeight(
      $pluginCollection->getRoutelessPlugins(),
      $statusMap
    );

    $fallbackPluginsSorted = FactoryUtil::flattenPluginsByWeight($fallbackPluginsByWeight);

    $fallbackPluginEngine = new TitleFinderEngine($fallbackPluginsSorted);

    $routePluginEngines = array();
    foreach ($pluginCollection->getRoutePluginsByRoute() as $route => $plugins) {
      $pluginsByWeight = FactoryUtil::groupTitlePluginsByWeight($plugins, $statusMap);
      foreach ($fallbackPluginsByWeight as $weight => $fallbackPlugins) {
        if (isset($pluginsByWeight[$weight])) {
          $pluginsByWeight[$weight] += $fallbackPlugins;
        }
        else {
          $pluginsByWeight[$weight] = $fallbackPlugins;
        }
      }
      $routePluginsSorted = FactoryUtil::flattenPluginsByWeight($pluginsByWeight);
      $routePluginEngines[$route] = new TitleFinderEngine($routePluginsSorted);
    }

    return new static($routePluginEngines, $fallbackPluginEngine);
  }

  /**
   * @param string $path
   * @param array $item
   * @param array $breadcrumb
   *
   * @return NULL|string The breadcrumb link title, or NULL.
   * The breadcrumb link title, or NULL.
   */
  function findTitle($path, array $item, array $breadcrumb = array()) {
    $route = $item['route'];
    return isset($this->routePluginEngines[$route])
      ? $this->routePluginEngines[$route]->findTitle($path, $item, $breadcrumb)
      : $this->fallbackPluginEngine->findTitle($path, $item, $breadcrumb);
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return string[]
   *   The link title candidates.
   */
  function findAllTitles($path, array $item) {
    $route = $item['route'];
    return isset($this->routePluginEngines[$route])
      ? $this->routePluginEngines[$route]->findAllTitles($path, $item)
      : $this->fallbackPluginEngine->findAllTitles($path, $item);
  }

}
