<?php

namespace Drupal\crumbs_ui\Element\Theme;

use Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection;
use Drupal\crumbs_ui\PluginKey\RawHierarchyInterface;

class CheckboxTreeTable implements ElementThemeInterface {

  /**
   * @var \Drupal\crumbs_ui\PluginKey\RawHierarchyInterface
   */
  private $rawHierarchy;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection
   */
  private $pluginCollection;

  /**
   * @var string[]
   */
  private $descriptions;

  /**
   * @param \Drupal\crumbs_ui\PluginKey\RawHierarchyInterface $raw_hierarchy
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection $pluginCollection
   */
  function __construct(RawHierarchyInterface $raw_hierarchy, LabeledPluginCollection $pluginCollection) {
    $this->rawHierarchy = $raw_hierarchy;
    $this->pluginCollection = $pluginCollection;
    $this->descriptions = $pluginCollection->getDescriptions();
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
        $element['#title'],
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
   * @return array[]
   */
  protected function buildTreeRows($element, $row_key, $parent_key, $tree_depth) {
    $description = isset($this->descriptions[$row_key])
      ? $this->descriptions[$row_key]
      : $row_key;
    $element[$row_key]['status']['#title'] = $description;
    $rows = array();
    if (!$this->rawHierarchy->keyIsWildcard($row_key)) {
      $element[$row_key]['status']['#attributes']['data-crumbs_ui-tree_depth'] = $tree_depth;
      $cells = $this->buildLeafRowCells($element[$row_key], $row_key);
      $rows[$row_key . ' LEAF'] = $this->buildRow($cells, NULL, $parent_key, $tree_depth, FALSE);
    }
    else {
      // Build the parent row.
      $cells = $this->buildParentRowCells($element[$row_key], $row_key, $description, $tree_depth);
      $rows[$row_key . ' PARENT'] = $this->buildRow($cells, $row_key, $parent_key, $tree_depth, TRUE);

      // Recursively build the child rows.
      foreach ($this->rawHierarchy->keyGetChildren($row_key) as $child_key) {
        $rows += $this->buildTreeRows($element, $child_key, $row_key, $tree_depth + 1);
      }

      // Build the "other" row, which is added alongside the children.
      $element[$row_key]['status']['#attributes']['data-crumbs_ui-tree_depth'] = $tree_depth + 1;
      $cells = $this->buildOtherRowCells($element[$row_key]);
      $rows[$row_key . ' OTHER'] = $this->buildRow($cells, NULL, $row_key, $tree_depth + 1, FALSE);
    }
    return $rows;
  }

  /**
   * @param string[] $cells
   * @param string $row_key
   * @param string $parent_key
   * @param int $tree_depth
   * @param bool $is_parent
   *
   * @return array
   */
  protected function buildRow($cells, $row_key, $parent_key, $tree_depth, $is_parent) {
    $row = array(
      'id' => sha1($row_key),
      'data' => $cells,
      'no_striping' => TRUE,
    );
    $row['class'][] = ($tree_depth === 1) ? 'even' : 'odd';
    $row['data-crumbs_ui-tree_depth'] = $tree_depth;
    if ($is_parent) {
      $row['data-crumbs_ui-is_parent'] = TRUE;
    }
    if (isset($parent_key)) {
      $row['parent'] = sha1($parent_key);
    }
    return $row;
  }

  /**
   * @param array $row_elements
   * @param string $row_key
   * @param string $description
   * @param int $tree_depth
   *
   * @return array
   */
  protected function buildParentRowCells(array $row_elements, $row_key, $description, $tree_depth) {
    $name = 'crumbs_ui-checkboxtree-' . $row_key;
    $attributes = array(
      'type' => 'checkbox',
      'class' => array('crumbs_ui-checkboxtree_item'),
      'data-crumbs_ui-tree_depth' => $tree_depth,
      'data-crumbs_ui-is_parent' => TRUE,
      'name' => $name,
    );
    $input = '<input ' . drupal_attributes($attributes) . '/>';
    $cells = array();
    if (!$this->rawHierarchy->keyHasSolidChildren($row_key)) {
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
   * @param $row_elements
   *
   * @return array
   */
  protected function buildOtherRowCells($row_elements) {
    # dpm($row_elements, $row_key);
    $cells = array();
    $row_elements['status']['#attributes']['class'][] = 'crumbs_ui-checkboxtree_item';
    $row_elements['status']['#title'] = '<em>' . t('other') . '</em>';
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
   * @param string|NULL $row_key
   *
   * @return array
   */
  protected function buildLeafRowCells($row_elements, $row_key) {
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
