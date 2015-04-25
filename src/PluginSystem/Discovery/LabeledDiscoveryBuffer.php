<?php

namespace Drupal\crumbs\PluginSystem\Discovery;

use Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection;

class LabeledDiscoveryBuffer extends PluginDiscoveryBuffer {

  protected function load() {
    if (!isset($this->parentPluginCollection)) {
      $this->parentPluginCollection = new LabeledPluginCollection();
      $this->titlePluginCollection = new LabeledPluginCollection();
      $this->pluginDiscovery->discoverPlugins($this->parentPluginCollection, $this->titlePluginCollection);
    }
  }

}
