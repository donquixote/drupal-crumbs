<?php
namespace Drupal\crumbs_ui\PluginKey;

/**
 * Hierarchy of plugin keys as raw strings, without meta information or status.
 */
interface RawHierarchyInterface_ {

  /**
   * @param string $plugin_key_str
   *
   * @return string|NULL
   */
  public function keyGetParentKey($plugin_key_str);

  /**
   * @param string $plugin_key_str
   *
   * @return string[]
   */
  public function keyGetChildren($plugin_key_str);
}
