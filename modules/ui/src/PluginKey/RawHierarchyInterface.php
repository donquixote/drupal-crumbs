<?php

namespace Drupal\crumbs_ui\PluginKey;

/**
 * Contains information about available keys, their parent-child relationships,
 * descriptions and other data discovered and derived from
 * hook_crumbs_plugins().
 */
interface RawHierarchyInterface {

  /**
   * @param string $parent_key
   *   Parent key, e.g. 'menu.*'
   *
   * @return string[]
   *   Child keys.
   *   Format: ['menu.hierarchy.*']
   */
  public function keyGetChildren($parent_key);

  /**
   * @param string $key
   *
   * @return bool
   */
  public function keyIsWildcard($key);
}
