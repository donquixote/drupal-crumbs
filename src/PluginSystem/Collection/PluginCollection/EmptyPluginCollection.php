<?php

namespace Drupal\crumbs\PluginSystem\Collection\PluginCollection;

class EmptyPluginCollection implements PluginCollectionInterface {

  /**
   * @param string $key
   * @param string $description
   */
  public function addDescription($key, $description) {
    // Do nothing.
  }

  /**
   * @param string $key
   * @param string $description
   *   The description in English.
   * @param string[] $args
   *   Placeholders to be inserted into the translated description.
   *
   * @see t()
   * @see format_string()
   */
  public function translateDescription($key, $description, $args = array()) {
    // Do nothing.
  }

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   * @param string|NULL $route
   */
  function addMonoPlugin($key, \crumbs_MonoPlugin $plugin, $route = NULL) {
    // Do nothing.
  }

  /**
   * @param string $key
   *   The plugin key, without the '.*'.
   * @param \crumbs_MultiPlugin $plugin
   * @param string|NULL $route
   */
  function addMultiPlugin($key, \crumbs_MultiPlugin $plugin, $route = NULL) {
    // Do nothing.
  }

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status) {
    // Do nothing.
  }
}
