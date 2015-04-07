<?php

namespace Drupal\crumbs_ui\PluginKey;

/**
 * Hierarchy of plugin keys as raw strings, without meta information or status.
 */
class RawHierarchy implements RawHierarchyInterface {

  /**
   * @var string[]
   *   Format: $[$plugin_key] = $parent_key
   */
  private $keysParent = array();

  /**
   * @var string[][]
   *   Format: $[$plugin_key][] = $child_key
   */
  private $keysChildren = array();

  /**
   * @param \crumbs_Container_MultiWildcardData $keys_meta
   *
   * @return \Drupal\crumbs_ui\PluginKey\RawHierarchy
   */
  static function createFromKeysMeta(\crumbs_Container_MultiWildcardData $keys_meta) {
    $keys = array();
    foreach ($keys_meta as $key => $meta) {
      $keys[] = $key;
    }
    return static::createFromKeys($keys);
  }

  /**
   * @param string[] $plugin_keys_str
   *   Format: $[] = $plugin_key_str
   *
   * @return static
   */
  static function createFromKeys(array $plugin_keys_str) {
    $keys_parent = static::keysFindParents($plugin_keys_str);
    return new static($keys_parent);
  }

  /**
   * @param string[] $plugin_keys_str
   *   Format: $[] = $plugin_key_str
   *
   * @return string[]
   *   Format: $[$plugin_key_str] = $parent_key_str
   */
  static function keysFindParents(array $plugin_keys_str) {
    $keys_parent = array();
    foreach ($plugin_keys_str as $plugin_key_str) {
      $parent_key_str = Util::pluginKeyGetParent($plugin_key_str);
      if (isset($parent_key_str)) {
        $keys_parent[$plugin_key_str] = $parent_key_str;
      }
    }
    return $keys_parent;
  }

  /**
   * @param string[] $keys_parent
   *   Format: $[$key] = $parent_key
   */
  function __construct(array $keys_parent) {
    foreach ($keys_parent as $key => $parent) {
      $this->keysChildren[$parent][] = $key;
    }
    $this->keysParent = $keys_parent;
  }

  /**
   * @param string $plugin_key_str
   *
   * @return string|NULL
   */
  public function keyGetParentKey($plugin_key_str) {
    return isset($this->keysParent[$plugin_key_str])
      ? $this->keysParent[$plugin_key_str]
      : NULL;
  }

  /**
   * @param string $plugin_key_str
   *
   * @return string[]
   */
  public function keyGetChildren($plugin_key_str) {
    return isset($this->keysChildren[$plugin_key_str])
      ? $this->keysChildren[$plugin_key_str]
      : array();
  }

  /**
   * @param string $key
   *
   * @return bool
   */
  public function keyIsWildcard($key) {
    return '*' === $key || '.*' === substr($key, -2);
  }
}
