<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset;

use Drupal\crumbs\PluginSystem\Discovery\Collection\PluginCollectionInterface;

class ArgumentOffset implements ArgumentOffsetInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Collection\PluginCollectionInterface
   */
  private $pluginCollection;

  /**
   * @var string
   */
  private $key;

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\PluginCollectionInterface $pluginCollection
   * @param string $key
   */
  public function __construct(PluginCollectionInterface $pluginCollection, $key) {
    $this->pluginCollection = $pluginCollection;
    $this->key = $key;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description) {
    $this->pluginCollection->addDescription($this->key, $description);
    return $this;
  }

  /**
   * @param string $description
   * @param string[] $args
   *
   * @return $this
   */
  function translateDescription($description, $args = array()) {
    $this->pluginCollection->translateDescription($this->key, $description, $args);
    return $this;
  }

  /**
   * @return $this
   */
  function disabledByDefault() {
    $this->pluginCollection->setDefaultStatus($this->key, FALSE);
    return $this;
  }

  /**
   * Only to be used for testing purposes.
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Collection\PluginCollectionInterface
   */
  public function getPluginCollection() {
    return $this->pluginCollection;
  }
}
