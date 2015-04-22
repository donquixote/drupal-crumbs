<?php

namespace Drupal\crumbs\PluginSystem\Settings;

class KeyMap {

  /**
   * @var bool[]
   *   Format: $[$key] = TRUE
   */
  private $keys;

  /**
   * @param bool[] $keys
   *   Format: $[$key] = TRUE
   */
  function __construct(array $keys) {
    if (!isset($keys['*'])) {
      throw new \InvalidArgumentException("Fallback key missing.");
    }
    $this->keys = $keys;
  }

  /**
   * @param string $key
   *
   * @return string
   */
  function keyLookup($key) {
    if (isset($this->keys[$key])) {
      return $key;
    }
    while (FALSE !== $pos = strrpos($key, '.')) {
      $key = substr($key, 0, $pos);
      $wildcard = $key . '.*';
      if (isset($this->keys[$wildcard])) {
        return $wildcard;
      }
    }
    return '*';
  }
}
