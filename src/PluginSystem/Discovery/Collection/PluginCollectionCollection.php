<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Collection;

class PluginCollectionCollection implements PluginCollectionInterface {

  /**
   * @var PluginCollectionInterface[]
   */
  private $pluginCollections = array();

  /**
   * @param PluginCollectionInterface[] $pluginCollections
   */
  function __construct(array $pluginCollections) {
    $this->pluginCollections = $pluginCollections;
  }

  /**
   * @param string $key
   * @param string $description
   */
  public function addDescription($key, $description) {
    foreach ($this->pluginCollections as $pluginCollection) {
      $pluginCollection->addDescription($key, $description);
    }
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
    foreach ($this->pluginCollections as $pluginCollection) {
      $pluginCollection->translateDescription($key, $description, $args);
    }
  }

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   * @param string|NULL $route
   */
  function addMonoPlugin($key, \crumbs_MonoPlugin $plugin, $route = NULL) {
    foreach ($this->pluginCollections as $pluginCollection) {
      $pluginCollection->addMonoPlugin($key, $plugin, $route);
    }
  }

  /**
   * @param string $key
   *   The plugin key, without the '.*'.
   * @param \crumbs_MultiPlugin $plugin
   * @param string|NULL $route
   */
  function addMultiPlugin($key, \crumbs_MultiPlugin $plugin, $route = NULL) {
    foreach ($this->pluginCollections as $pluginCollection) {
      $pluginCollection->addMultiPlugin($key, $plugin, $route);
    }
  }

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status) {
    foreach ($this->pluginCollections as $pluginCollection) {
      $pluginCollection->setDefaultStatus($key, $status);
    }
  }
}
