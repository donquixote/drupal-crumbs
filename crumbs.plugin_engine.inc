<?php
// $Id$


function _crumbs_load_plugin_engine() {
  $plugins = crumbs_get_plugins();
  $weights = _crumbs_load_weights();
  return new _crumbs_PluginEngine($plugins, $weights);
}


function crumbs_get_plugins() {
  static $_plugins;
  if (!isset($_plugins)) {
    $_plugins = _crumbs_load_plugins();
    $key_weights = _crumbs_load_weights();
    _crumbs_sort_plugins($_plugins, $key_weights);
  }
  return $_plugins;
}


function _crumbs_load_plugins() {
  foreach (array(
    'crumbs', 'menu', 'forum', 'views_ui', 'og', 'clickpath',
    'taxonomy', 'path', 'pathauto', 'nodereference'
  ) as $module) {
    if (module_exists($module)) {
      module_load_include('inc', 'crumbs', 'plugins/crumbs.'. $module);
    }
  }
  $plugins = array();
  foreach (module_implements('crumbs_plugins') as $module) {
    $function = $module .'_crumbs_plugins';
    $module_plugins = $function();
    if (is_array($module_plugins)) {
      foreach ($module_plugins as $key => $plugin) {
        $plugins[$module .'.'. $key] = $plugin;
      }
    }
    else {
      $plugins[$module] = $module_plugins;
    }
  }
  return $plugins;
}


function _crumbs_load_weights() {
  $weights = variable_get('crumbs_weights', NULL);
  if (!is_array($weights)) {
    // load and convert settings from the 1.x branch.
    $weights = _crumbs_load_weights_1_x();
  }
  $weights = is_array($weights) ? $weights : array();
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


/**
 * Sort plugins by the order they need to be invoked,
 * and remove those that don't need to be invoked at all.
 */
function _crumbs_sort_plugins(array &$plugins, array $key_weights) {
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


class _crumbs_PluginEngine {

  protected $_plugins;
  protected $_weights;

  function __construct(array $plugins, array $weights) {
    $this->_plugins = $plugins;
    $this->_weights = $weights;
  }

  /**
   * Invoke a method hook for all plugins.
   *
   * @param $invokeAction
   *   an object that does the method call, and can maintain a state between
   *   different plugins' method calls.
   */
  function invokeAll($invokeAction) {
    $invokeAction->setWeights($this->_weights);
    foreach ($this->_plugins as $plugin_key => $plugin) {
      $done = $invokeAction->invoke($plugin, $plugin_key);
      if ($done) {
        return $done;
      }
    }
  }

  /**
   * invokeAll for alter hooks.
   * These need to be called with the lowest priority first,
   * because later calls will overwrite earlier calls.
   */
  function invokeAllReverse($invokeAction) {
    foreach (array_reverse($this->_plugins) as $plugin_key => $plugin) {
      $invokeAction->invoke($plugin, $plugin_key);
    }
  }
}


abstract class crumbs_InvokeAction_findSomething {

  // injected parameters
  protected $_path;
  protected $_item;
  protected $_weights = array();

  protected $_method;  // to be set by the child class
  protected $_methods = array();

  protected $_value;
  protected $_weight;

  function __construct($path, $item) {
    $this->_path = $path;
    $this->_item = $item;
    $method_suffix = preg_replace('/[^a-z0-9]/', '_', strtolower($item['path']));
    $this->_methods = array($this->_method .'__'. $method_suffix, $this->_method);
  }

  /**
   * This should be called by the PluginEngine, at the start of invokeAll().
   */
  function setWeights($weights) {
    $this->_weights = $weights;
  }

  /**
   * This should run once for each plugin object.
   */
  function invoke($plugin, $plugin_key) {
    foreach ($this->_methods as $method) {
      if (method_exists($plugin, $method)) {
        $result = $this->_invoke($plugin, $method);
        break;
      }
    }
    if (is_array($result)) {
      foreach ($result as $key => $value) {
        $this->_setValue($plugin_key .'.'. $key, $value);
      }
    }
    else if (isset($result)) {
      $this->_setValue($plugin_key, $result);
    }
  }

  /**
   * This runs at the end of the InvokeAction's life cycle,
   * and returns the value that was determined.
   */
  function getValue() {
    return $this->_value;
  }

  protected function _setValue($key, $value) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    while (TRUE) {
      if (empty($fragments)) break;
      if (isset($this->_weights[$partial_key .'.*'])) {
        $weight = $this->_weights[$partial_key .'.*'];
      }
      $partial_key .= '.'. array_shift($fragments);
    }
    if (isset($this->_weights[$key])) {
      $weight = $this->_weights[$key];
    }
    if ($weight !== FALSE) {
      if (!isset($this->_weight) || $weight < $this->_weight) {
        $this->_weight = $weight;
        $this->_value = $value;
      }
    }
  }

  protected function _getMethods($method) {
    return array($method, $method .'__'. $this->_method_suffix);
  }

  abstract protected function _invoke($plugin, $method);
}




