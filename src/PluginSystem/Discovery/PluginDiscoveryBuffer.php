<?php

namespace Drupal\crumbs\PluginSystem\Discovery;

use Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection;

class PluginDiscoveryBuffer {

  /**
   * @var RawPluginCollection|NULL
   */
  protected $parentPluginCollection;

  /**
   * @var RawPluginCollection|NULL
   */
  protected $titlePluginCollection;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery
   */
  protected $pluginDiscovery;

  /**
   * @return static
   */
  static function create() {
    $discovery = PluginDiscovery::create();
    return new static($discovery);
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery $pluginDiscovery
   */
  function __construct(PluginDiscovery $pluginDiscovery) {
    $this->pluginDiscovery = $pluginDiscovery;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection
   */
  function getParentPluginCollection() {
    $this->load();
    return $this->parentPluginCollection;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection
   */
  function getTitlePluginCollection() {
    $this->load();
    return $this->titlePluginCollection;
  }

  protected function load() {
    if (!isset($this->parentPluginCollection)) {
      $this->parentPluginCollection = new RawPluginCollection();
      $this->titlePluginCollection = new RawPluginCollection();
      $this->pluginDiscovery->discoverPlugins($this->parentPluginCollection, $this->titlePluginCollection);
    }
  }

  function reset() {
    $this->parentPluginCollection = NULL;
    $this->titlePluginCollection = NULL;
  }
}
