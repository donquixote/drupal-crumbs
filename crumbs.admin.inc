<?php
// $Id$


function crumbs_admin_form() {
  $form = array();
  
  $text = <<<EOT
    <p>Reorder and enable/disable crumbs rules.</p>
    <p>The title coming after the first space in each row can be ignored, it has no effect. What matters is the key before the first space.</p>
    <p>Hint: Copy+paste to edit this text to your favourite text editor, or to import, export and backup. Text editor beats tabledrag, don't you think?</p>
    <p>If a newly installed module introduces new crumbs rules, these rules will find themselves in the "inherit" section at first. Priority and enabled/disabled status of rules in the "inherit" section is inherited from matching wildcard rules in the "enabled" or "disabled" section. The '*' wildcard rule counts a enabled, if it is in the inherit section itself.</p>
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

  module_load_include('inc', 'crumbs', 'crumbs.PluginEngine');

  $available_keys = _crumbs_load_available_keys();
  $weights = _crumbs_load_weights();

  return _crumbs_build_default_text($available_keys, $weights);
}


function _crumbs_build_default_text(array $available_keys, array $weights) {

  $lines = array(
    'inherit' => $available_keys,
    'enabled' => array(),
    'disabled' => array(),
  );

  foreach ($weights as $key => $weight) {
    $section = ($weight === FALSE) ? 'disabled' : 'enabled';
    $string = $key;
    if (isset($available_keys[$key])) {
      $title = $available_keys[$key];
      if (is_string($title)) {
        $string .= ' ('. $title .')';
      }
    }
    else if ($key !== '*') {
      // an orphan setting.
      $string .= ' (orphan rule)';
    }
    $lines[$section][$key] = $string;
    unset($lines['inherit'][$key]);
  }

  foreach ($lines['inherit'] as $key => $title) {
    $string = $key;
    if (is_string($title)) {
      $string .= ' ('. $title .')';
    }
    $lines['inherit'][$key] = $string;
  }

  return "\n\n"
    . implode("\n", $lines['enabled'])
    . "\n\n---- disabled ----\n" . implode("\n", $lines['disabled'])
    . "\n\n---- inherit ----\n" . implode("\n", $lines['inherit'])
    . "\n\n"
  ;
}


function crumbs_admin_form_submit($form, &$form_state) {

  module_load_include('inc', 'crumbs', 'crumbs.PluginEngine');

  $weights = _crumbs_load_weights();
  asort($weights);
  $available_keys = _crumbs_load_available_keys();

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


function _crumbs_load_available_keys() {
  // we can't use the plugin engine,
  // or else we would miss disabled plugins.
  $plugins = _crumbs_load_plugins();
  $invokeAction = new _crumbs_InvokeAction_define();
  foreach ($plugins as $plugin_key => $plugin) {
    $invokeAction->invoke($plugin, $plugin_key);
  }
  return $invokeAction->getKeys();
}


class _crumbs_InvokeAction_define {

  protected $_keys = array();
  protected $_plugin_key;
  protected $_definitionHandler;

  function __construct() {
    $this->_definitionHandler = new _crumbs_PluginDefinitionHandler($this);
  }

  function invoke($plugin, $plugin_key) {
    $this->_plugin_key = $plugin_key;
    if (method_exists($plugin, 'define')) {
      $plugin->define($this->_definitionHandler);
    }
    else {
      $this->_addRule($plugin_key, TRUE);
    }
  }

  function addRule($key_suffix, $title) {
    $this->_addRule($this->_plugin_key .'.'. $key_suffix, $title);
  }

  function setTitle($title) {
    $this->_addRule($this->_plugin_key, $title);
  }

  protected function _addRule($key, $title) {
    $fragments = explode('.', $key);
    $partial_key = array_shift($fragments);
    while (TRUE) {
      if (empty($fragments)) break;
      $wildcard_key = $partial_key .'.*';
      $this->_keys[$wildcard_key] = TRUE;
      $partial_key .= '.'. array_shift($fragments);
    }
    $this->_keys[$key] = $title;
  }

  function enabledByDefault($enabled) {
    // TODO: Implement this.
  }

  function getKeys() {
    return $this->_keys;
  }
}


class _crumbs_PluginDefinitionHandler {

  protected $_invokeAction;

  function __construct($invokeAction) {
    $this->_invokeAction = $invokeAction;
  }

  function addRule($key_suffix, $title = TRUE) {
    $this->_invokeAction->addRule($key_suffix, $title);
  }

  function setTitle($title) {
    $this->_invokeAction->setTitle($title);
  }

  function enabledByDefault($enabled) {
    $this->_invokeAction->enabledByDefault($enabled);
  }
}


