<?php

namespace Drupal\crumbs\PluginSystem\Collection\PluginCollection;

class TreeCollection implements TreeCollectionInterface {

  /**
   * @param string $key
   * @param string $description
   */
  public function addDescription($key, $description) {
    // TODO: Implement addDescription() method.
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
    // TODO: Implement translateDescription() method.
  }

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status) {
    // TODO: Implement setDefaultStatus() method.
  }
}
