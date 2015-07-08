<?php

namespace Drupal\crumbs\PluginSystem\TreePosition;

interface TreePositionInterface {

  /**
   * @return string
   */
  function getKey();

  /**
   * @return string
   */
  function getKeyPrefix();

  /**
   * @return static|null|self
   */
  function getParent();

  /**
   * @return int
   */
  function getDepth();

  /**
   * @return static[]
   */
  function getChildren();

  /**
   * @return bool
   */
  function hasChildren();

  /**
   * @return bool
   */
  function isLeaf();

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface|null
   */
  function getTreeNode();

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface
   *
   * @throws \Exception
   */
  function requireTreeNode();

  /**
   * @return string
   */
  function getDescription();

  /**
   * @return bool
   */
  function getStatus();

  /**
   * @return int
   */
  function getWeight();

  /**
   * @return int|bool
   *   The weight, if enabled. FALSE, if disabled.
   */
  function getWeightOrFalse();

  /**
   * @return bool
   */
  function isPluginPosition();

}
