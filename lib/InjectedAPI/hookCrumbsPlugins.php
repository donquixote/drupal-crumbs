<?php


/**
 * API object to be used as an argument for hook_crumbs_plugins()
 * This is a sandbox class, currently not used..
 */
class crumbs_InjectedAPI_hookCrumbsPlugins {

  protected $module;
  protected $plugins;
  protected $disabledKeys;
  protected $entityRoutes = array();
  protected $entityParentPlugins = array();

  function __construct(&$plugins, &$disabled_keys) {
    $this->plugins =& $plugins;
    $this->disabledKeys =& $disabled_keys;
  }

  function finalize() {
    $build = array();
    foreach ($this->entityParentPlugins as $key => $y) {
      list($entity_plugin, $types) = $y;
      if (!isset($types)) {
        foreach ($this->entityRoutes as $entity_type => $x) {
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
    foreach ($this->entityRoutes as $entity_type => $x) {
      list($route, $class, $bundle_key) = $x;
      if (!empty($build[$entity_type])) {
        if (empty($class)) {
          foreach ($build[$entity_type] as $key => $entity_plugin) {
            $this->plugins[$key] = new crumbs_MultiPlugin_EntityParent($entity_type, $route, $bundle_key, $entity_plugin);
          }
        }
        else {
          foreach ($build[$entity_type] as $key => $entity_plugin) {
            $this->plugins[$key] = new $class($entity_plugin);
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
   * @param $module
   *   The module name.
   */
  function setModule($module) {
    $this->module = $module;
  }

  function entityRoute($entity_type, $route, $class_suffix, $bundle_key) {
    $class = $this->module . '_CrumbsMultiPlugin_' . $class_suffix;
    if (!class_exists($class)) {
      $class = NULL;
    }
    $this->entityRoutes[$entity_type] = array($route, $class, $bundle_key);
  }

  function entityParentPlugin($key, $entity_plugin, $types = NULL) {
    $this->entityParentPlugins[$this->module . '.' . $key] = array($entity_plugin, $types);
  }

  protected function buildEntityParentPlugin($entity_plugin, $entity_type) {
    switch ($entity_type) {
      case 'node':
        return new crumbs_MultiPlugin_NodeParent($entity_plugin);
      case 'user':
        return new crumbs_MultiPlugin_UserParent($entity_plugin);
      case 'taxonomy_term':
        return new crumbs_MultiPlugin_TaxonomyTermParent($entity_plugin);
    }
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param $key
   *   Rule key, relative to module name.
   * @param $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   */
  function monoPlugin($key = NULL, $plugin = NULL) {
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
    $this->plugins[$key] = $plugin;
    if (method_exists($plugin, 'disabledByDefault')) {
      $disabled_by_default = $plugin->disabledByDefault();
      if ($disabled_by_default === TRUE) {
        $this->disabledKeys[$key] = $key;
      }
      elseif ($disabled_by_default !== FALSE && $disabled_by_default !== NULL) {
        throw new Exception("$class::disabledByDefault() must return TRUE, FALSE or NULL.");
      }
    }
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param $key
   *   Rule key, relative to module name.
   * @param $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   */
  function multiPlugin($key, $plugin = NULL) {
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
    if (method_exists($plugin, 'disabledByDefault')) {
      $disabled_by_default = $plugin->disabledByDefault();
      if ($disabled_by_default !== NULL) {
        if (!is_array($disabled_by_default)) {
          throw new Exception("$class::disabledByDefault() must return an array or NULL.");
        }
        foreach ($disabled_by_default as $suffix) {
          if (!isset($suffix) || $suffix === '') {
            throw new Exception("$class::disabledByDefault() - returned array contains an empty key.");
          }
          else {
            $key = $plugin_key . '.' . $suffix;
            $disabled_keys[$key] = $key;
          }
        }
      }
    }
  }

  /**
   * Set specific rules as disabled by default.
   *
   * @param $keys
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
    $this->disabledKeys[$key] = $key;
  }
}
