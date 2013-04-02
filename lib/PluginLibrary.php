<?php


class crumbs_PluginLibrary {

  // Set in constructor.
  protected $pluginOrder_find = array();
  protected $pluginOrder_alter = array();

  // Lazy-filled when needed.
  protected $basicFinderPluginMethods = array();
  protected $routeFinderPluginMethods = array();

  /**
   * @param array $plugins
   *   Plugins, not sorted.
   * @param crumbs_RuleWeightKeeper $weight_keeper
   *   Object that can determine weights for every plugin.
   */
  function __construct($plugins, $weight_keeper) {

    // Sort the plugins, using the weights from weight keeper.
    foreach ($plugins as $plugin_key => $plugin) {
      if ($plugin instanceof crumbs_MultiPlugin) {
        $keeper = $weight_keeper->prefixedWeightKeeper($plugin_key);
        $w_find = $keeper->getSmallestWeight();
        if ($w_find !== FALSE) {
          $this->pluginOrder_find[$plugin_key] = $w_find;
        }
        $w_alter = $keeper->findWeight();
        if ($w_alter !== FALSE) {
          $this->pluginOrder_alter[$plugin_key] = $w_alter;
        }
      }
      else {
        $weight = $weight_keeper->findWeight($plugin_key);
        if ($weight !== FALSE) {
          $this->pluginOrder_find[$plugin_key] = $weight;
          $this->pluginOrder_alter[$plugin_key] = $weight;
        }
      }
    }

    // Lowest weight first = highest priority first
    asort($this->pluginOrder_find);
    foreach ($this->pluginOrder_find as $plugin_key => $weight) {
      $this->pluginOrder_find[$plugin_key] = $plugins[$plugin_key];
    }

    // Lowest weight last = highest priority last
    arsort($this->pluginOrder_alter);
    foreach ($this->pluginOrder_alter as $plugin_key => $weight) {
      $this->pluginOrder_alter[$plugin_key] = $plugins[$plugin_key];
    }

    // Load stuff from cache.
    $this->load();
  }

  /**
   * Load from cache
   */
  function load() {
    $cache = cache_get('crumbs:pluginMethods');
    if ($cache && isset($cache->data)) {
      $this->basicFinderPluginMethods = $cache->data['basic'];
      $this->routeFinderPluginMethods = $cache->data['by_route'];
    }
  }

  /**
   * Save to cache
   */
  function save() {
    cache_set('crumbs:pluginMethods', array(
      'basic' => $this->basicFinderPluginMethods,
      'by_route' => $this->routeFinderPluginMethods,
    ));
  }

  /**
   * Get plugin methods for findParent() / findTitle(), any route.
   *
   * @param string $method
   *   Either 'findParent' or 'findTitle'.
   */
  function basicFinderPluginMethods($method) {
    if (!isset($this->basicFinderPluginMethods[$method])) {
      $this->basicFinderPluginMethods = array();
      foreach ($this->pluginOrder_find as $plugin_key => $plugin) {
        if (method_exists($plugin, $method)) {
          $this->basicFinderPluginMethods[$method][$plugin_key] = $method;
        }
      }
    }
    return $this->basicFinderPluginMethods[$method];
  }

  /**
   * Get plugin methods for findParent() / findTitle(), specific route.
   *
   * @param string $method
   *   Either 'findParent' or 'findTitle'.
   * @param string $route
   *   The route, e.g. "node/%".
   *
   * @return array
   *   Either an array of plugin methods,
   *   or FALSE, if we should use the basic instead.
   */
  function routeFinderPluginMethods($method, $route) {
    if (!isset($this->routeFinderPluginMethods[$method][$route])) {
      $result = $this->basicFinderPluginMethods($method);
      $basic = TRUE;
      if (!empty($route)) {
        $method_suffix = crumbs_Util::buildMethodSuffix($route);
        if (!empty($method_suffix)) {
          $method_with_suffix = $method . '__' . $method_suffix;
          foreach ($this->pluginOrder_find as $plugin_key => $plugin) {
            if (method_exists($plugin, $method_with_suffix)) {
              $result[$plugin_key] = $method_with_suffix;
              $basic = FALSE;
            }
          }
        }
      }
      $this->routeFinderPluginMethods[$method][$route] = $basic ? FALSE : $result;
      $this->save();
    }
    $return = $this->routeFinderPluginMethods[$method][$route];
    if (FALSE === $return) {
      return $this->basicFinderPluginMethods($method);
    }
    else {
      return $return;
    }
  }
}
