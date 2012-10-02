<?php


function _crumbs_load_plugin_engine() {
  list($plugins, $disabled_keys) = crumbs_get_plugins();
  $weights = _crumbs_load_weights();
  foreach ($disabled_keys as $key => $disabled) {
    if (!isset($weights[$key])) {
      $weights[$key] = FALSE;
    }
  }
  return new crumbs_PluginEngine($plugins, $weights);
}


function crumbs_get_plugins() {
  static $_plugins;
  static $_disabled_keys;
  if (!isset($_plugins)) {
    list($_plugins, $_disabled_keys) = _crumbs_load_plugins();
  }
  return array($_plugins, $_disabled_keys);
}


function _crumbs_load_plugins() {
  $modules = array(
    'blog',
    'comment',
    'crumbs',
    'entityreference',
    'menu',
    'og',
    'path',
    'taxonomy',
    // Unported: 'forum'
  );

  foreach ($modules as $module) {
    if (module_exists($module)) {
      module_load_include('inc', 'crumbs', 'plugins/crumbs.'. $module);
    }
  }
  $plugins = array();
  $disabled_keys = array();
  $api = new crumbs_InjectedAPI_hookCrumbsPlugins($plugins, $disabled_keys);
  foreach (module_implements('crumbs_plugins') as $module) {
    $function = $module .'_crumbs_plugins';
    $api->setModule($module);
    $function($api);
  }
  return array($plugins, $disabled_keys);
}


function _crumbs_load_weights() {
  $weights = variable_get('crumbs_weights', array('crumbs.home_title' => 0));
  asort($weights);
  if (!isset($weights['*'])) {
    $weights['*'] = count($weights);
  }
  return $weights;
}
