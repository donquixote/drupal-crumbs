<?php

namespace Drupal\crumbs_ui\PluginKey;

/**
 * Hierarchy of plugin keys with status / weight.
 */
class StatefulHierarchy implements StatefulHierarchyInterface {

  /**
   * @var \Drupal\crumbs_ui\PluginKey\RawHierarchy
   */
  private $rawHierarchy;

  /**
   * @var \crumbs_PluginSystem_PluginInfo
   */
  private $pluginInfo;

  /**
   * @var \crumbs_Container_MultiWildcardData
   */
  private $availableKeysMeta;

  /**
   * @param \crumbs_PluginSystem_PluginInfo $plugin_info
   */
  function __construct_(\crumbs_PluginSystem_PluginInfo $plugin_info) {
    $this->pluginInfo = $plugin_info;
    $this->availableKeysMeta = $plugin_info->availableKeysMeta;
    $this->rawHierarchy = RawHierarchy::createFromKeysMeta($this->availableKeysMeta);
  }

  /**
   * @param \Drupal\crumbs_ui\PluginKey\RawHierarchyInterface $raw_hierarchy
   * @param array $data
   *   Plugin keys (wildcard or leaf) with explicit status (int weight or 'disabled')
   *   Format: $['menu.hierarchy.*'] = 'disabled'.
   */
  function __construct(RawHierarchyInterface $raw_hierarchy, array $data) {
    $this->rawHierarchy = $raw_hierarchy;
    $this->data = $data;
  }

  /**
   * @param string $plugin_key_str
   *
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface|NULL
   */
  public function keyGetParentKey($plugin_key_str) {
    $parent_key_str = $this->rawHierarchy->keyGetParentKey($plugin_key_str);
    return isset($parent_key_str)
      ? $this->keyGetKey($parent_key_str)
      : NULL;
  }

  /**
   * @param string $plugin_key_str
   *
   * @return \Drupal\crumbs_ui\PluginKey\PluginKeyInterface[]
   */
  public function keyGetChildren($plugin_key_str) {
    $children_str = $this->rawHierarchy->keyGetChildren($plugin_key_str);
    $children = array();
    foreach ($children_str as $child_str) {
      $children[$child_str] = $this->keyGetKey($child_str);
    }
    return $children;
  }

  /**
   * @param string $plugin_key_str
   *
   * @return \Drupal\crumbs_ui\PluginKey\PluginKey
   */
  public function keyGetKey($plugin_key_str) {
    return new PluginKey($plugin_key_str, $this->availableKeysMeta[$plugin_key_str], $this);
  }

  /**
   * @param string $plugin_key_str
   *
   * @return bool
   */
  public function keyHasExplicitValue($plugin_key_str) {
    return isset($this->pluginInfo->userWeights[$plugin_key_str])
      || isset($this->pluginInfo->defaultWeights[$plugin_key_str]);
  }

  /**
   * @param string $plugin_key_str
   *
   * @return bool
   *   TRUE, if the plugin key is enabled (explicitly, or via inheritance).
   *   FALSE, otherwise.
   */
  public function keyIsEnabled($plugin_key_str) {
    $weight = $this->pluginInfo->weightMap->valueAtKey($plugin_key_str);
    return is_numeric($weight);
  }

  /**
   * @param string $plugin_key_str
   *
   * @return int|FALSE
   *   A numeric weight, if the plugin key is enabled.
   *   FALSE, otherwise.
   */
  public function keyGetWeight($plugin_key_str) {
    return $this->pluginInfo->weightMap->valueAtKey($plugin_key_str);
  }
}
