<?php

namespace Drupal\crumbs_ui\FormElement;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\TreePosition\TreePosition;
use Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface;

class WeightsCheckboxTree implements ElementInterface, PreRenderInterface {

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
   * Callback for $element['#value_callback'].
   *
   * @param array $element
   * @param array|FALSE|NULL $input
   * @param array $form_state
   *
   * @return mixed
   * @throws \Exception
   *
   * @see _crumbs_ui_element_value_callback()
   */
  public function value_callback(array $element, $input, array $form_state) {

    if ($input === FALSE) {
      return isset($element['#default_value'])
        ? $element['#default_value']
        : array();
    }

    if (!is_array($input)) {
      $input = array();
    }

    return $this->extractValue($input);
  }

  /**
   * @param array $input
   *
   * @return array
   */
  private function extractValue(array $input) {
    $rootPosition = new TreePosition($this->tree);
    return array(
      'statuses' => $this->collectStatuses($rootPosition, $input, TRUE),
      'weights' => $this->collectWeights($rootPosition, $input),
    );
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $treePosition
   * @param array $input
   *   The complete input array.
   * @param bool $default_status
   *   The status that applies to $parent_key, if it is not overridden. This is
   *   either the status inherited from the parent, or a default status defined
   *   for this key via hook_crumbs_plugins().
   *
   * @return bool[]
   */
  protected function collectStatuses(TreePositionInterface $treePosition, array $input, $default_status) {
    $statuses = array();
    $key = $treePosition->getKey();

    // Collect the status from the root key of this (sub)tree.
    $status = !empty($input[$key]['status']);
    if ($status !== $default_status) {
      $statuses[$key] = $status;
    }

    /** @var TreePositionInterface $childTreePosition */
    foreach ($treePosition->getChildren() as $childTreePosition) {
      $child_default_status = $childTreePosition->requireTreeNode()->getStatus();
      if (!isset($child_default_status)) {
        $child_default_status = $status;
      }
      $statuses += $this->collectStatuses($childTreePosition, $input, $child_default_status);
    }

    return $statuses;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $treePosition
   * @param array $input
   *
   * @return mixed[]
   */
  protected function collectWeights(TreePositionInterface $treePosition, array $input) {
    $weights_all = array();
    foreach ($treePosition->getChildren() as $childTreeNode) {
      $childKey = $childTreeNode->getKey();
      if (!empty($input[$childKey]['distinct_weight'])) {
        if (isset($input[$childKey]['weight'])) {
          $child_weight = $input[$childKey]['weight'];
          if ((string)(int)$child_weight === (string)$child_weight) {
            $weights_all[$childKey] = (int)$child_weight;
          }
        }
      }
      $weights_all += $this->collectWeights($childTreeNode, $input);
    }
    return $weights_all;
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
    $rootPosition = new TreePosition($this->tree);
    $element += $this->buildTree($value_all, $rootPosition, TRUE, 0);
    return $element;
  }

  /**
   * Recursively builds form elements for a plugin key and its children.
   *
   * @param array $value_all
   *   All statuses and weights currently stored in the database.
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $treePosition
   * @param bool $inherited_status
   *   The status that applies to $parent_key, if it is not overridden. This is
   *   either the status inherited from the parent, or a default status defined
   *   for this key via hook_crumbs_plugins().
   * @param int $inherited_weight
   *   The weight that applies to $parent_key, if it is not overridden. This is
   *   either the weight inherited from the parent, or a default weight defined
   *   for this key via hook_crumbs_plugins().
   *
   * @return array
   *   An array of form elements.
   */
  protected function buildTree(array $value_all, TreePositionInterface $treePosition, $inherited_status, $inherited_weight) {

    $key = $treePosition->getKey();

    if (isset($value_all['statuses'][$key])) {
      $status = $value_all['statuses'][$key];
    }
    else {
      $status = $treePosition->requireTreeNode()->getStatus();
      if (!isset($status)) {
        $status = $inherited_status;
      }
    }

    $distinct_weight = isset($value_all['weights'][$key]);

    $weight = isset($value_all['weights'][$key])
      ? $value_all['weights'][$key]
      : $inherited_weight;

    $elements = array();

    $elements[$key] = $this->buildChildElement($key, $status, $distinct_weight, $weight);

    foreach ($treePosition->getChildren() as $childTreePosition) {
      $elements += $this->buildTree($value_all, $childTreePosition, $status, $weight);
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
