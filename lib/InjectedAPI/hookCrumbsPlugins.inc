<?php


/**
 * API object to be used as an argument for hook_crumbs_plugins()
 * This is a sandbox class, currently not used..
 */
class crumbs_InjectedAPI_hookCrumbsPlugins {

  protected $module;
  protected $plugins;

  function __construct(&$plugins) {
    $this->plugins =& $plugins;
  }

  function setModule($module) {
    $this->module = $module;
  }

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
    if (!is_a($plugin, 'crumbs_MonoPlugin')) {
      throw new Exception("$class must implement class_MonoPlugin.");
    }
    $this->plugins[$key] = $plugin;
  }

  function multiPlugin($key, $plugin = NULL) {
    if (!isset($key)) {
      $class = $this->module . '_CrumbsMultiPlugin';
      $plugin = new $class();
      $key = $this->module;
    }
    elseif (!isset($plugin)) {
      $class = $this->module . '_CrumbsMultiPlugin_' . $key;
      $plugin = new $class();
      $key = $this->module . '.' . $key;
    }
    else {
      $class = get_class($plugin);
      $key = $this->module . '.' . $key;
    }
    if (!is_a($plugin, 'crumbs_MultiPlugin')) {
      throw new Exception("$class must implement class_MultiPlugin.");
    }
    $this->plugins[$key] = $plugin;
  }
}
