<?php
namespace Drupal\crumbs\PluginSystem\Tree;

interface TreeNodeInterface {

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode[]
   */
  function getChildren();

  /**
   * Adds a child to the tree node, if it does not exist, and returns it.
   *
   * @param string|null $key
   * @param bool|null $isLeaf
   *
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   * @throws \Exception
   */
  function child($key, $isLeaf = NULL);

  /**
   * @return bool
   */
  function isLeaf();

  /**
   * @return bool
   */
  function isEmpty();

  /**
   * @return bool|null
   */
  function getStatus();

  /**
   * @return int|null
   */
  function getWeight();

  /**
   * @return \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface
   */
  function getPluginType();

  /**
   * @return \crumbs_PluginInterface|null
   */
  function getPlugin();

  /**
   * @return \crumbs_PluginInterface[]
   */
  function getRoutePlugins();

  /**
   * @return bool
   */
  function isPluginNode();

}
