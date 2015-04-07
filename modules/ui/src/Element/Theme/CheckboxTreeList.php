<?php

namespace Drupal\crumbs_ui\Element\Theme;

use Drupal\crumbs_ui\PluginKey\RawHierarchyInterface;

class CheckboxTreeList implements ElementThemeInterface {

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
    return $this->buildTreeHtml($element, '*', NULL);
  }

  /**
   * @param array $element
   * @param string $row_key
   * @param string|null $parent_key
   *
   * @return string
   */
  protected function buildTreeHtml($element, $row_key, $parent_key) {
    $html = '<div>' . $this->buildTreeRow($element[$row_key], $row_key, $parent_key) . '</div>';
    $list_html = '';
    foreach ($this->rawHierarchy->keyGetChildren($row_key) as $child_key) {
      $list_html .= '<li>' . $this->buildTreeHtml($element, $child_key, $row_key) . '</li>';
    }
    if (!empty($list_html)) {
      $html .= '<ul>' . $list_html . '</ul>';
    }
    return $html;
  }

  /**
   * @param array $row_elements
   * @param string $row_key
   * @param string|NULL $parent_key
   *
   * @return string
   */
  protected function buildTreeRow($row_elements, $row_key, $parent_key) {
    # dpm($row_elements, $row_key);
    $html = '';
    $row_elements['status']['#title'] = $row_key;
    $row_elements['status']['#crumbs_ui_container_attributes']['class'][] = 'container-inline';
    $row_elements['status']['#theme_wrappers'] = array('crumbs_ui_form_element');
    $html .= render($row_elements['status']);
    $html .= render($row_elements['distinct_weight']);
    unset($row_elements['weight']['#title']);
    $html .= render($row_elements['weight']);
    return $html;
  }
}
