<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\Tree\TreeUtil;
use Drupal\crumbs_ui\TreePosition\ResultTreePosition;
use Drupal\crumbs_ui\TreePosition\ResultTreePositionInterface;

class ParentPluginDemo implements WidgetInterface {

  /**
   * @var array
   */
  private $routerItem;

  /**
   * @param array $routerItem
   */
  function __construct(array $routerItem) {
    $this->routerItem = $routerItem;
  }

  /**
   * @return array
   *   A render array.
   */
  function build() {
    # $resultTree = PluginResultTree::createRoot($this->getTree(), $this->routerItem);
    # $rows = $this->buildTableRows($resultTree, '', 0);
    $tree = $this->getTree();
    $results = $this->collectResults($tree);
    $position = new ResultTreePosition($tree, $results);
    $best_position = $this->findBestCandidatePosition($position);
    $best_key = isset($best_position)
      ? $best_position->getKey()
      : '';
    $rows = $this->buildTableRows($position, '', $best_key);
    return array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => array(
        t('Plugin key'),
        t('Candidate'),
        t('Weight'),
        # t('Index'),
        # t('Accepted?'),
      ),
    );
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  protected function getTree() {
    return crumbs()->qualifiedParentTree;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $tree
   *
   * @return null|string|string[]
   */
  private function collectResults(TreeNode $tree) {

    if (!$tree->isPluginNode()) {
      $results = array();
      foreach ($tree->getChildren() as $key => $subtree) {
        $subtree_results = $this->collectResults($subtree);
        if (!empty($subtree_results)) {
          $results[$key] = $subtree_results;
        }
      }
      return $results;
    }

    $plugin = $tree->routeGetPlugin($this->routerItem['route']);
    if (!isset($plugin)) {
      return NULL;
    }

    return $this->pluginCollectResults($plugin, $this->routerItem);
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   * @param array $routerItem
   *
   * @return array|null|string
   */
  protected function pluginCollectResults(\crumbs_PluginInterface $plugin, array $routerItem) {

    $path = $routerItem['link_path'];
    if ($plugin instanceof \crumbs_MultiPlugin_FindParentInterface) {
      $candidates = $plugin->findParent($path, $routerItem);
      if (is_array($candidates) && !empty($candidates)) {
        return TreeUtil::spliceCandidates($candidates);
      }
      return NULL;
    }
    elseif ($plugin instanceof \crumbs_MonoPlugin_FindParentInterface) {
      return $plugin->findParent($path, $routerItem);
    }
    else {
      return NULL;
    }
  }

  /**
   * @param \Drupal\crumbs_ui\TreePosition\ResultTreePositionInterface $position
   * @param string $indent
   * @param string $bestResultKey
   *
   * @return array[]
   */
  private function buildTableRows(ResultTreePositionInterface $position, $indent, $bestResultKey) {
    $cells = array(
      $indent . $position->getDescription(),
      $position->getCandidate() ?: '',
      $position->getStatus()
        ? $position->getWeight()
        : 'disabled',
    );
    if ($bestResultKey === $position->getKey()) {
      foreach ($cells as $iCell => $cell) {
        $cells[$iCell] = '<strong>' . $cell . '</strong>';
      }
    }
    $rows = array(
      array(
        'no_striping' => TRUE,
        'class' => array(
          ($position->getDepth() === 1) ? 'even' : 'odd',
        ),
        'data' => $cells,
      ),
    );
    $child_indent = $indent . '&nbsp; &nbsp; &nbsp; ';
    foreach ($position->getChildren() as $key => $subtree) {
      $child_rows = $this->buildTableRows($subtree, $child_indent, $bestResultKey);
      $rows = array_merge($rows, $child_rows);
    }
    return $rows;
  }

  /**
   * Finds the tree position with the candidate with the best weight.
   *
   * @param \Drupal\crumbs_ui\TreePosition\ResultTreePositionInterface $position
   *
   * @return \Drupal\crumbs_ui\TreePosition\ResultTreePositionInterface|null
   */
  private function findBestCandidatePosition(ResultTreePositionInterface $position) {
    if ($position->isLeaf()) {
      if (!$position->getStatus()) {
        return NULL;
      }
      if (!$position->getCandidate()) {
        return NULL;
      }
      return $position;
    }
    /** @var ResultTreePositionInterface|null $best */
    $best = NULL;
    foreach ($position->getChildren() as $key => $childPosition) {
      $childBestPosition = $this->findBestCandidatePosition($childPosition);
      if (!isset($childBestPosition)) {
        continue;
      }
      if (!isset($best)
        || $best->getWeight() > $childBestPosition->getWeight()
      ) {
        $best = $childBestPosition;
      }
    }
    return $best;
  }

}
