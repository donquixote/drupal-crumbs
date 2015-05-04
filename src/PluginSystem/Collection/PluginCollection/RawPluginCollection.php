<?php

namespace Drupal\crumbs\PluginSystem\Collection\PluginCollection;

/**
 * Collection object that collects plugins of a specific type, that is, either
 * parent-finding or title-finding plugins.
 */
class RawPluginCollection implements PluginCollectionInterface {

  /**
   * @var bool[]
   */
  private $defaultStatuses = array();

  /**
   * @var \crumbs_PluginInterface[]
   *   Format: $[$pluginKey] = $plugin
   */
  private $routelessPlugins = array();

  /**
   * @var \crumbs_PluginInterface[][]
   *   Format: $[$route][$pluginKey] = $plugin
   */
  private $routePlugins = array();

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   * @param string|null $route
   */
  function addMonoPlugin($key, \crumbs_MonoPlugin $plugin, $route = NULL) {
    if (isset($route)) {
      $this->routePlugins[$route][$key] = $plugin;
    }
    else {
      $this->routelessPlugins[$key] = $plugin;
    }
  }

  /**
   * @param string $key
   *   The plugin key, without the '.*'.
   * @param \crumbs_MultiPlugin $plugin
   * @param null $route
   */
  function addMultiPlugin($key, \crumbs_MultiPlugin $plugin, $route = NULL) {
    if (isset($route)) {
      $this->routePlugins[$route][$key] = $plugin;
    }
    else {
      $this->routelessPlugins[$key] = $plugin;
    }
  }

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status) {
    $this->defaultStatuses[$key] = $status;
  }

  /**
   * @param string $key
   * @param string $description
   */
  public function addDescription($key, $description) {
    // Do nothing.
  }

  /**
   * @param string $key
   * @param string $description
   *   The description in English.
   * @param string[] $args
   *   Placeholders to be inserted into the translated description.
   *
   * @see t()
   * @see format_string()
   */
  public function translateDescription($key, $description, $args = array()) {
    // Do nothing.
  }

  /**
   * @return bool[]
   */
  public function getDefaultStatuses() {
    return $this->defaultStatuses;
  }

  /**
   * @return \crumbs_PluginInterface[][]
   */
  public function getRoutePluginsByRoute() {
    return $this->routePlugins;
  }

  /**
   * @return \crumbs_PluginInterface[]
   */
  public function getRoutelessPlugins() {
    return $this->routelessPlugins;
  }
}
