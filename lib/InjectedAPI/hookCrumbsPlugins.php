<?php


/**
 * API object to be used as an argument for hook_crumbs_plugins()
 * This is a sandbox class, currently not used..
 */
class crumbs_InjectedAPI_hookCrumbsPlugins {

  protected $module;
  protected $plugins = array();
  protected $pluginRoutes = array();
  protected $defaultValues = array();
  protected $entityRoutes = array();
  protected $entityParentPlugins = array();
  protected $discoveryOngoing;

  /**
   * @param bool $discovery_ongoing
   *   Switch to prevent some methods from being called from hook
   *   implementations.
   */
  function __construct(&$discovery_ongoing) {
    $this->discoveryOngoing = &$discovery_ongoing;
  }

  /**
   * @return array
   * @throws Exception
   */
  function getPlugins() {
    if ($this->discoveryOngoing) {
      throw new Exception("getPlugins() cannot be called from an implementation of hook_crumbs_plugins().");
    }
    return $this->plugins;
  }

  /**
   * @return array
   * @throws Exception
   */
  function getPluginRoutes() {
    if ($this->discoveryOngoing) {
      throw new Exception("getPluginRoutes() cannot be called from an implementation of hook_crumbs_plugins().");
    }
    return $this->pluginRoutes;
  }

  /**
   * @return array
   * @throws Exception
   */
  function getDefaultValues() {
    if ($this->discoveryOngoing) {
      throw new Exception("getDefaultValues() cannot be called from an implementation of hook_crumbs_plugins().");
    }
    return $this->defaultValues;
  }

  /**
   * @throws Exception
   */
  function finalize() {
    if ($this->discoveryOngoing) {
      throw new Exception("finalize() cannot be called from an implementation of hook_crumbs_plugins().");
    }

    $build = array();
    foreach ($this->entityParentPlugins as $key => $y) {
      list($entity_plugin, $types) = $y;
      if (!isset($types)) {
        foreach ($this->entityRoutes as $route => $x) {
          list($entity_type) = $x;
          $build[$entity_type][$key . '.' . $entity_type] = $entity_plugin;
        }
      }
      elseif (is_array($types)) {
        foreach ($types as $entity_type) {
          $build[$entity_type][$key . '.' . $entity_type] = $entity_plugin;
        }
      }
      elseif (is_string($types)) {
        $entity_type = $types;
        $build[$entity_type][$key] = $entity_plugin;
      }
    }

    foreach ($this->entityRoutes as $route => $x) {
      list($entity_type, $class, $bundle_key, $bundle_name) = $x;
      if (!empty($build[$entity_type])) {
        if (empty($class)) {
          foreach ($build[$entity_type] as $key => $entity_plugin) {
            $this->plugins[$key] = new crumbs_MultiPlugin_EntityParent($entity_plugin, $entity_type, $bundle_key, $bundle_name);
            $this->pluginRoutes[$key] = $route;
          }
        }
        else {
          foreach ($build[$entity_type] as $key => $entity_plugin) {
            $this->plugins[$key] = new $class($entity_plugin);
            $this->pluginRoutes[$key] = $route;
          }
        }
      }
    }
  }

  /**
   * This is typically called before each invocation of hook_crumbs_plugins(),
   * to let the object know about the module implementing the hook.
   * Modules can call this directly if they want to let other modules talk to
   * the API object.
   *
   * @param string $module
   *   The module name.
   */
  function setModule($module) {
    $this->module = $module;
  }

  /**
   * Register an entity route.
   * This should be called by those modules that define entity types and routes.
   *
   * @param string $entity_type
   * @param string $route
   * @param string $bundle_key
   * @param string $bundle_name
   */
  function entityRoute($entity_type, $route, $bundle_key, $bundle_name) {
    $this->entityRoutes[$route] = array($entity_type, NULL, $bundle_key, $bundle_name);
  }

  /**
   * @param $entity_type
   * @param $route
   * @param $class
   * @throws Exception
   */
  function entityRouteSpecial($entity_type, $route, $class) {
    if (!class_exists($class)) {
      throw new Exception("Class '$class' does not exist.");
    }
    $this->entityRoutes[$route] = array($entity_type, $class, NULL, NULL);
  }

  /**
   * Register an entity parent plugin.
   *
   * @param string $key
   * @param string|crumbs_EntityParentPlugin $entity_plugin
   * @param array $types
   */
  function entityParentPlugin($key, $entity_plugin = NULL, $types = NULL) {

    if (!isset($entity_plugin)) {
      $class = $this->module . '_CrumbsEntityParentPlugin';
      $entity_plugin = new $class();
    }
    elseif (is_string($entity_plugin)) {
      $class = $this->module . '_CrumbsEntityParentPlugin_' . $entity_plugin;
      $entity_plugin = new $class();
    }
    if ($entity_plugin instanceof crumbs_EntityParentPlugin) {
      $this->entityParentPlugins[$this->module . '.' . $key] = array($entity_plugin, $types);
    }
  }

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * @param string $key
   *   Rule key, relative to module name.
   * @param Crumbs_MonoPlugin $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   * @throws Exception
   */
  function monoPlugin($key = NULL, crumbs_MonoPlugin $plugin = NULL) {
    if (!isset($key)) {
      $class = $this->module . '_CrumbsMonoPlugin';
      $plugin = new $class();
      $key = $this->module;
    }
    elseif (!isset($plugin)) {
      $class = $this->module . '_CrumbsMonoPlugin_' . $key;
      $plugin = new $class();
      $key = $this->module . '.' . $key;
    }
    else {
      $class = get_class($plugin);
      $key = $this->module . '.' . $key;
    }
    if (!($plugin instanceof crumbs_MonoPlugin)) {
      throw new Exception("$class must implement class_MonoPlugin.");
    }
    if (isset($this->plugins[$key])) {
      throw new Exception("There already is a plugin with key '$key'.");
    }
    $this->plugins[$key] = $plugin;
  }

  /**
   * @param string $route
   * @param string $key
   * @param crumbs_MonoPlugin $plugin
   */
  function routeMonoPlugin($route, $key = NULL, crumbs_MonoPlugin $plugin = NULL) {
    $this->monoPlugin($key, $plugin);
    $this->pluginRoutes[$key] = $route;
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param $key
   *   Rule key, relative to module name.
   * @param crumbs_MultiPlugin $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   * @throws Exception
   */
  function multiPlugin($key, crumbs_MultiPlugin $plugin = NULL) {
    if (!isset($key)) {
      $class = $this->module . '_CrumbsMultiPlugin';
      $plugin = new $class();
      $plugin_key = $this->module;
    }
    elseif (!isset($plugin)) {
      $class = $this->module . '_CrumbsMultiPlugin_' . $key;
      $plugin = new $class();
      $plugin_key = $this->module . '.' . $key;
    }
    else {
      $class = get_class($plugin);
      $plugin_key = $this->module . '.' . $key;
    }
    if (!($plugin instanceof crumbs_MultiPlugin)) {
      throw new Exception("$class must implement class_MultiPlugin.");
    }
    $this->plugins[$plugin_key] = $plugin;
  }

  /**
   * @param string $route
   * @param string $key
   * @param crumbs_MultiPlugin $plugin
   */
  function routeMultiPlugin($route, $key = NULL, crumbs_MultiPlugin $plugin = NULL) {
    $this->multiPlugin($key, $plugin);
    $this->pluginRoutes[$key] = $route;
  }

  /**
   * Set specific rules as disabled by default.
   *
   * @param array|string $keys
   *   Array of keys, relative to the module name, OR
   *   a single string key, relative to the module name.
   */
  function disabledByDefault($keys = NULL) {
    if (is_array($keys)) {
      foreach ($keys as $key) {
        $this->_disabledByDefault($key);
      }
    }
    else {
      $this->_disabledByDefault($keys);
    }
  }

  protected function _disabledByDefault($key) {
    $key = isset($key) ? ($this->module . '.' . $key) : $this->module;
    $this->defaultValues[$key] = FALSE;
  }
}
