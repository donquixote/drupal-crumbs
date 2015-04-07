<?php

namespace Drupal\crumbs_ui\Element\Theme;

use Drupal\crumbs_ui\PluginKey\RawHierarchyInterface;

class CheckboxTreeTable implements ElementThemeInterface {

  /**
   * @var \Drupal\crumbs_ui\PluginKey\RawHierarchyInterface
   */
  private $rawHierarchy;

  /**
   * @var \crumbs_Container_MultiWildcardData
   */
  private $meta;

  /**
   * @param \Drupal\crumbs_ui\PluginKey\RawHierarchyInterface $raw_hierarchy
   * @param \crumbs_Container_MultiWildcardData $meta
   */
  function __construct(RawHierarchyInterface $raw_hierarchy, \crumbs_Container_MultiWildcardData $meta) {
    $this->meta = $meta;
    $this->rawHierarchy = $raw_hierarchy;
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
    $tree_table = array(
      /** @see theme_table() */
      '#theme' => 'table',
      '#header' => array(
        t('Plugin keys'),
        t('Distinct weight'),
        t('Weight'),
      ),
      '#rows' => $this->buildTreeRows($element, '*', NULL, 0),

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
   * @param string $row_key
   * @param string|null $parent_key
   * @param int $tree_depth
   *
   * @return \array[]
   */
  protected function buildTreeRows($element, $row_key, $parent_key, $tree_depth) {
    $rows = array();
    if (!$this->rawHierarchy->keyIsWildcard($row_key)) {
      $cells = $this->buildLeafRowCells($element[$row_key], $row_key);
      $rows[$row_key . ' LEAF'] = $this->buildRow($cells, NULL, $parent_key, $tree_depth);
    }
    else {
      // Build the parent row.
      $cells = $this->buildParentRowCells($row_key, $tree_depth);
      $rows[$row_key . ' PARENT'] = $this->buildRow($cells, $row_key, $parent_key, $tree_depth);

      // Recursively build the child rows.
      foreach ($this->rawHierarchy->keyGetChildren($row_key) as $child_key) {
        $rows += $this->buildTreeRows($element, $child_key, $row_key, $tree_depth + 1);
      }

      // Build the "other" row, which is added alongside the children.
      $cells = $this->buildOtherRowCells($element[$row_key], $row_key);
      $rows[$row_key . ' OTHER'] = $this->buildRow($cells, NULL, $row_key, $tree_depth + 1);
    }
    return $rows;
  }

  /**
   * @param $cells
   * @param $row_key
   * @param $parent_key
   * @param $tree_depth
   *
   * @return array
   */
  protected function buildRow($cells, $row_key, $parent_key, $tree_depth) {
    $row = array(
      'id' => sha1($row_key),
      'data' => $cells,
      'no_striping' => TRUE,
    );
    if (isset($tree_depth)) {
      $row['data-crumbs_ui-tree_depth'] = $tree_depth;
    }
    if (isset($parent_key)) {
      $row['parent'] = sha1($parent_key);
    }
    return $row;
  }

  /**
   * @param $row_key
   * @param $tree_depth
   *
   * @return array
   */
  protected function buildParentRowCells($row_key, $tree_depth) {
    $cells = array();
    $cells[] = '<input type="checkbox" class="crumbs_ui-checkboxtree_item" data-crumbs_ui-tree_depth="' . $tree_depth . '"/> ' . $row_key;
    $cells[] = '';
    $cells[] = '';
    return $cells;
  }

  /**
   * @param $row_elements
   * @param $row_key
   *
   * @return array
   */
  protected function buildOtherRowCells($row_elements, $row_key) {
    # dpm($row_elements, $row_key);
    $cells = array();
    $row_elements['status']['#attributes']['class'][] = 'crumbs_ui-checkboxtree_item';
    $row_elements['status']['#title'] = t('Other');
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

  /**
   * @param array $row_elements
   * @param string|NULL $row_key
   *
   * @return array
   */
  protected function buildLeafRowCells($row_elements, $row_key) {
    # dpm($row_elements, $row_key);
    $cells = array();
    $row_elements['status']['#attributes']['class'][] = 'crumbs_ui-checkboxtree_item';
    $row_elements['status']['#title'] = $row_key;
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
