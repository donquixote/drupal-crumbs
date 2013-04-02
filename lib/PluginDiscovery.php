<?php


class crumbs_PluginDiscovery {

  protected $plugins;
  protected $disabledKeys;

  function getAvailablePlugins() {
    $this->lazyInit();
    return $this->plugins;
  }

  function getDisabledByDefaultKeys() {
    $this->lazyInit();
    // TODO: This is wrong isn't it?
    return $this->disabledKeys;
  }

  protected function lazyInit() {
    if (!isset($this->plugins)) {
      $this->includePluginFiles();
      $this->load();
      if (!isset($this->plugins)) {
        $this->discover();
      }
    }
  }

  protected function load() {
    $cache = cache_get('crumbs:plugin_info');
    if ($cache && isset($cache->data['plugins']) && isset($cache->data['disabled_keys'])) {
      $this->plugins = $cache->data['plugins'];
      $this->disabledKeys = $cache->data['disabled_keys'];
    }
  }

  protected function save() {
    cache_set('crumbs:plugin_info', array(
      'plugins' => $this->plugins,
      'disabled_keys' => $this->disabledKeys,
    ));
  }

  /**
   * Discover all plugins via hook_crumbs_plugins()
   */
  protected function discover() {

    $this->plugins = array();
    $this->disabledKeys = array();
    $api = new crumbs_InjectedAPI_hookCrumbsPlugins($this->plugins, $this->disabledKeys);
    foreach (module_implements('crumbs_plugins') as $module) {
      $function = $module .'_crumbs_plugins';
      $api->setModule($module);
      $function($api);
    }
    $api->finalize();
    $this->save();
  }

  protected function includePluginFiles() {

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
  }
}
