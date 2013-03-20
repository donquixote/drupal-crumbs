<?php


class crumbs_PluginLibrary {

  protected $plugins;
  protected $disabledKeys;

  function getAvailablePlugins() {
    $this->lazyInit();
    return $this->plugins;
  }

  function getDisabledByDefaultKeys() {
    $this->lazyInit();
    return $this->plugins;
  }

  protected function lazyInit() {
    if (!isset($this->plugins)) {
      $this->discover();
    }
  }

  function load() {
    // Not implemented yet.
    $this->discover();
  }

  function save() {
    // Not implemented yet.
  }

  /**
   * Discover all plugins via hook_crumbs_plugins()
   */
  function discover() {

    $modules = array(
      'blog',
      'comment',
      'crumbs',
      'entityreference',
      'menu',
      'path',
      'taxonomy',
      'forum',
    );

    // Include Crumbs-provided plugins.
    foreach ($modules as $module) {
      if (module_exists($module)) {
        module_load_include('inc', 'crumbs', 'plugins/crumbs.'. $module);
      }
    }

    // Organic groups is a special case,
    // because 7.x-2.x behaves different from 7.x-1.x.
    if (module_exists('og')) {
      if (function_exists('og_get_group')) {
        // We are using the og-7.x-1.x branch.
        module_load_include('inc', 'crumbs', 'plugins/crumbs.og');
      }
      else {
        // We are using the og-7.x-2.x branch.
        module_load_include('inc', 'crumbs', 'plugins/crumbs.og.2');
      }
    }

    $this->plugins = array();
    $this->disabledKeys = array();
    $api = new crumbs_InjectedAPI_hookCrumbsPlugins($this->plugins, $this->disabledKeys);
    foreach (module_implements('crumbs_plugins') as $module) {
      $function = $module .'_crumbs_plugins';
      $api->setModule($module);
      $function($api);
    }
  }
}
