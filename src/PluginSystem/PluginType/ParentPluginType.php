<?php

namespace Drupal\crumbs\PluginSystem\PluginType;

class ParentPluginType implements PluginTypeInterface {

  /**
   * @return string
   *   The key for plugin weights and statuses, to be used in variable_get().
   */
  public function getSettingsKey() {
    return 'crumbs-parent_plugin_settings';
  }
}
