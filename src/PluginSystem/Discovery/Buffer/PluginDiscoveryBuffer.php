<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Buffer;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\Tree\TreeUtil;
use Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery;
use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;
use Drupal\crumbs\PluginSystem\PluginType\TitlePluginType;

class PluginDiscoveryBuffer {

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode|null
   */
  protected $findParentTreeNode;

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode|null
   */
  protected $findTitleTreeNode;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery
   */
  protected $pluginDiscovery;

  /**
   * @return static
   */
  static function create() {
    $discovery = new PluginDiscovery();
    return new static($discovery);
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery $pluginDiscovery
   */
  function __construct(PluginDiscovery $pluginDiscovery) {
    $this->pluginDiscovery = $pluginDiscovery;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  function getParentTree() {
    $this->load();
    return $this->findParentTreeNode;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  function getTitleTree() {
    $this->load();
    return $this->findTitleTreeNode;
  }

  /**
   * Initiates plugin discovery, if it has not run before.
   */
  protected function load() {
    if (isset($this->findParentTreeNode)) {
      return;
    }

    $this->findParentTreeNode = TreeNode::root(new ParentPluginType());
    $this->findTitleTreeNode = TreeNode::root(new TitlePluginType());
    $this->pluginDiscovery->discoverPlugins(
      $this->findParentTreeNode,
      $this->findTitleTreeNode);
  }

  /**
   * Resets discovered plugins, so that they need to be discovered again.
   */
  function reset() {
    $this->findParentTreeNode = NULL;
    $this->findTitleTreeNode = NULL;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  function getTree(PluginTypeInterface $pluginType) {
    $this->load();
    if ($pluginType instanceof ParentPluginType) {
      return $this->findParentTreeNode;
    }
    elseif ($pluginType instanceof TitlePluginType) {
      return $this->findTitleTreeNode;
    }

    throw new \InvalidArgumentException("Invalid plugin type.");
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  function getQualifiedTree(PluginTypeInterface $pluginType) {
    $tree = $this->getTree($pluginType);
    $settings = variable_get($pluginType->getSettingsKey(), array()) + array(
      'statuses' => array(),
      'weights' => array(),
    );
    $statuses = TreeUtil::spliceCandidates($settings['statuses']);
    $weights = TreeUtil::spliceCandidates($settings['weights']);
    $tree->setStatuses($statuses);
    $tree->setWeights($weights);
    return $tree;
  }

}
