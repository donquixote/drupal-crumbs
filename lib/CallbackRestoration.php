<?php

class crumbs_CallbackRestoration {

  /**
   * @var array
   *   Callbacks by module and "key", where
   *   $module . '.' . $key === $plugin_key
   *   $this->callbacks[$module][$key] = $callback
   */
  protected $callbacks = array();

  /**
   * @var crumbs_InjectedAPI_hookCrumbsPlugins
   */
  protected $api;

  /**
   * @var bool
   */
  protected $discoveryOngoing = FALSE;

  /**
   * Constructor
   */
  function __construct() {
    $this->api = new crumbs_InjectedAPI_hookCrumbsPlugins($this->discoveryOngoing);
  }

  /**
   * @param string $module
   * @param string $key
   * @return callback
   */
  function getEntityParentCallback($module, $key) {
    if (!isset($this->callbacks[$module])) {
      $this->restoreModuleCallbacks($module);
    }
    return isset($this->callbacks[$module]['entityParent'][$key]) ? $this->callbacks[$module]['entityParent'][$key] : FALSE;
  }

  /**
   * @param string $module
   * @param string $key
   * @return callback
   */
  function getRouteParentCallback($module, $key) {
    if (!isset($this->callbacks[$module])) {
      $this->restoreModuleCallbacks($module);
    }
    return isset($this->callbacks[$module]['routeParent'][$key]) ? $this->callbacks[$module]['routeParent'][$key] : FALSE;
  }

  /**
   * @param string $module
   */
  protected function restoreModuleCallbacks($module) {
    $f = $module . '_crumbs_plugins';
    if (!function_exists($f)) {
      // The module may have been disabled in the meantime,
      // or the function has been removed by a developer.
      $this->callbacks[$module] = array();
      return;
    }
    $this->discoveryOngoing = TRUE;
    $this->api->setModule($module);
    $f($this->api);
    $this->discoveryOngoing = FALSE;
    $this->callbacks[$module] = $this->api->getModuleCallbacks($module);
  }
}