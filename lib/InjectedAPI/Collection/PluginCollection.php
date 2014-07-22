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
    if (isset($route)) {
      $legacyMethods = $this->analyzeRoutePluginMethods($route, $plugin_key, $plugin);
    }
    else {
      $legacyMethods = $this->analyzePluginMethods($plugin_key, $plugin);
    }

    if (!empty($legacyMethods)) {
      $legacyMethods += array(
        'findParent' => array(),
        'findTitle' => array(),
      );
      if ($plugin instanceof crumbs_MultiPlugin) {
        $plugin = new crumbs_MultiPlugin_LegacyWrapper(
          $plugin,
          $legacyMethods['findParent'],
          $legacyMethods['findTitle']);
      }
      elseif ($plugin instanceof crumbs_MonoPlugin) {
        $plugin = new crumbs_MonoPlugin_LegacyWrapper(
          $plugin,
          $legacyMethods['findParent'],
          $legacyMethods['findTitle']);
      }
    }

    $this->plugins[$plugin_key] = $plugin;
  }

  /**
   * @param string $plugin_key
   * @param crumbs_PluginInterface $plugin
   *
   * @return string[][]
   *   Format: $['findParent']['node/%'] = 'findParent__node_x'
   *   Any legacy methods.
   */
  private function analyzePluginMethods($plugin_key, crumbs_PluginInterface $plugin) {
    $reflectionObject = new ReflectionObject($plugin);
    $legacyMethods = array();
    foreach ($reflectionObject->getMethods() as $method) {
      switch ($method->name) {

        case 'decorateBreadcrumb':
          $this->routelessPluginMethods['decorateBreadcrumb'][$plugin_key] = 'decorateBreadcrumb';
          $this->pluginRoutelessMethods[$plugin_key]['decorateBreadcrumb'] = 'decorateBreadcrumb';
          break;

        case 'findParent':
        case 'findTitle':
          $this->routelessPluginMethods[$method->name][$plugin_key] = $method->name;
          $this->pluginRoutelessMethods[$plugin_key][$method->name] = $method->name;
          break;

        default:
          if (0 === strpos($method->name, 'findParent__')) {
            $baseMethodName = 'findParent';
            $methodSuffix = substr($method->name, 12);
          }
          elseif (0 === strpos($method->name, 'findTitle__')) {
            $baseMethodName = 'findTitle';
            $methodSuffix = substr($method->name, 12);
          }
          else {
            break;
          }
          $route = crumbs_Util::routeFromMethodSuffix($methodSuffix);
          $this->routePluginMethods[$baseMethodName][$route][$plugin_key] = $baseMethodName;
          $this->pluginRouteMethods[$plugin_key][$baseMethodName][$route] = $baseMethodName;
          $legacyMethods[$baseMethodName][$route] = $method->name;
      }
    }

    return $legacyMethods;
  }

  /**
   * @param string $route
   * @param string $plugin_key
   * @param crumbs_PluginInterface $plugin
   *
   * @return string[][]
   *   Format: $['findParent']['node/%'] = 'findParent__node_x'
   *   Any legacy methods.
   */
  private function analyzeRoutePluginMethods($route, $plugin_key, crumbs_PluginInterface $plugin) {

    $method_suffix = crumbs_Util::buildMethodSuffix($route);
    $legacyMethods = array();

    foreach (array('findTitle', 'findParent') as $base_method_name) {
      if (!empty($method_suffix)) {
        $method_with_suffix = $base_method_name . '__' . $method_suffix;
        if (method_exists($plugin, $method_with_suffix)) {
          $this->routePluginMethods[$base_method_name][$route][$plugin_key] = $base_method_name;
          $this->pluginRouteMethods[$plugin_key][$base_method_name][$route] = $base_method_name;
          $legacyMethods[$base_method_name][$route] = $method_with_suffix;
          continue;
        }
      }
      if (method_exists($plugin, $base_method_name)) {
        $this->routePluginMethods[$base_method_name][$route][$plugin_key] = $base_method_name;
        $this->pluginRouteMethods[$plugin_key][$base_method_name][$route] = $base_method_name;
      }
    }

    return $legacyMethods;
  }

} 
