<?php

namespace Drupal\crumbs\PluginSystem\PluginType;

use Drupal\crumbs\PluginSystem\Plugin\ParentPluginInterface;

class ParentPluginType implements PluginTypeInterface {

  /**
   * @return string
   *   The key for plugin weights and statuses, to be used in variable_get().
   */
  public function getSettingsKey() {
    return 'crumbs-parent_plugin_settings';
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @return bool
   */
  public function validatePlugin(\crumbs_PluginInterface $plugin) {
    return $plugin instanceof ParentPluginInterface;
  }

  /**
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return bool
   */
  public function validateMonoPlugin(\crumbs_MonoPlugin $plugin) {
    return $plugin instanceof \crumbs_MonoPlugin_FindParentInterface;
  }

  /**
   * @param \crumbs_MultiPlugin $plugin
   *
   * @return bool
   */
  public function validateMultiPlugin(\crumbs_MultiPlugin $plugin) {
    return $plugin instanceof \crumbs_MultiPlugin_FindParentInterface;
  }
}
