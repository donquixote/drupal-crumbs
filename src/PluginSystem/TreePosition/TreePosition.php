<?php

namespace Drupal\crumbs\PluginSystem\TreePosition;


use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface;

class TreePosition extends TreePositionBase implements TreePositionInterface {

  /**
   * @var TreeNodeInterface
   */
  private $treeNode;

  /**
   * Protected constructor.
   *
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $treeNode
   *
   * @see TreePosition::root()
   * @see TreePosition::getChildren()
   */
  function __construct(TreeNodeInterface $treeNode) {
    parent::__construct($treeNode->getStatus(), $treeNode->getWeight());
    $this->treeNode = $treeNode;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface|null
   */
  function getTreeNode() {
    if (!is_object($this->treeNode) || !$this->treeNode instanceof TreeNodeInterface) {
      return NULL;
    }
    return $this->treeNode;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface
   * @throws \Exception
   */
  function requireTreeNode() {
    if (!is_object($this->treeNode) || !$this->treeNode instanceof TreeNodeInterface) {
      throw new \Exception("The tree does not support getTreeNode().");
    }
    return $this->treeNode;
  }

  /**
   * @return static[]
   */
  function getChildren() {
    $children = array();
    foreach ($this->treeNode->getChildren() as $suffix => $childTreeNode) {
      $position = new static($childTreeNode);
      $position->setParent($this, $suffix, $childTreeNode->isLeaf());
      $children[$suffix] = $position;
    }
    return $children;
  }

  /**
   * @return bool
   */
  public function hasChildren() {
    return !empty($this->treeNode->getChildren());
  }

  /**
   * @return null|string
   * @throws \Exception
   */
  public function getDescription() {
    if ($this->treeNode instanceof TreeNode) {
      $description = $this->treeNode->getDescription();
      if (isset($description)) {
        return $description;
      }
    }
    return parent::getDescription();
  }

  /**
   * @return bool
   */
  public function isLeaf() {
    return $this->treeNode->isLeaf();
  }

}
