<?php

namespace Drupal\crumbs\PluginSystem\TreePosition;

abstract class TreePositionBase implements TreePositionInterface {

  /**
   * @var TreePosition|null
   */
  private $parentTreePosition;

  /**
   * @var string
   */
  private $shortkey = '*';

  /**
   * @var string
   */
  private $key = '*';

  /**
   * @var string
   */
  private $prefix = '';

  /**
   * @var int
   */
  private $depth = 0;

  /**
   * @var bool|null
   */
  private $status;

  /**
   * @var int|null
   */
  private $weight;

  /**
   * @param bool|null $status
   * @param int|null $weight
   */
  public function __construct($status, $weight) {
    $this->status = $status;
    $this->weight = $weight;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionBase $parent
   * @param string $suffix
   * @param bool $isLeaf
   */
  function setParent(TreePositionBase $parent, $suffix, $isLeaf) {
    $this->shortkey = $suffix;
    if (!$isLeaf) {
      $this->shortkey .= '.*';
    }
    $this->key = $parent->prefix . $this->shortkey;
    $this->shortkey = $suffix;
    $this->prefix = $parent->prefix . $suffix . '.';
    $this->parentTreePosition = $parent;
    $this->depth = $parent->depth + 1;
    if (!isset($this->status)) {
      $this->status = $parent->getStatus();
    }
    if (!isset($this->weight)) {
      $this->weight = $parent->getWeight();
    }
  }

  /**
   * @return string
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * @return string
   */
  public function getKeyPrefix() {
    return $this->prefix;
  }

  /**
   * @return string
   */
  public function getDescription() {
    return $this->shortkey;
  }

  /**
   * @return TreePosition|null
   */
  public function getParent() {
    return $this->parentTreePosition;
  }

  /**
   * @return int
   */
  public function getDepth() {
    return $this->depth;
  }

  /**
   * @return bool
   */
  function getStatus() {
    return isset($this->status) ? $this->status : TRUE;
  }

  /**
   * @return int
   */
  function getWeight() {
    return isset($this->weight) ? $this->weight : 0;
  }

  /**
   * @return int|bool
   *   The weight, if enabled. FALSE, if disabled.
   */
  function getWeightOrFalse() {
    return $this->getStatus()
      ? $this->getWeight()
      : FALSE;
  }

  /**
   * @return bool
   */
  function isPluginPosition() {
    return ($treeNode = $this->getTreeNode())
      ? $treeNode->isPluginNode()
      : FALSE;
  }

}
