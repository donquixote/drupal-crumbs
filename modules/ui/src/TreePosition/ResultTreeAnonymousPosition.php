<?php

namespace Drupal\crumbs_ui\TreePosition;

use Drupal\crumbs\PluginSystem\TreePosition\TreePositionBase;

class ResultTreeAnonymousPosition extends TreePositionBase implements ResultTreePositionInterface {

  /**
   * @var mixed[]|string|null
   */
  private $results;

  /**
   * @param mixed[]|string|null $results
   */
  function __construct($results) {
    parent::__construct(NULL, NULL);
    $this->results = $results;
  }

  /**
   * @return static[]
   */
  function getChildren() {
    if (empty($this->results) || !is_array($this->results)) {
      return array();
    }
    $positions = array();
    foreach ($this->results as $key => $results) {
      $position = new ResultTreeAnonymousPosition($results);
      $position->setParent($this, $key, !is_array($results));
      $positions[$key] = $position;
    }
    return $positions;
  }

  /**
   * @return bool
   */
  function hasChildren() {
    return !empty($this->results) && is_array($this->results);
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode|null
   */
  function getTreeNode() {
    return NULL;
  }

  /**
   * @throws \Exception
   */
  function requireTreeNode() {
    throw new \Exception("This position does not have a tree node.");
  }

  /**
   * @return string|null
   */
  function getCandidate() {
    return !is_array($this->results) ? $this->results : NULL;
  }

  /**
   * @return bool
   */
  function isLeaf() {
    return !is_array($this->results);
  }
}
