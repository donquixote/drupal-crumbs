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
   * @var bool[]
   *   Format: $[$key] = TRUE
   */
  private $keysWithSolidChildren = array();

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
   * @param \crumbs_Container_MultiWildcardData $keys_meta
   *
   * @return \Drupal\crumbs_ui\PluginKey\RawHierarchy
   */
  static function createFromKeysMetaFiltered(\crumbs_Container_MultiWildcardData $keys_meta) {
    $keys = array();
    foreach ($keys_meta as $key => $meta) {
      $methods = (array)$meta->basicMethods + (array)$meta->routeMethods;
      if (!empty($methods) && empty($methods['findParent'])) {
        continue;
      }
      $keys[] = $key;
    }
    return static::createFromKeys($keys);
  }

  /**
   * @param string[] $plugin_keys
   *   Format: $[] = $plugin_key
   *
   * @return static
   */
  static function createFromKeys(array $plugin_keys) {
    $keys_parent = static::keysFindParentsComplete($plugin_keys);
    return new static($keys_parent);
  }

  /**
   * @param array $plugin_keys
   *
   * @return mixed[]
   *   Format: $[$plugin_key] = $parent_key|NULL
   */
  static function keysFindParentsComplete(array $plugin_keys) {
    $plugin_keys = array_fill_keys($plugin_keys, TRUE);
    $keys_parent = array();
    while (!empty($plugin_keys)) {
      $keys_parent_new = static::keysFindParents($plugin_keys);
      $keys_parent += $keys_parent_new;
      $plugin_keys = array();
      foreach ($keys_parent_new as $parent_key) {
        if (isset($parent_key) && !isset($keys_parent[$parent_key])) {
          $plugin_keys[$parent_key] = TRUE;
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
      $parent_key = Util::pluginKeyGetParent($key);
      $keys_parent[$key] = $parent_key;
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
    $this->fillSolidChildren();
    $this->keysParent = $keys_parent;
  }

  /**
   * Fills the $this->
   */
  private function fillSolidChildren() {
    $newSolidKeys = array();
    foreach ($this->keysParent as $key => $parent_key) {
      if ($this->keyIsWildcard($key)) {
        $newSolidKeys[$key] = TRUE;
      }
    }

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
  public function keyHasSolidChildren($key) {
    return !empty($this->keysWithSolidChildren[$key]);
  }
}
