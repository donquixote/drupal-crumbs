<?php

namespace Drupal\crumbs\PluginSystem\PluginType;

class TitlePluginType implements PluginTypeInterface {

  /**
   * @return string
   *   The key for plugin weights and statuses, to be used in variable_get().
   */
  public function getSettingsKey() {
    return 'crumbs-title_plugin_settings';
  }

}
