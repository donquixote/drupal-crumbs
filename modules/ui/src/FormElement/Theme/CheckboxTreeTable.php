<?php

namespace Drupal\crumbs_ui\FormElement\Theme;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\TreePosition\TreePosition;
use Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface;

class CheckboxTreeTable implements ElementThemeInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Tree\TreeNode
   */
  private $tree;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $tree
   */
  function __construct(TreeNode $tree) {
    $this->tree = $tree;
  }

  /**
   * @param array $element
   *   The processed element to be rendered.
   *
   * @return string
   *   Rendered HTML.
   */
  public function theme($element) {
    $module_path = drupal_get_path('module', 'crumbs_ui');
    $rootPosition = new TreePosition($this->tree);
    $tree_table = array(
      /** @see theme_table() */
      '#theme' => 'table',
      '#header' => array(
        $element['#title'],
        t('Distinct weight'),
        t('Weight'),
      ),
      '#rows' => $this->buildTreeRows($element, $rootPosition),

      '#attributes' => array('class' => array(
        'crumbs_ui-checkboxtree',
        'crumbs_ui-treetable',
      )),
      '#attached' => array(
        'js' => array(
          $module_path . '/js/crumbs_ui.treetable.js',
          $module_path . '/js/crumbs_ui.checkboxtree.js',
        ),
        'css' => array(
          $module_path . '/css/crumbs_ui.treetable.css',
          $module_path . '/css/crumbs_ui.checkboxtree.css',
        ),
        # 'css' => array(drupal_get_path('module', 'token') . '/token.css'),
        # 'library' => array(array('token', 'treeTable')),
      ),
    );
    return render($tree_table);
  }

  /**
   * @param array $element
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $treePosition
   *
   * @return array[]
   */
  protected function buildTreeRows($element, TreePositionInterface $treePosition) {
    $row_key = $treePosition->getKey();
    $description = $treePosition->getDescription();
    $element[$row_key]['status']['#title'] = $description;
    $rows = array();
    if ($treePosition->isLeaf()) {
      $element[$row_key]['status']['#attributes']['data-crumbs_ui-tree_depth'] = $treePosition->getDepth();
      $cells = $this->buildLeafRowCells($element[$row_key]);
      $rows[$row_key . ' LEAF'] = $this->buildRow($cells, $treePosition);
    }
    else {
      // Build the parent row.
      $cells = $this->buildParentRowCells($element[$row_key], $treePosition);
      $rows[$row_key . ' PARENT'] = $this->buildRow($cells, $treePosition);

      // Recursively build the child rows.
      foreach ($treePosition->getChildren() as $childPosition) {
        $rows += $this->buildTreeRows($element, $childPosition);
      }

      // Build the "other" row, which is added alongside the children.
      $element[$row_key]['status']['#attributes']['data-crumbs_ui-tree_depth'] = $treePosition->getDepth() + 1;
      $cells = $this->buildOtherRowCells($element[$row_key], $treePosition);
      $rows[$row_key . ' OTHER'] = $this->buildRow($cells, $treePosition, TRUE);
    }
    return $rows;
  }

  /**
   * @param string[] $cells
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $treePosition
   * @param bool $is_other
   *
   * @return array
   * @throws \Exception
   */
  protected function buildRow($cells, TreePositionInterface $treePosition, $is_other = FALSE) {
    $row = array(
      'id' => sha1($treePosition->getKey()),
      'data' => $cells,
      'no_striping' => TRUE,
    );
    $depth = $treePosition->getDepth();
    if ($is_other) {
      ++$depth;
    }
    $row['class'][] = ($depth === 1) ? 'even' : 'odd';
    $row['data-crumbs_ui-tree_depth'] = $depth;
    if (!$is_other && !$treePosition->isLeaf()) {
      $row['data-crumbs_ui-is_parent'] = TRUE;
    }
    if ($parentPosition = $treePosition->getParent()) {
      $row['parent'] = sha1($parentPosition->getKey());
    }
    return $row;
  }

  /**
   * @param array $row_elements
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $treePosition
   *
   * @return array
   */
  protected function buildParentRowCells(array $row_elements, TreePositionInterface $treePosition) {
    $name = 'crumbs_ui-checkboxtree-' . $treePosition->getKey();
    $attributes = array(
      'type' => 'checkbox',
      'class' => array('crumbs_ui-checkboxtree_item'),
      'data-crumbs_ui-tree_depth' => $treePosition->getDepth(),
      'data-crumbs_ui-is_parent' => TRUE,
      'name' => $name,
      'id' => $name,
    );
    $input = '<input ' . drupal_attributes($attributes) . '/>';
    $cells = array();
    $description = $treePosition->getDescription();
    if (!$treePosition->hasChildren()) {
      $description = '<em>' . $description . '</em>';
    }
    $cells[] = $input . ' <label class="option" for="' . check_plain($name) . '">' . $description . '</label>';

    $row_elements['distinct_weight']['#theme_wrappers'] = array('crumbs_ui_inline_element');
    unset($row_elements['weight']['#title']);
    $row_elements['weight']['#theme_wrappers'] = array('crumbs_ui_inline_element');
    $cells[] = render($row_elements['distinct_weight']);

    $cells[] = render($row_elements['weight']);

    return $cells;
  }

  /**
   * @param array $row_elements
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $treePosition
   *
   * @return array
   */
  protected function buildOtherRowCells($row_elements, TreePositionInterface $treePosition) {
    # dpm($row_elements, $row_key);
    $cells = array();
    $row_elements['status']['#attributes']['class'][] = 'crumbs_ui-checkboxtree_item';
    # $row_elements['status']['#title'] = '<em>' . t('other') . '</em>';
    $row_elements['status']['#title'] = '<em>' . $treePosition->getKey() . '</em>';
    # $row_elements['status']['#crumbs_ui_container_attributes']['class'][] = 'container-inline';

    // @see theme_crumbs_ui_inline_element()
    $row_elements['status']['#theme_wrappers'] = array('crumbs_ui_inline_element');
    $row_elements['#attributes']['class'][] = 'crumbs_ui-checkboxtree_item';
    $cells[] = render($row_elements['status']);

    $cells[] = '';
    $cells[] = '';

    return $cells;
  }

  /**
   * @param array $row_elements
   *
   * @return array
   */
  protected function buildLeafRowCells($row_elements) {
    # dpm($row_elements, $row_key);
    $cells = array();
    $row_elements['status']['#attributes']['class'][] = 'crumbs_ui-checkboxtree_item';
    # $row_elements['status']['#crumbs_ui_container_attributes']['class'][] = 'container-inline';

    // @see theme_crumbs_ui_inline_element()
    $row_elements['status']['#theme_wrappers'] = array('crumbs_ui_inline_element');
    $row_elements['#attributes']['class'][] = 'crumbs_ui-checkboxtree_item';
    $cells[] = render($row_elements['status']);

    $row_elements['distinct_weight']['#theme_wrappers'] = array('crumbs_ui_inline_element');
    unset($row_elements['weight']['#title']);
    $row_elements['weight']['#theme_wrappers'] = array('crumbs_ui_inline_element');
    $cells[] = render($row_elements['distinct_weight']);

    $cells[] = render($row_elements['weight']);

    return $cells;
  }
}
