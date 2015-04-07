<?php

namespace Drupal\crumbs_ui\Element;

use Drupal\crumbs_ui\PluginKey\RawHierarchyInterface;

class WeightsCheckboxTree implements ElementInterface, PreRenderInterface {

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
   * Callback for $element['#value_callback'].
   *
   * @param array $element
   * @param array|FALSE $input
   * @param array $form_state
   *
   * @return mixed
   *
   * @see _crumbs_ui_element_value_callback()
   */
  public function value_callback(array $element, $input, array $form_state) {
    if ($input === FALSE) {
      return isset($element['#default_value']) ? $element['#default_value'] : array();
    }
    else {
      $toplevel_weight = empty($input['*']['distinct_weight'])
        ? 0
        : $input['*']['weight'];
      $toplevel_value = empty($input['*']['status'])
        ? FALSE
        : $toplevel_weight;
      return $this->collectValues('*', $input, $toplevel_value, $toplevel_weight);
    }
  }

  /**
   * @param string $parent_key
   * @param array $input
   * @param false|int $parent_value
   * @param int $parent_weight
   *
   * @return mixed[]
   */
  protected function collectValues($parent_key, array $input, $parent_value, $parent_weight) {
    $values_all = array();
    foreach ($this->rawHierarchy->keyGetChildren($parent_key) as $child_key) {
      if (!isset($input[$child_key])) {
        throw new \RuntimeException("Missing key in input.");
      }
      $child_weight = empty($input[$child_key]['distinct_weight'])
        ? $parent_weight
        : $input[$child_key]['weight'];
      $child_value = empty($input[$child_key]['status'])
        ? FALSE
        : $child_weight;
      if ($child_value !== $parent_value) {
        $values_all[$child_key] = $child_value;
      }
      $values_all += $this->collectValues($child_key, $input, $child_value, $child_weight);
    }
    return $values_all;
  }

  /**
   * Callback for $element['#process']
   *
   * Creates one checkbox (and more?) for each plugin key.
   * Later the theme function will arrange these in an <ul> list.
   * Client-side js will turn it into a js tree with tri-state checkboxes.
   *
   * @param array $element
   *   The original form element, where the weights are all in one array.
   * @param array $form_state
   *   Form state array that is passed around from the form.
   *
   * @return array
   *   The modified form elements array, where the original element is split up
   *   into checkboxes.
   *
   * @see _crumbs_ui_element_process()
   */
  public function process($element, $form_state) {
    $value_all = $element['#value'];
    $element += $this->buildTree($value_all, '*', 0, 0);
    return $element;
  }

  /**
   * @param array $value_all
   * @param string $parent_key
   * @param int|FALSE $default_value
   * @param int $default_weight
   *
   * @return array
   */
  protected function buildTree(array $value_all, $parent_key, $default_value, $default_weight) {
    $parent_value = isset($value_all[$parent_key])
      ? $value_all[$parent_key]
      : $default_value;
    $parent_status = is_numeric($parent_value);
    $parent_weight = $parent_status
      ? $parent_value
      : $default_value;

    $elements = array();
    $elements[$parent_key] = $this->buildChildElement($parent_key, $parent_status, $parent_weight !== $default_weight, $parent_weight);
    foreach ($this->rawHierarchy->keyGetChildren($parent_key) as $child_key) {
      $elements += $this->buildTree($value_all, $child_key, $parent_value, $parent_weight);
    }
    return $elements;
  }

  /**
   * @param string $plugin_key
   * @param bool $status
   * @param bool $distinct_weight
   * @param int $weight
   *
   * @return array
   */
  protected function buildChildElement($plugin_key, $status, $distinct_weight, $weight) {
    $elements = array(
      '#type' => 'fieldset',
      '#title' => $plugin_key,
      '#tree' => TRUE,
    );
    $elements['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Status'),
      '#default_value' => $status,
    );
    $elements['distinct_weight'] = array(
      '#type' => 'checkbox',
      '#title' => t('Distinct weight'),
      '#default_value' => $distinct_weight,
    );
    $elements['weight'] = array(
      '#type' => 'textfield',
      '#title' => t('Weight'),
      '#size' => 3,
      '#default_value' => $weight,
    );
    return $elements;
  }

  /**
   * @param array $element
   *
   * @return array
   */
  public function pre_render($element) {
    foreach (element_children($element) as $plugin_key) {
      $name = $element[$plugin_key]['distinct_weight']['#name'];
      $element[$plugin_key]['weight']['#states'] = array(
        'enabled' => array(
          // @todo Sanitize the name.
          ':input[name="' . $name . '"]' => array('checked' => TRUE),
        ),
      );
    }
    return $element;
  }
}
