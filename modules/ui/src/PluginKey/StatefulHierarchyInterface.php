<?php

namespace Drupal\crumbs_ui\PluginKey;

/**
 * Contains information about plugin keys, their inheritance relationships, and
 * their current status.
 */
interface StatefulHierarchyInterface {

  /**
   * @param string $plugin_key_str
   *
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface|NULL
   */
  public function keyGetParentKey($plugin_key_str);

  /**
   * @param string $plugin_key_str
   *
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface[]
   */
  public function keyGetChildren($plugin_key_str);

  /**
   * @param string $plugin_key_str
   *
   * @return bool
   */
  public function keyHasExplicitValue($plugin_key_str);

  /**
   * @param string $plugin_key_str
   *
   * @return bool
   *   TRUE, if the plugin key is enabled (explicitly, or via inheritance).
   *   FALSE, otherwise.
   */
  public function keyIsEnabled($plugin_key_str);

  /**
   * @param string $plugin_key_str
   *
   * @return int|FALSE
   *   A numeric weight, if the plugin key is enabled.
   *   FALSE, otherwise.
   */
  public function keyGetWeight($plugin_key_str);
}
