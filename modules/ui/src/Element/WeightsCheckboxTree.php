<?php

namespace Drupal\crumbs_ui\Element;

use Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection;
use Drupal\crumbs_ui\PluginKey\RawHierarchyInterface;

class WeightsCheckboxTree implements ElementInterface, PreRenderInterface {

  /**
   * @var \Drupal\crumbs_ui\PluginKey\RawHierarchyInterface
   */
  private $rawHierarchy;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection
   */
  private $pluginCollection;

  /**
   * @param \Drupal\crumbs_ui\PluginKey\RawHierarchyInterface $raw_hierarchy
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection $pluginCollection
   */
  function __construct(RawHierarchyInterface $raw_hierarchy, LabeledPluginCollection $pluginCollection) {
    $this->rawHierarchy = $raw_hierarchy;
    $this->pluginCollection = $pluginCollection;
  }

  /**
   * Callback for $element['#value_callback'].
   *
   * @param array $element
   * @param array|FALSE $input
   * @param array $form_state
   *
   * @return mixed
   * @throws \Exception
   *
   * @see _crumbs_ui_element_value_callback()
   */
  public function value_callback(array $element, $input, array $form_state) {
    if ($input === FALSE) {
      return isset($element['#default_value']) ? $element['#default_value'] : array();
    }
    elseif (is_array($input)) {
      $toplevel_weight = empty($input['*']['distinct_weight'])
        ? 0
        : $input['*']['weight'];
      $toplevel_status = empty($input['*']['status'])
        ? FALSE
        : TRUE;
      return array(
        'statuses' => $this->collectStatuses('*', $input, $toplevel_status),
        'weights' => $this->collectWeights('*', $input, $toplevel_weight),
      );
    }
    elseif (!isset($input)) {
      throw new \Exception("Unexpected input NULL.");
    }
    else {
      throw new \Exception("Unexpected input.");
    }
  }

  /**
   * @param string $parent_key
   * @param array $input
   * @param bool $parent_status
   *
   * @return bool[]
   */
  protected function collectStatuses($parent_key, array $input, $parent_status) {
    $statuses_all = array();
    foreach ($this->rawHierarchy->keyGetChildren($parent_key) as $child_key) {
      if (!isset($input[$child_key])) {
        throw new \RuntimeException("Missing key in input.");
      }
      $child_status = !empty($input[$child_key]['status']) ? TRUE : FALSE;
      if ($child_status !== $parent_status) {
        $statuses_all[$child_key] = $child_status;
      }
      $statuses_all += $this->collectStatuses($child_key, $input, $child_status);
    }
    return $statuses_all;
  }

  /**
   * @param string $parent_key
   * @param array $input
   *
   * @return mixed[]
   */
  protected function collectWeights($parent_key, array $input) {
    $weights_all = array();
    foreach ($this->rawHierarchy->keyGetChildren($parent_key) as $child_key) {
      if (!isset($input[$child_key])) {
        throw new \RuntimeException("Missing key in input.");
      }
      if (!empty($input[$child_key]['distinct_weight'])) {
        if (isset($input[$child_key]['weight'])) {
          $child_weight = $input[$child_key]['weight'];
          if ((string)(int)$child_weight === (string)$child_weight) {
            $weights_all[$child_key] = (int)$child_weight;
          }
        }
      }
      $weights_all += $this->collectWeights($child_key, $input);
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
    $element += $this->buildTree($value_all, '*', TRUE, 0);
    return $element;
  }

  /**
   * @param array $value_all
   * @param string $parent_key
   * @param bool $default_status
   * @param int $default_weight
   *
   * @return array
   */
  protected function buildTree(array $value_all, $parent_key, $default_status, $default_weight) {
    $parent_status = isset($value_all['statuses'][$parent_key])
      ? $value_all['statuses'][$parent_key]
      : $default_status;
    $distinct_weight = isset($value_all['weights'][$parent_key]);
    $parent_weight = isset($value_all['weights'][$parent_key])
      ? $value_all['weights'][$parent_key]
      : $default_weight;

    $elements = array();
    $elements[$parent_key] = $this->buildChildElement($parent_key, $parent_status, $distinct_weight, $parent_weight);
    foreach ($this->rawHierarchy->keyGetChildren($parent_key) as $child_key) {
      $elements += $this->buildTree($value_all, $child_key, $parent_status, $parent_weight);
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
        'invisible' => array(
          // @todo Sanitize the name.
          ':input[name="' . $name . '"]' => array('checked' => TRUE),
        ),
      );
    }
    return $element;
  }
}
