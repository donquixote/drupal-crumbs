<?php


function _crumbs_load_plugin_engine() {
  $plugins = crumbs_get_plugins();
  $weights = _crumbs_load_weights();
  $disabled_keys = _crumbs_get_disabled_keys($plugins);
  foreach ($disabled_keys as $key => $disabled) {
    if (!isset($weights[$key])) {
      $weights[$key] = FALSE;
    }
  }
  return new crumbs_PluginEngine($plugins, $weights);
}


function crumbs_get_plugins() {
  static $_plugins;
  if (!isset($_plugins)) {
    $_plugins = _crumbs_load_plugins();
    // $key_weights = _crumbs_load_weights();
    // _crumbs_sort_plugins($_plugins, $key_weights);
  }
  return $_plugins;
}


function _crumbs_load_plugins() {
  foreach (array(
    'crumbs', 'menu', 'forum', 'views_ui', 'og', 'clickpath',
    'taxonomy', 'path', 'pathauto', 'nodereference', 'comment',
  ) as $module) {
    if (module_exists($module)) {
      module_load_include('inc', 'crumbs', 'plugins/crumbs.'. $module);
    }
  }
  $plugins = array();
  $api = new crumbs_InjectedAPI_hookCrumbsPlugins($plugins);
  foreach (module_implements('crumbs_plugins') as $module) {
    $function = $module .'_crumbs_plugins';
    $api->setModule($module);
    $function($api);
  }
  return $plugins;
}


function _crumbs_load_weights() {
  $weights = variable_get('crumbs_weights', array());
  asort($weights);
  if (!isset($weights['*'])) {
    $weights['*'] = count($weights);
  }
  return $weights;
}


function _crumbs_load_weights_1_x() {
  $weights_1_x = variable_get('crumbs', array());
  $weights_1_x = is_array($weights_1_x) ? $weights_1_x : array();
  $weights = array();
  $weight = 0;
  foreach ($weights_1_x as $key_1_x => $enabled) {
    // Replace ':' by '.'.
    $key = str_replace(':', '.', $key_1_x);
    if ($enabled) {
      $weights[$key] = $weight;
      ++ $weight;
    }
    else {
      $weights[$key] = FALSE;
    }
  }
  return $weights;
}


function _crumbs_get_disabled_keys(array $plugins) {
  $disabled_keys = array();
  foreach ($plugins as $plugin_key => $plugin) {
    if (method_exists($plugin, 'disabledByDefault')) {
      $result = $plugin->disabledByDefault();
      if (!isset($result)) {
        continue;
      }
      $class = get_class($plugin);
      if (is_a($plugin, 'crumbs_MultiPlugin')) {
        if (!is_array($result)) {
          throw new Exception("$class::disabledByDefault() must return an array or NULL.");
        }
        foreach ($result as $suffix) {
          if (!isset($suffix) || $suffix === '') {
            throw new Exception("$class::disabledByDefault() - returned array contains an empty key.");
          }
          else {
            $disabled_keys[$plugin_key . '.' . $suffix] = TRUE;
          }
        }
      }
      elseif (is_a($plugin, 'crumbs_MonoPlugin')) {
        if ($result === TRUE) {
          $disabled_keys[$plugin_key] = TRUE;
        }
        elseif ($result !== FALSE) {
          throw new Exception("$class::disabledByDefault() must return TRUE, FALSE or NULL.");
        }
      }
    }
  }
  return $disabled_keys;
}


/**
 * Sort plugins by the order they need to be invoked,
 * and remove those that don't need to be invoked at all.
 */
function _crumbs_sort_plugins___DISABLED(array &$plugins, array $key_weights) {
  $plugin_weights = array();
  foreach ($plugins as $plugin_key => $plugin) {
    $fragments = explode('.', $plugin_key);
    $partial_key = array_shift($fragments);
    $weight = 0;
    if (isset($key_weights['*'])) {
      $weight = $key_weights['*'];
    }
    while (TRUE) {
      if (isset($key_weights[$partial_key .'.*'])) {
        $weight = $key_weights[$partial_key .'.*'];
      }
      if (empty($fragments)) break;
      $partial_key .= '.'. array_shift($fragments);
    }
    if (isset($key_weights[$plugin_key])) {
      $weight = $key_weights[$plugin_key];
    }
    $plugin_weights[$plugin_key] = $weight;
  }
  foreach ($key_weights as $key => $weight) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    $plugin_key = NULL;
    while (TRUE) {
      if (isset($plugins[$partial_key])) {
        $plugin_key = $partial_key;
      }
      if (empty($fragments)) break;
      $partial_key .= '.'. array_shift($fragments);
    }
    if (isset($plugin_key)) {
      if ($plugin_weights[$plugin_key] === FALSE || $plugin_weights[$plugin_key] > $weight) {
        $plugin_weights[$plugin_key] = $weight;
      }
    }
  }
  // this works, because the keys are never numeric.
  // (each key contains a module name)
  array_multisort($plugin_weights, $plugins);
  foreach ($plugins as $plugin_key => $plugin) {
    if ($plugin_weights[$plugin_key] === FALSE) {
      unset($plugins[$plugin_key]);
    }
  }
}




