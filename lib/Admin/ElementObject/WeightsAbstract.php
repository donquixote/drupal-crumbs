<?php

class crumbs_Admin_ElementObject_WeightsAbstract extends crumbs_Admin_ElementObject_Abstract {

  protected function loadAvailableKeys($plugins) {
    // we can't use the plugin engine,
    // or else we would miss disabled plugins.
    $plugin_operation = new crumbs_PluginOperation_describe();
    foreach ($plugins as $plugin_key => $plugin) {
      $plugin_operation->invoke($plugin, $plugin_key);
    }
    return array($plugin_operation->getKeys(), $plugin_operation->getKeysByPlugin());
  }
}
