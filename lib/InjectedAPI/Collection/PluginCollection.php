<?php

/**
 * @see crumbs_InjectedAPI_hookCrumbsPlugins
 */
class crumbs_InjectedAPI_Collection_PluginCollection {

  /**
   * @var crumbs_PluginInterface[]
   */
  private $plugins = array();

  /**
   * @var string[][]
   *   Format: $['findParent'][$pluginKey] = $method
   */
  private $routelessPluginMethods = array();

  /**
   * @var string[][]
   *   Format: $[$pluginKey]['findParent'] = $method
   */
  private $pluginRoutelessMethods = array();

  /**
   * @var string[][][]
   *   Format: $['findParent'][$route][$pluginKey] = $method
   */
  private $routePluginMethods = array();

  /**
   * @var string[][][]
   *   Format: $[$pluginKey]['findParent'][$route] = $method
   */
  private $pluginRouteMethods = array();

  /**
   * @return array
   * @throws Exception
   */
  function getPlugins() {
    return $this->plugins;
  }

  /**
   * @return string[][]
   *   Format: $['findParent'][$plugin_key] = $method
   */
  function getRoutelessPluginMethods() {
    return $this->routelessPluginMethods;
  }

  /**
   * @return string[][][]
   *   Format: $['findParent'][$route][$plugin_key] = $method.
   */
  function getRoutePluginMethods() {
    $routePluginMethods = $this->routePluginMethods;
    foreach ($routePluginMethods as $base_method => &$route_plugin_methods) {
      if (isset($this->routelessPluginMethods[$base_method])) {
        foreach ($route_plugin_methods as $route => &$methods_by_plugin_key) {
          $methods_by_plugin_key += $this->routelessPluginMethods[$base_method];
        }
      }
    }
    return $routePluginMethods;
  }

  /**
   * @return string[][]
   *   Format: $[$pluginKey]['findParent'] = $method
   */
  function getPluginRoutelessMethods() {
    return $this->pluginRoutelessMethods;
  }

  /**
   * @return string[][][]
   *   Format: $[$pluginKey]['findParent'][$route] = $method
   */
  function getPluginRouteMethods() {
    return $this->pluginRouteMethods;
  }

  /**
   * @param crumbs_PluginInterface $plugin
   * @param string $plugin_key
   * @param string|null $route
   *
   * @throws Exception
   */
  function addPlugin(crumbs_PluginInterface $plugin, $plugin_key, $route = NULL) {
    if (isset($this->plugins[$plugin_key])) {
      throw new Exception("There already is a plugin with key '$plugin_key'.");
    }
    $this->plugins[$plugin_key] = $plugin;
    if (isset($route)) {
      $this->analyzeRoutePluginMethods($route, $plugin_key, $plugin);
    }
    else {
      $this->analyzePluginMethods($plugin_key, $plugin);
    }
  }

  /**
   * @param string $plugin_key
   * @param crumbs_PluginInterface $plugin
   */
  private function analyzePluginMethods($plugin_key, crumbs_PluginInterface $plugin) {
    $reflectionObject = new ReflectionObject($plugin);
    foreach ($reflectionObject->getMethods() as $method) {
      if ('decorateBreadcrumb' === $method->name) {
        $this->routelessPluginMethods['decorateBreadcrumb'][$plugin_key] = $method->name;
        $this->pluginRoutelessMethods[$plugin_key]['decorateBreadcrumb'] = $method->name;
        continue;
      }
      $this->analyzePluginMethod($plugin_key, $method);
    }
  }

  /**
   * @param string $plugin_key
   * @param ReflectionMethod $method
   */
  private function analyzePluginMethod($plugin_key, ReflectionMethod $method) {
    foreach (array('findTitle', 'findParent') as $base_method_name) {
      if ($base_method_name === $method->name) {
        $this->routelessPluginMethods[$base_method_name][$plugin_key] = $base_method_name;
        $this->pluginRoutelessMethods[$plugin_key][$base_method_name] = $base_method_name;
        return;
      }
      elseif (0 === strpos($method->name, $base_method_name . '__')) {
        // This method is only for a specific route.
        $method_suffix = substr($method->name, strlen($base_method_name . '__'));
        $route = crumbs_Util::routeFromMethodSuffix($method_suffix);
        $this->routePluginMethods[$base_method_name][$route][$plugin_key] = $method->name;
        $this->pluginRouteMethods[$plugin_key][$base_method_name][$route] = $method->name;
        return;
      }
    }
  }

  /**
   * @param string $route
   * @param string $plugin_key
   * @param crumbs_PluginInterface $plugin
   */
  private function analyzeRoutePluginMethods($route, $plugin_key, crumbs_PluginInterface $plugin) {

    $method_suffix = crumbs_Util::buildMethodSuffix($route);

    foreach (array('findTitle', 'findParent') as $base_method_name) {
      if (!empty($method_suffix)) {
        $method_with_suffix = $base_method_name . '__' . $method_suffix;
        if (method_exists($plugin, $method_with_suffix)) {
          $this->routePluginMethods[$base_method_name][$route][$plugin_key] = $method_with_suffix;
          $this->pluginRouteMethods[$plugin_key][$base_method_name][$route] = $method_with_suffix;
          continue;
        }
      }
      if (method_exists($plugin, $base_method_name)) {
        $this->routePluginMethods[$base_method_name][$route][$plugin_key] = $base_method_name;
        $this->pluginRouteMethods[$plugin_key][$base_method_name][$route] = $base_method_name;
      }
    }
  }

} 
