<?php


class crumbs_PluginSystem_PluginBag {

  /**
   * @var crumbs_PluginInterface[]
   */
  protected $plugins;

  /**
   * @var string[][]
   *   Format: $['findParent'][$plugin_key] = $method
   */
  protected $routelessPluginMethods = array();

  /**
   * @var string[][][]
   *   Format: $['findParent'][$route][$plugin_key] = $method
   */
  protected $routePluginMethods = array();

  /**
   * @param crumbs_PluginInterface[] $plugins
   * @param string[][] $routelessPluginMethods
   * @param string[][][] $routePluginMethods
   */
  function __construct($plugins, $routelessPluginMethods, $routePluginMethods) {
    $this->plugins = $plugins;
    $this->routelessPluginMethods = $routelessPluginMethods;
    $this->routePluginMethods = $routePluginMethods;
  }

  /**
   * @return crumbs_MonoPlugin
   */
  function getDecorateBreadcrumbPlugins() {
    $plugin_methods = isset($this->routelessPluginMethods['decorateBreadcrumb'])
      ? $this->routelessPluginMethods['decorateBreadcrumb']
      : array();
    $plugins = array();
    foreach ($plugin_methods as $pluginKey => $method) {
      if (!isset($this->plugins[$pluginKey])) {
        continue;
      }
      $plugins[$pluginKey] = $this->plugins[$pluginKey];
    }
    return $plugins;
  }

  /**
   * @param $base_method_name
   * @param $route
   *
   * @return crumbs_PluginSystem_PluginMethodIterator
   */
  function getRoutePluginMethodIterator($base_method_name, $route) {
    $methods = $this->getRoutePluginMethods($base_method_name, $route);
    return new crumbs_PluginSystem_PluginMethodIterator($methods, $this->plugins);
  }

  /**
   * @param string $base_method_name
   *   Either 'findParent' or 'findTitle' or 'decorateBreadcrumb'.
   * @param string $route
   *   A route, e.g. 'node/%'.
   *
   * @return string[]
   *   Format: $[$plugin_key] = $method.
   */
  private function getRoutePluginMethods($base_method_name, $route) {
    if (isset($this->routePluginMethods[$base_method_name][$route])) {
      return $this->routePluginMethods[$base_method_name][$route];
    }
    if (isset($this->routelessPluginMethods[$base_method_name])) {
      return $this->routelessPluginMethods[$base_method_name];
    }
    return array();
  }

} 
