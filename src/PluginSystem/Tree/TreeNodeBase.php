<?php

namespace Drupal\crumbs\PluginSystem\Tree;

abstract class TreeNodeBase implements TreeNodeInterface {

  /**
   * @var bool|null
   */
  private $isLeaf;

  /**
   * @var TreeNode[]
   *   Format: $[$key] = $treeNode
   */
  private $children = array();

  /**
   * @param bool|null $isLeaf
   */
  public function __construct($isLeaf = NULL) {
    $this->isLeaf = $isLeaf;
  }

  /**
   * @return static
   */
  function cloneTree() {
    $clone = clone $this;
    foreach ($this->children as $key => $child) {
      $clone->children[$key] = $child->cloneTree();
    }
    return $clone;
  }

  /**
   * @param static[] $children
   *
   * @return static
   */
  function cloneWithChildren(array $children) {
    $clone = clone $this;
    $clone->children = $children;
    return $clone;
  }

  /**
   * @param bool $isLeaf
   */
  protected function setIsLeaf($isLeaf) {
    $this->isLeaf = $isLeaf;
  }

  /**
   * @param bool $else
   *
   * @return bool
   */
  public function isLeaf($else = TRUE) {
    return isset($this->isLeaf)
      ? $this->isLeaf
      // If in doubt, treat it as a leaf.
      : $else;
  }

  /**
   * @return static[]
   */
  function getChildren() {
    return $this->children;
  }

  /**
   * @param string|null $key
   * @param bool|null $isLeaf
   *
   * @return static
   * @throws \Exception
   */
  function child($key, $isLeaf = NULL) {
    if (!isset($key)) {
      return $this;
    }
    if (TRUE === $this->isLeaf) {
      dpm(ddebug_backtrace(TRUE));
      throw new \Exception('Cannot add children to a leaf node.');
    }
    $this->isLeaf = FALSE;
    if ('*' === $key) {
      return $this;
    }
    $pieces = explode('.', $key, 2);
    if (!isset($pieces[1])) {
      return $this->directChild($key, $isLeaf);
    }
    elseif ('*' === $pieces[0]) {
      dpm(ddebug_backtrace(TRUE));
      throw new \Exception('The wildcard symbol can only be at the end of a plugin key.');
    }
    elseif ('*' === $pieces[1]) {
      if ($isLeaf === TRUE) {
        dpm(ddebug_backtrace(TRUE));
        throw new \Exception('Tree node cannot be a leaf, if the key ends with ".*".');
      }
      return $this->directChild($pieces[0], FALSE);
    }
    else {
      return $this->directChild($pieces[0], FALSE)->child($pieces[1], $isLeaf);
    }
  }

  /**
   * @param string $key
   */
  function unchild($key) {
    unset($this->children[$key]);
  }

  /**
   * @param string $key
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeBase $child
   */
  protected function setChild($key, TreeNodeBase $child) {
    $this->children[$key] = $child;
  }

  /**
   * @return bool
   */
  function isEmpty() {
    return empty($this->children);
  }

  /**
   * @param string $key
   * @param bool|null $isLeaf
   *
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  private function directChild($key, $isLeaf) {
    return isset($this->children[$key])
      ? $this->children[$key]
      : $this->children[$key] = $this->createChildNode($key, $isLeaf);
  }

  /**
   * @param string $key
   * @param bool|null $isLeaf
   *
   * @return static
   */
  abstract protected function createChildNode($key, $isLeaf);

}
