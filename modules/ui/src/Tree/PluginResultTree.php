<?php

namespace Drupal\crumbs_ui\Tree;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;

class PluginResultTree {

  /**
   * @var string
   */
  private $description;

  /**
   * @var bool
   */
  private $weight;

  /**
   * @var bool
   */
  private $status;

  /**
   * @var static[]
   */
  private $children = array();

  /**
   * @var string|null
   */
  private $candidate;

  /**
   * @var bool
   */
  private $isLeaf;

  /**
   * @param \Drupal\crumbs_ui\Tree\PluginResultTree $parent
   * @param string $key
   * @param bool $isLeaf
   *
   * @return static
   */
  static function createFromParent(PluginResultTree $parent, $key, $isLeaf) {
    $status = $parent->getStatus();
    $weight = $parent->getWeight();
    $tree = new static($status, $weight, $isLeaf);
    $tree->description = $key;
    return $tree;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $original
   * @param \Drupal\crumbs_ui\Tree\PluginResultTree $parent
   * @param string $key
   *
   * @return static
   */
  static function createFromOriginalAndParent(TreeNode $original, PluginResultTree $parent, $key) {
    $status = $original->getStatus();
    $weight = $original->getWeight();
    if (!isset($status)) {
      $status = $parent->getStatus();
    }
    if (!isset($weight)) {
      $weight = $parent->getWeight();
    }
    $tree = new static($status, $weight, $original->isLeaf());
    $tree->description = $original->getDescription();
    if (!isset($tree->description)) {
      $tree->description = $key;
    }
    return $tree;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $original
   * @param array $routerItem
   *
   * @return \Drupal\crumbs_ui\Tree\PluginResultTree
   */
  static function createRoot(TreeNode $original, array $routerItem) {
    $status = $original->getStatus();
    $weight = $original->getWeight();
    $tree = new static($status, $weight, FALSE);
    $tree->description = $original->getDescription();
    $tree->prepare($original, $routerItem);
    $tree->description = '*';
    return $tree;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $original
   * @param array $routerItem
   */
  function prepare(TreeNode $original, array $routerItem) {
    if (!$original->isPluginNode()) {
      // This is a plugin family, so there can be plugins further down in the tree.
      $children = array();
      foreach ($original->getChildren() as $key => $child_original) {
        $child = static::createFromOriginalAndParent($child_original, $this, $key);
        $child->prepare($child_original, $routerItem);
        $children[$key] = $child;
      }
      $this->setChildren($children);
    }
    $plugin = $original->routeGetPlugin($routerItem['route']);
    if (empty($plugin)) {
      // None of the plugins registered here applies for the given route.
      return;
    }
    $path = $routerItem['link_path'];
    if ($plugin instanceof \crumbs_MultiPlugin) {
      if ($plugin instanceof \crumbs_MultiPlugin_FindParentInterface) {
        $candidates = $plugin->findParent($path, $routerItem);
      }
      elseif ($plugin instanceof \crumbs_MultiPlugin_FindTitleInterface) {
        $candidates = $plugin->findTitle($path, $routerItem);
      }
      if (!empty($candidates)) {
        $this->candidatesSetChildren($original->getChildren(), $candidates);
      }
    }
    elseif ($plugin instanceof \crumbs_MonoPlugin) {
      if ($plugin instanceof \crumbs_MonoPlugin_FindParentInterface) {
        $this->candidate = $plugin->findParent($path, $routerItem);
      }
      elseif ($plugin instanceof \crumbs_MonoPlugin_FindTitleInterface) {
        $this->candidate = $plugin->findTitle($path, $routerItem);
      }
      else {
        $this->candidate = NULL;
      }
    }
    else {
      // Weird case. No children. No candidates.
    }
  }

  /**
   * @param bool $status
   * @param int $weight
   * @param bool $isLeaf
   */
  function __construct($status, $weight, $isLeaf) {
    $this->status = isset($status) ? $status : TRUE;
    $this->weight = isset($weight) ? $weight : 0;
    $this->isLeaf = $isLeaf;
  }

  /**
   * @param \Drupal\crumbs_ui\Tree\PluginResultTree $parent
   */
  function inherit(PluginResultTree $parent) {
    if (!isset($this->weight)) {
      $this->weight = $parent->weight;
    }
    if (!isset($this->status)) {
      $this->status = $parent->status;
    }
  }

  /**
   * @param \Drupal\crumbs_ui\Tree\PluginResultTree[] $children
   */
  function setChildren(array $children) {
    $this->children = $children;
    foreach ($children as $key => $child) {
      $child->inherit($this);
    }
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode[] $original_children
   * @param string[] $candidates
   *
   * @return static[]
   */
  protected function candidatesSetChildren(array $original_children, array $candidates) {
    $groups = array();
    $children = array();
    foreach ($candidates as $key => $candidate) {
      if (FALSE !== $pos = strpos('.', $key)) {
        $prefix = substr($key, 0, $pos);
        $suffix = substr($key, $pos + 1);
        $groups[$prefix][$suffix] = $candidate;
      }
      else {
        if (isset($original_children[$key])) {
          $tree_child = static::createFromOriginalAndParent($original_children[$key], $this, $key);
        }
        else {
          $tree_child = static::createFromParent($this, $key, TRUE);
        }
        $tree_child->candidate = $candidate;
        $children[$key] = $tree_child;
      }
    }
    foreach ($groups as $prefix => $group) {
      if (isset($original_children[$prefix])) {
        $original_child = $original_children[$prefix];
        $tree_child = static::createFromOriginalAndParent($original_child, $this, $prefix);
        $tree_child->candidatesSetChildren($original_child->getChildren(), $group);
      }
      else {
        $tree_child = static::createFromParent($this, $prefix, FALSE);
        $tree_child->candidatesSetChildren(array(), $group);
      }
      $children[$prefix] = $tree_child;
    }
    $this->setChildren($children);
  }

  /**
   * @return static[]
   */
  function getChildren() {
    return $this->children;
  }

  /**
   * @return bool
   */
  function getWeight() {
    return $this->weight;
  }

  /**
   * @return bool
   */
  function getStatus() {
    return $this->status;
  }

  /**
   * @return string
   */
  function getDescription() {
    return $this->description;
  }

  /**
   * @return null|string
   */
  public function getCandidate() {
    return $this->candidate;
  }

  /**
   * @return bool
   */
  public function isLeaf() {
    return $this->isLeaf;
  }
}
