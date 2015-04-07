<?php

namespace Drupal\crumbs_ui\PluginKey;

class Util {

  /**
   * @param string $plugin_key
   *
   * @return string|NULL
   */
  static function pluginKeyGetParent($plugin_key) {
    if ('*' === $plugin_key) {
      return NULL;
    }
    $starless = ('.*' === substr($plugin_key, -2))
      ? substr($plugin_key, 0, -2)
      : $plugin_key;
    return (FALSE !== $dotpos = strrpos($starless, '.'))
      ? substr($starless, 0, $dotpos) . '.*'
      : '*';
  }
}
