<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Parent\DefaultImplementation;

use Drupal\crumbs\PluginApi\SpecFamily\Parent\BaseFamilyInterface;
use Drupal\crumbs\PluginSystem\Tree\TreeNode;

/**
 * Base clas for:
 * @see Route
 * @see Family
 */
class BaseFamily implements BaseFamilyInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  private $treeNode;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $treeNode
   */
  function __construct(TreeNode $treeNode) {
    $this->treeNode = $treeNode;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  protected function getTreeNode() {
    return $this->treeNode;
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin|\crumbs_MultiPlugin_FindParentInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   * @throws \Exception
   */
  function multiPlugin($key, \crumbs_MultiPlugin_FindParentInterface $plugin) {
    return $this->treeNode
      ->child($key, FALSE)
      ->setMultiPlugin($plugin)
      ->offset();
  }

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   * @param \crumbs_MonoPlugin|\crumbs_MonoPlugin_FindParentInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   * @throws \Exception
   */
  function monoPlugin($key, \crumbs_MonoPlugin_FindParentInterface $plugin) {
    return $this->treeNode
      ->child($key, TRUE)
      ->setMonoPlugin($plugin)
      ->offset();
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function parentCallback($key, $callback) {
    $plugin = new \crumbs_MonoPlugin_ParentPathCallback($callback);
    return $this->treeNode
      ->child($key, TRUE)
      ->setMonoPlugin($plugin)
      ->offset();
  }

}
