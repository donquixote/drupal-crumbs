<?php


/**
 * Injected API object for the describe() method of mono plugins.
 */
class crumbs_InjectedAPI_describeMonoPlugin {

  /**
   * @var crumbs_PluginOperation_describe
   */
  protected $pluginOperation;

  /**
   * @param crumbs_PluginOperation_describe $plugin_operation
   */
  function __construct($plugin_operation) {
    $this->pluginOperation = $plugin_operation;
  }

  function setTitle($title) {
    $this->pluginOperation->setTitle($title);
  }

  function titleWithLabel($title, $label) {
    $this->setTitle(t('!key: !value', array(
      '!key' => $label,
      '!value' => $title,
    )));
  }
}
