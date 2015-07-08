<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
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
