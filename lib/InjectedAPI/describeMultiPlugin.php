<?php


/**
 * Injected API object for the describe() method of multi plugins.
 */
class crumbs_InjectedAPI_describeMultiPlugin {

  protected $pluginOperation;

  function __construct($plugin_operation) {
    $this->pluginOperation = $plugin_operation;
  }

  function addRule($key_suffix, $title = TRUE) {
    $this->pluginOperation->addRule($key_suffix, $title);
  }

  function ruleWithLabel($key_suffix, $title, $label) {
    $this->addRule($key_suffix, t('!key: !value', array(
      '!key' => $label,
      '!value' => $title,
    )));
  }

  function addDescription($description, $key_suffix = '*') {
    $this->pluginOperation->addDescription($description, $key_suffix);
  }

  function descWithLabel($description, $label, $key_suffix = '*') {
    $this->addDescription(t('!key: !value', array(
      '!key' => $label,
      '!value' => $description,
    )), $key_suffix);
  }
}
