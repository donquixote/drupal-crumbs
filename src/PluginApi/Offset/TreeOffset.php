<?php

namespace Drupal\crumbs\PluginApi\Offset;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;

class TreeOffset implements TreeOffsetMetaInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  protected $treeNode;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $treeNode
   */
  function __construct(TreeNode $treeNode) {
    $this->treeNode = $treeNode;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description) {
    $this->treeNode->describe($description);
    return $this;
  }

  /**
   * @param string $description
   * @param string[] $args
   *
   * @return $this
   */
  function translateDescription($description, $args = array()) {
    $this->treeNode->translateDescription($description, $args);
    return $this;
  }

  /**
   * @return $this
   */
  function disabledByDefault() {
    $this->treeNode->disabledByDefault();
    return $this;
  }
}
