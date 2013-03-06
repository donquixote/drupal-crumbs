<?php

class crumbs_Admin_ElementObject_WeightsAbstract extends crumbs_Admin_ElementObject_Abstract {

  /**
   * TODO:
   *   This should not be part of the form element type.
   *   We will clean this up later.
   */
  protected function loadAvailableKeys($plugins) {
    // we can't use the plugin engine,
    // or else we would miss disabled plugins.
    $op = new crumbs_PluginOperation_describe();
    foreach ($plugins as $plugin_key => $plugin) {
      $op->invoke($plugin, $plugin_key);
    }
    return array($op->getKeys(), $op->getKeysByPlugin());
  }

  /**
   * Callback for $element['#element_validate']
   */
  function validate(&$element, &$form_state) {
    // We need to unset the NULL values from child elements we created.
    $weights = $element['#value'];
    form_set_value($element, $weights, $form_state);
  }
}
