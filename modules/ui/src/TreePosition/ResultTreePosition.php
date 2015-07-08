<?php

namespace Drupal\crumbs_ui\TreePosition;

use Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface;
use Drupal\crumbs\PluginSystem\TreePosition\TreePosition;

class ResultTreePosition extends TreePosition implements ResultTreePositionInterface {

  /**
   * @var mixed[]|string|null
   */
  private $results;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $treeNode
   * @param mixed[]|string|null $results
   */
  function __construct(TreeNodeInterface $treeNode, $results) {
    parent::__construct($treeNode);
    $this->results = $results;
  }

  /**
   * @return ResultTreePositionInterface[]
   */
  function getChildren() {
    if (empty($this->results) || !is_array($this->results)) {
      return array();
    }
    $subtrees = $this->requireTreeNode()->getChildren();
    $positions = array();
    foreach ($this->results as $key => $results) {
      if (isset($subtrees[$key])) {
        $subtree = $subtrees[$key];
        $position = new static($subtree, $results);
        $position->setParent($this, $key, $subtree->isLeaf());
      }
      else {
        $position = new ResultTreeAnonymousPosition($results);
        $position->setParent($this, $key, !is_array($results));
      }
      $positions[$key] = $position;
    }
    return $positions;
  }

  /**
   * @return string|null
   */
  function getCandidate() {
    return !is_array($this->results) ? $this->results : NULL;
  }
}
