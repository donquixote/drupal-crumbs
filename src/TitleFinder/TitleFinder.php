<?php

namespace Drupal\crumbs\TitleFinder;

use Drupal\crumbs\PluginSystem\Engine\TitleFinderEngine;

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

}
