<?php

namespace Drupal\crumbs\PluginSystem\Collection\PluginCollection;

interface DescriptionCollectionInterface {

  /**
   * @param string $key
   * @param string $description
   */
  function addDescription($key, $description);

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
  function translateDescription($key, $description, $args = array());
}
