<?php


function crumbs_admin_form() {
  $form = array();
  
  // drupal_add_js(drupal_get_path('module', 'crumbs') .'/crumbs.admin.js');
  // drupal_add_css(drupal_get_path('module', 'crumbs') .'/crumbs.admin.css');
  
  $text = <<<EOT
    <p>Reorder and enable/disable crumbs rules.</p>
    <p>The title coming after the first space in each row can be ignored, it has no effect. What matters is the key before the first space.</p>
    <p>Hint: Copy+paste to edit this text to your favourite text editor, or to import, export and backup. Text editor beats tabledrag, don't you think?</p>
    <p>If a newly installed module introduces new crumbs rules, these rules will find themselves either in the "inherit" section or in the "disabled by default" section at first. Priority and enabled/disabled status of rules in the "inherit" section is inherited from matching wildcard rules in the "enabled" or "disabled" or "disabled by default" section. The '*' wildcard rule counts as enabled, if it is in the inherit section itself.</p>
EOT;
  $form['instructions'] = array(
    '#value' => t($text),
  );
  
  $form['settings'] = array(
    '#type' => 'textarea',
    '#title' => 'Order of rules.',
    '#description' => 'Each row is a rule',
    '#rows' => 24,
  );
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Save',
  );
  
  $form['settings']['#default_value'] = _crumbs_get_default_text();
  
  return $form;
}


function _crumbs_get_default_text() {

  module_load_include('inc', 'crumbs', 'crumbs.plugin_engine');

  $plugins = _crumbs_load_plugins();
  list($available_keys, $keys_by_plugin) = _crumbs_load_available_keys($plugins);
  $weights = _crumbs_load_weights();
  $disabled_keys = _crumbs_get_disabled_keys($plugins);

  return _crumbs_build_default_text($available_keys, $keys_by_plugin, $weights, $disabled_keys);
}


function _crumbs_build_default_text(array $available_keys, array $keys_by_plugin, array $weights, array $disabled_keys) {

  $key_lengths = array();
  foreach ($available_keys as $key => $title) {
    $key_lengths[] = strlen($key);
  }
  $ideal_length = _crumbs_admin_find_ideal_length($key_lengths);
  
  foreach ($available_keys as $key => $title) {
    $string = $key;
    if (is_string($title)) {
      if (strlen($string) < $ideal_length) {
        $string .= str_repeat(' ', $ideal_length - strlen($string));
      }
      $string .= ' - '. $title;
    }
    $available_keys[$key] = $string;
  }

  $lines = array(
    'inherit' => $available_keys,
    'disabled_by_default' => array(),
    'enabled' => array(),
    'disabled' => array(),
  );

  foreach ($weights as $key => $weight) {
    $section = ($weight === FALSE) ? 'disabled' : 'enabled';
    $string = $key;
    if (isset($available_keys[$key])) {
      $string = $available_keys[$key];
    }
    else if ($key !== '*') {
      // an orphan setting.
      if (strlen($string) < $ideal_length) {
        $string .= str_repeat(' ', $ideal_length - strlen($string));
      }
      $string .= '   (orphan rule)';
    }
    $lines[$section][$key] = $string;
    unset($lines['inherit'][$key]);
  }

  foreach ($disabled_keys as $key => $disabled) {
    if (isset($lines['inherit'][$key])) {
      $lines['disabled_by_default'][$key] = $lines['inherit'][$key];
      unset($lines['inherit'][$key]);
    }
  }

  foreach ($keys_by_plugin as $plugin_key => $keys_for_this_plugin) {
    $lines['inherit'][$plugin_key .':NEWLINE:'] = "";
  }

  ksort($lines['inherit']);
  foreach ($lines['inherit'] as $key => $line) {
    if (isset($prev) && $prev === '' && $line === '') {
      unset($lines['inherit'][$key]);
    }
    $prev = $line;
  }

  return "\n\n"
    . implode("\n", $lines['enabled'])
    . "\n\n\n---- disabled ----\n\n". implode("\n", $lines['disabled'])
    . "\n\n\n---- disabled by default ----\n\n". implode("\n", $lines['disabled_by_default'])
    . "\n\n\n---- inherit ----\n\n". implode("\n", $lines['inherit'])
    . "\n\n"
  ;
}


/**
 * This algorithm is copied 1:1 from blockadminlight
 */
function _crumbs_admin_find_ideal_length(array $key_lengths, $factor = 30) {
  sort($key_lengths, SORT_NUMERIC);
  $n = count($key_lengths);
  $length = 0;
  $best_length = 0;
  $cost = $n * $factor;
  $best_cost = $cost;
  for ($i=0; $i<$n; ++$i) {
    $increment = $key_lengths[$i] - $length;
    $length = $key_lengths[$i];
    $cost += $i * $increment;
    $cost -= $factor;
    if ($cost < $best_cost) {
      $best_cost = $cost;
      $best_length = $length;
    }
  }
  return $best_length;
}


function crumbs_admin_form_submit($form, &$form_state) {

  module_load_include('inc', 'crumbs', 'crumbs.plugin_engine');

  $weights = _crumbs_load_weights();
  asort($weights);
  $plugins = _crumbs_load_plugins();
  list($available_keys, $keys_by_plugin) = _crumbs_load_available_keys($plugins);

  $weights = array();

  $text = $form_state['values']['settings'];
  $lines = explode("\n", $text);
  $weight = 0;
  foreach ($lines as $line) {
    $line = trim($line);
    list($key, $title) = explode(' ', $line, 2);
    if (isset($available_keys[$key])) {
      $weights[$key] = $weight;
      ++ $weight;
    } else if (preg_match('/^-/', $line)) {
      if ($weight !== FALSE) {
        $weight = FALSE;
      } else {
        break;
      }
    }
  }
  variable_set('crumbs_weights', $weights);
}


function _crumbs_load_available_keys(array $plugins) {
  // we can't use the plugin engine,
  // or else we would miss disabled plugins.
  $invokeAction = new _crumbs_InvokeAction_define();
  foreach ($plugins as $plugin_key => $plugin) {
    $invokeAction->invoke($plugin, $plugin_key);
  }
  return array($invokeAction->getKeys(), $invokeAction->getKeysByPlugin());
}


/**
 * This class uses the InvokeAction pattern, but it does not implement any of
 * the InvokeAction interfaces. This is because it is not supposed to be used
 * with the PluginEngine, but rather from a custom function (see above).
 */
class _crumbs_InvokeAction_define {

  protected $_keys = array('*' => TRUE);
  protected $_keysByPlugin = array();
  protected $_pluginKey;
  protected $_definitionHandler_one;
  protected $_definitionHandler_many;

  function __construct() {
    $this->_definitionHandler_one = new _crumbs_PluginDefinitionHandler_one($this);
    $this->_definitionHandler_many = new _crumbs_PluginDefinitionHandler_many($this);
  }

  function invoke($plugin, $plugin_key) {
    $this->_pluginKey = $plugin_key;
    if (method_exists($plugin, 'defineOne')) {
      $plugin->defineOne($this->_definitionHandler_one);
    }
    if (method_exists($plugin, 'define')) {
      $plugin->define($this->_definitionHandler_many);
    }
    if (method_exists($plugin, 'defineMany')) {
      $plugin->defineMany($this->_definitionHandler_many);
    }
  }

  function addRule($key_suffix, $title) {
    $this->_addRule($this->_pluginKey .'.'. $key_suffix, $title);
  }

  function setTitle($title) {
    $this->_addRule($this->_pluginKey, $title);
  }

  protected function _addRule($key, $title) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    while (TRUE) {
      if (empty($fragments)) break;
      $wildcard_key = $partial_key .'.*';
      $this->_keys[$wildcard_key] = TRUE;
      $this->_keysByPlugin[$this->_pluginKey][$wildcard_key] = TRUE;
      $partial_key .= '.'. array_shift($fragments);
    }
    $this->_keys[$key] = $title;
    $this->_keysByPlugin[$this->_pluginKey][$key] = $title;
  }

  function getKeys() {
    return $this->_keys;
  }

  function getKeysByPlugin() {
    return $this->_keysByPlugin;
  }
}


/**
 * This class is used solely for arguments passed to the "define()" method
 * on plugin objects.
 * It wraps the invoke action, to make sure that the plugin does not call the
 * action's invoke() method.
 */
class _crumbs_PluginDefinitionHandler_one {

  protected $_invokeAction;

  function __construct($invoke_action) {
    $this->_invokeAction = $invoke_action;
  }

  function setTitle($title) {
    $this->_invokeAction->setTitle($title);
  }
}


/**
 * This class is used solely for arguments passed to the "define()" method
 * on plugin objects.
 * It wraps the invoke action, to make sure that the plugin does not call the
 * action's invoke() method.
 */
class _crumbs_PluginDefinitionHandler_many extends _crumbs_PluginDefinitionHandler_one {

  function addRule($key_suffix, $title = TRUE) {
    $this->_invokeAction->addRule($key_suffix, $title);
  }
}


