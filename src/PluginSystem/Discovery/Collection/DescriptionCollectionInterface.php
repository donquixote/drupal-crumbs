<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Collection;

interface DescriptionCollectionInterface {

  /**
   * @param string $key
   * @param string $description
   */
  public function addDescription($key, $description);

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
  public function translateDescription($key, $description, $args = array());
}
