<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Buffer;

class LabeledDiscoveryBuffer extends PluginDiscoveryBuffer {

  protected function load() {
    parent::load();
    /*
    if (!isset($this->parentCollectionContainer)) {
      $this->parentCollectionContainer = new LabeledPluginCollection();
      $this->titleCollectionContainer = new LabeledPluginCollection();
      $this->pluginDiscovery->discoverPlugins($this->parentCollectionContainer, $this->titleCollectionContainer);
    }
    */
  }

}
