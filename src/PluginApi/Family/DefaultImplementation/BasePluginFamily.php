<?php

namespace Drupal\crumbs\PluginApi\Family\DefaultImplementation;


use Drupal\crumbs\PluginApi\Family\BaseFamilyInterface;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\Plugin\ParentPluginInterface;
use Drupal\crumbs\PluginSystem\Plugin\TitlePluginInterface;

class BasePluginFamily implements BaseFamilyInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  private $findParentTreeNode;

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  private $findTitleTreeNode;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findParentTreeNode
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findTitleTreeNode
   */
  function __construct(TreeNode $findParentTreeNode, TreeNode $findTitleTreeNode) {
    $this->findParentTreeNode = $findParentTreeNode;
    $this->findTitleTreeNode = $findTitleTreeNode;
    $this->validate();
  }

  /**
   * @throws \Exception
   *
   * @return $this
   */
  function validate() {
    $parentPlugin = new \crumbs_MonoPlugin_ParentPathCallback(function(){});
    $titlePlugin = new \crumbs_MonoPlugin_TitleCallback(function(){});
    $this->findParentTreeNode->validateMonoPlugin($parentPlugin);
    $this->findTitleTreeNode->validateMonoPlugin($titlePlugin);
    return $this;
  }

  protected function getFindParentTreeNode() {
    return $this->findParentTreeNode;
  }

  protected function getFindTitleTreeNode() {
    return $this->findTitleTreeNode;
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @return string
   *   The plugin type, either 'parent' or 'title'.
   */
  protected function pluginGetType(\crumbs_PluginInterface $plugin) {
    if ($plugin instanceof ParentPluginInterface) {
      return 'parent';
    }
    elseif ($plugin instanceof TitlePluginInterface) {
      return 'title';
    }
    else {
      throw new \InvalidArgumentException("Invalid plugin type.");
    }
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  protected function pluginGetTreeNode(\crumbs_PluginInterface $plugin) {
    if ($plugin instanceof ParentPluginInterface) {
      $this->findParentTreeNode->validatePlugin($plugin);
      return $this->findParentTreeNode;
    }
    elseif ($plugin instanceof TitlePluginInterface) {
      $this->findTitleTreeNode->validatePlugin($plugin);
      return $this->findTitleTreeNode;
    }
    elseif ('object' === $type = gettype($plugin)) {
      $class = get_class($plugin);
      throw new \InvalidArgumentException("Invalid plugin type: $class");
    }
    else {
      throw new \InvalidArgumentException("Invalid plugin type: $type.");
    }
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key, \crumbs_MultiPlugin $plugin) {
    return $this->pluginGetTreeNode($plugin)
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
   * @param \crumbs_MonoPlugin $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function monoPlugin($key, \crumbs_MonoPlugin $plugin) {
    return $this->pluginGetTreeNode($plugin)
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
    return $this->findParentTreeNode
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
  function titleCallback($key, $callback) {
    $plugin = new \crumbs_MonoPlugin_TitleCallback($callback);
    return $this->findTitleTreeNode
      ->child($key, TRUE)
      ->setMonoPlugin($plugin)
      ->offset();
  }
}
