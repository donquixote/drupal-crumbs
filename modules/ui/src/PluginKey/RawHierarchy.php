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
   * @param bool[] $keys
   *   Format: $[$plugin_key] = TRUE
   *
   * @return static
   */
  static function createFromKeys(array $keys) {
    $keys_parent = static::keysFindParentsComplete($keys);
    return new static($keys_parent);
  }

  /**
   * @param bool[] $keys
   *   Format: $[$key] = TRUE
   *
   * @return mixed[]
   *   Format: $[$key] = $parent_key|NULL
   */
  static function keysFindParentsComplete(array $keys) {
    $keys_parent = array();
    while (!empty($keys)) {
      $keys_parent_new = static::keysFindParents($keys);
      $keys_parent += $keys_parent_new;
      $keys = array();
      foreach ($keys_parent_new as $parent_key) {
        if (isset($parent_key) && !isset($keys_parent[$parent_key])) {
          $keys[$parent_key] = TRUE;
        }
      }
    }
    return $keys_parent;
  }

  /**
   * @param bool[] $keys
   *   Format: $[$plugin_key] = TRUE
   *
   * @return string[]
   *   Format: $[$key] = $parent_key
   */
  static function keysFindParents(array $keys) {
    $keys_parent = array();
    foreach ($keys as $key => $cTrue) {
      $keys_parent[$key] = Util::pluginKeyGetParent($key);
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

  /**
   * @param string $key
   *
   * @return bool
   */
  public function keyExists($key) {
    return array_key_exists($key, $this->keysParent);
  }
}
