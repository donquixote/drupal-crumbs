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


function _crumbs_get_disabled_keys(array $plugins) {
  $disabled_keys = array();
  foreach ($plugins as $plugin_key => $plugin) {
    if (method_exists($plugin, 'disabledByDefault')) {
      foreach ($plugin->disabledByDefault() as $suffix) {
        if (!isset($suffix) || $suffix === '') {
          $disabled_keys[$plugin_key] = TRUE;
        }
        else {
          $disabled_keys[$plugin_key .'.'. $suffix] = TRUE;
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


class crumbs_PluginEngine {

  protected $_plugins;
  protected $_weightKeeper;

  protected $_pluginOrder_find = array();
  protected $_pluginOrder_alter = array();

  function __construct(array $plugins, array $weights) {
    $this->_plugins = $plugins;
    foreach ($plugins as $plugin_key => $plugin) {
      if (!method_exists($plugin, 'defineOne')) {
        $weights[$plugin_key] = FALSE;
      }
      if (!method_exists($plugin, 'defineMany') && !method_exists($plugin, 'define')) {
        $weights[$plugin_key . '.*'] = FALSE;
      }
    }
    $this->_weightKeeper = new crumbs_RuleWeightKeeper($weights);

    foreach ($plugins as $plugin_key => $plugin) {
      $keeper = $this->_weightKeeper->prefixedWeightKeeper($plugin_key);
      $w_find = $keeper->getSmallestWeight();
      if ($w_find !== FALSE) {
        $this->_pluginOrder_find[$plugin_key] = $w_find;
      }
      $w_alter = $keeper->findWeight();
      if ($w_alter !== FALSE) {
        $this->_pluginOrder_alter[$plugin_key] = $w_alter;
      }
    }
    // lowest weight first = highest priority first
    asort($this->_pluginOrder_find);
    // lowest weight last = highest priority last
    arsort($this->_pluginOrder_alter);

    foreach ($this->_pluginOrder_find as $plugin_key => $weight) {
      $this->_pluginOrder_find[$plugin_key] = $plugins[$plugin_key];
    }
    foreach ($this->_pluginOrder_alter as $plugin_key => $weight) {
      $this->_pluginOrder_alter[$plugin_key] = $plugins[$plugin_key];
    }
  }

  /**
   * Invoke the invoke action for all plugins, starting with the plugin with
   * highest priority. The function will stop when it has 
   *
   * @param $invokeAction
   *   an object that does the method call, and can maintain a state between
   *   different plugins' method calls.
   */
  function invokeAll_find(crumbs_InvokeActionInterface_find $invoke_action) {
    foreach ($this->_pluginOrder_find as $plugin_key => $plugin) {
      $weight_keeper = $this->_weightKeeper->prefixedWeightKeeper($plugin_key);
      $found = $invoke_action->invoke($plugin, $plugin_key, $weight_keeper);
      if ($found) {
        return $found;
      }
    }
  }

  /**
   * invokeAll for alter hooks.
   * These need to be called with the lowest priority first,
   * because later calls will overwrite earlier calls.
   */
  function invokeAll_alter(crumbs_InvokeActionInterface_alter $invokeAction) {
    foreach ($this->_pluginOrder_alter as $plugin_key => $plugin) {
      $invokeAction->invoke($plugin, $plugin_key);
    }
  }
}


class crumbs_RuleWeightKeeper {
  protected $_rule_weights;
  protected $_prefixedKeepers = array();
  protected $_prefixSorted = array();
  protected $_soloSorted = array();

  function __construct(array $rule_weights) {
    asort($rule_weights);
    $this->_rule_weights = $rule_weights;
  }

  function prefixedWeightKeeper($prefix) {
    if (!isset($this->_prefixedKeepers[$prefix])) {
      $this->_prefixedKeepers[$prefix] = $this->_buildPrefixedWeightKeeper($prefix);
    }
    return $this->_prefixedKeepers[$prefix];
  }

  protected function _buildPrefixedWeightKeeper($prefix) {
    $weights = array();
    $k = strlen($prefix);
    $weights[''] = $weights['*'] = $this->_findWildcardWeight($prefix);
    if (isset($this->_rule_weights[$prefix])) {
      $weights[''] = $this->_rule_weights[$prefix];
    }
    if (isset($this->_rule_weights[$prefix .'.*'])) {
      $weights['*'] = $this->_rule_weights[$prefix .'.*'];
    }
    foreach ($this->_rule_weights as $key => $weight) {
      if (strlen($key) > $k && substr($key, 0, $k+1) === ($prefix .'.')) {
        $weights[substr($key, $k+1)] = $weight;
      }
    }
    return new crumbs_RuleWeightKeeper($weights);
  }

  function getSmallestWeight() {
    foreach ($this->_rule_weights as $weight) {
      if ($weight !== FALSE) {
        return $weight;
      }
    }
    return FALSE;
  }

  /**
   * Determine the weight for the rule specified by the key.
   */
  function findWeight($key = NULL) {
    if (!isset($key)) {
      return $this->_rule_weights[''];
    }
    if (isset($this->_rule_weights[$key])) {
      return $this->_rule_weights[$key];
    }
    return $this->_findWildcardWeight($key);
  }

  protected function _findWildcardWeight($key) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    $weight = $this->_rule_weights['*'];
    while (!empty($fragments)) {
      if (isset($this->_rule_weights[$partial_key .'.*'])) {
        $weight = $this->_rule_weights[$partial_key .'.*'];
      }
      $partial_key .= '.'. array_shift($fragments);
    }
    return $weight;
  }
}


abstract class crumbs_InvokeAction_findForPath implements crumbs_InvokeActionInterface_find {

  // injected constructor parameters
  protected $_path;
  protected $_item;

  protected $_method;  // child class should override this.
  protected $_methods = array();

  // weight, value and key of the candidate with highest priority
  protected $_candidate_key;
  protected $_candidate_value;
  protected $_candidate_weight;
  protected $_log;

  function __construct($path, $item) {
    $this->_path = $path;
    $this->_item = $item;
    // Replace all characters with something that is allowed in method names.
    // while avoiding false positives.
    // Example: 'findParent__node_x()' should only match 'node/%',
    // but not 'node-_' or 'node-x', or other exotic router paths.
    // Special character router paths can not be matched by any method name,
    // so you will need to use switch() or if/else on $item['path'].
    $method_suffix = crumbs_build_method_suffix($item['path']);
    if ($method_suffix !== FALSE) {
      $this->_methods[] = $this->_method .'__'. $method_suffix;
    }
    $this->_methods[] = $this->_method;
  }

  /**
   * This should run once for each plugin object.
   * It should be called by the PluginEngine, during invokeUntilFound().
   */
  function invoke($plugin, $plugin_key, $weight_keeper) {
    $smallest_weight = $weight_keeper->getSmallestWeight();
    if (isset($this->_candidate_weight) && $this->_candidate_weight <= $smallest_weight) {
      // any further candidate would have a higher weight, thus lower priority,
      // than what we already have found. Thus, we can stop searching.
      return TRUE;
    }
    foreach ($this->_methods as $method) {
      if (method_exists($plugin, $method)) {
        $result = $this->_invoke($plugin, $method);
        break;
      }
    }
    if (method_exists($plugin, 'defineMany') || method_exists($plugin, 'define')) {
      // we expect an array result.
      if (!empty($result) && is_array($result)) {
        foreach ($result as $key => $value) {
          $weight = $weight_keeper->findWeight($key);
          $this->_setValue($plugin_key .'.'. $key, $value, $weight);
        }
      }
      else {
        $this->_log[$plugin_key .'.*'] = array(NULL, NULL);
      }
    }
    else {
      // we expect a simple value as a result
      if (isset($result)) {
        $weight = $weight_keeper->findWeight();
        $this->_setValue($plugin_key, $result, $weight);
      }
      else {
        $this->_log[$plugin_key] = array(NULL, NULL);
      }
    }
  }

  /**
   * This runs at the end of the InvokeAction's life cycle,
   * and returns the value that was determined.
   */
  function getValue() {
    return $this->_candidate_value;
  }

  function getCandidateKey() {
    return $this->_candidate_key;
  }

  function getLoggedCandidates() {
    return $this->_log;
  }

  protected function _setValue($key, $value, $weight) {
    if ($weight !== FALSE) {
      if (!isset($this->_candidate_weight) || $weight < $this->_candidate_weight) {
        $this->_candidate_weight = $weight;
        $this->_candidate_value = $value;
        $this->_candidate_key = $key;
      }
    }
    $this->_log[$key] = array($value, $weight);
  }

  protected function _getMethods($method) {
    return array($method, $method .'__'. $this->_method_suffix);
  }

  abstract protected function _invoke($plugin, $method);
}




