<?php

namespace Drupal\crumbs\PluginApi\PluginOffset;


use Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface;

class PluginOffset implements TreeOffsetMetaInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface
   */
  private $treeCollection;

  /**
   * @var string
   */
  private $key;

  /**
   * @param \Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollectionInterface $treeCollection
   * @param string $key
   */
  public function __construct(TreeCollectionInterface $treeCollection, $key) {
    $this->treeCollection = $treeCollection;
    $this->key = $key;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description) {
    $this->treeCollection->addDescription($this->key, $description);
    return $this;
  }

  /**
   * @param string $description
   * @param string[] $args
   *
   * @return $this
   */
  function translateDescription($description, $args = array()) {
    $this->treeCollection->translateDescription($this->key, $description, $args);
    return $this;
  }

  /**
   * @return $this
   */
  function disabledByDefault() {
    $this->treeCollection->setDefaultStatus($this->key, FALSE);
    return $this;
  }

  /**
   * Only to be used for testing purposes.
   *
   * @return \Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollectionInterface
   */
  public function getPluginCollection() {
    return $this->treeCollection;
  }
}
