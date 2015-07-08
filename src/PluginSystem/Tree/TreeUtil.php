<?php

namespace Drupal\crumbs\PluginSystem\Tree;

use Drupal\crumbs\PluginSystem\TreePosition\TreePosition;
use Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface;

class TreeUtil {

  /**
   * @param string[] $candidates
   *
   * @return array
   */
  static function spliceCandidates(array $candidates) {
    $groups = array();
    foreach ($candidates as $key => $candidate) {
      if (FALSE !== $pos = strpos($key, '.')) {
        unset($candidates[$key]);
        $prefix = substr($key, 0, $pos);
        $suffix = substr($key, $pos + 1);
        $groups[$prefix][$suffix] = $candidate;
      }
    }
    foreach ($groups as $prefix => $group) {
      $candidates[$prefix] = static::spliceCandidates($group);
    }
    return $candidates;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $tree
   *
   * @return bool[]
   */
  static function treeCollectStatuses(TreeNodeInterface $tree) {
    $position = new TreePosition($tree);
    $statuses = array();
    static::doCollectStatuses($statuses, $position);
    return $statuses;
  }

  /**
   * @param bool[] $statuses
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $position
   */
  static function doCollectStatuses(array &$statuses, TreePositionInterface $position) {
    $status = $position->requireTreeNode()->getStatus();
    if (isset($status)) {
      $statuses[$position->getKey()] = $status;
    }
    foreach ($position->getChildren() as $childPosition) {
      static::doCollectStatuses($statuses, $childPosition);
    }
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $tree
   *
   * @return int[]
   */
  static function treeCollectWeights(TreeNodeInterface $tree) {
    $position = new TreePosition($tree);
    $weights = array();
    static::doCollectWeights($weights, $position);
    return $weights;
  }

  /**
   * @param int[] $weights
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $position
   */
  static function doCollectWeights(array &$weights, TreePositionInterface $position) {
    $weight = $position->requireTreeNode()->getWeight();
    $weights[$position->getKey()] = $weight;
    foreach ($position->getChildren() as $childPosition) {
      static::doCollectWeights($weights, $childPosition);
    }
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNodeInterface $tree
   *
   * @return \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface[]
   */
  static function treeCollectPluginPositions(TreeNodeInterface $tree) {
    $position = new TreePosition($tree);
    $positions = array();
    static::doCollectPluginPositions($positions, $position);
    return $positions;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface[] $positions
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $position
   */
  static function doCollectPluginPositions(&$positions, TreePositionInterface $position) {
    if ($position->isPluginPosition()) {
      $positions[] = $position;
    }
    else {
      foreach ($position->getChildren() as $childPosition) {
        static::doCollectPluginPositions($positions, $childPosition);
      }
    }
  }

}
