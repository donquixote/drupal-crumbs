<?php

namespace Drupal\crumbs\PluginSystem\PluginType;

interface PluginTypeInterface {

  /**
   * @return string
   *   The key for plugin weights and statuses, to be used in variable_get().
   *   E.g. 'crumbs-parent_plugin_settings'.
   */
  public function getSettingsKey();
}
