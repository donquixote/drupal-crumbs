<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine;

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
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   *   The normalized parent path, or NULL.
   */
  function findParent($path, array $item) {
    $route = $item['route'];
    return isset($this->routePluginEngines[$route])
      ? $this->routePluginEngines[$route]->findParent($path, $item)
      : $this->fallbackPluginEngine->findParent($path, $item);
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return string[]
   *   The normalized parent path candidates.
   */
  function findAllParents($path, array $item) {
    $route = $item['route'];
    return isset($this->routePluginEngines[$route])
      ? $this->routePluginEngines[$route]->findAllParents($path, $item)
      : $this->fallbackPluginEngine->findAllParents($path, $item);
  }

}
