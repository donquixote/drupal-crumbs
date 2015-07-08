<?php

namespace Drupal\crumbs\PluginSystem\PluginType;

interface PluginTypeInterface {

  /**
   * @return string
   *   The key for plugin weights and statuses, to be used in variable_get().
   *   E.g. 'crumbs-parent_plugin_settings'.
   */
  public function getSettingsKey();

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @return bool
   */
  public function validatePlugin(\crumbs_PluginInterface $plugin);

  /**
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return bool
   */
  public function validateMonoPlugin(\crumbs_MonoPlugin $plugin);

  /**
   * @param \crumbs_MultiPlugin $plugin
   *
   * @return bool
   */
  public function validateMultiPlugin(\crumbs_MultiPlugin $plugin);
}
