<?php

class crumbs_Admin_ElementObject_WeightsTabledrag extends crumbs_Admin_ElementObject_WeightsAbstract {

  /**
   * Callback for $element['#value_callback']
   */
  function value_callback(&$element, $input = FALSE, $form_state = array()) {

    if ($input === FALSE) {
      return isset($element['#default_value']) ? $element['#default_value'] : array();
    }
    else {
      $weights = array();
      $i = 0;
      foreach ($input as $row_key => $row_values) {
        if (substr($row_key, 0, 9) === 'sections.') {
          $section_key = substr($row_key, 9);
          if ($section_key === 'auto') {
            break;
          }
        }
        elseif (substr($row_key, 0, 6) === 'rules.') {
          $key = substr($row_key, 6);
          if ($section_key === 'enabled') {
            $weights[$key] = ++$i;
          }
          elseif ($section_key === 'disabled') {
            $weights[$key] = FALSE;
          }
        }
      }
      return $weights;
    }
  }

  /**
   * Callback for $element['#process']
   * Create one textfield element per rule.
   */
  function process($element, $form_state) {

    // Apologies for the stupid identifiers.
    $info = $element['#crumbs_plugin_info'];
    $admin_info = $info->adminPluginInfo;
    $available_keys = $admin_info->collectedInfo();

    // Set up sections
    foreach (array(
      'enabled' => t('Enabled'),
      'disabled' => t('Disabled'),
      'auto' => t('Inherit / automatic'),
    ) as $section_key => $section_title) {
      $element["sections.$section_key"] = array(
        '#tree' => TRUE,
        '#title' => $section_title,
        'weight' => array(
          '#type' => 'hidden',
          '#default_value' => 'section',
        ),
        '#section_key' => $section_key,
      );
    }

    // Set up tabledrag rows
    foreach ($available_keys as $key => $meta) {
      $child = array(
        '#title' => $key,
        'weight' => array(
          '#type' => 'textfield',
          '#size' => 10,
          '#default_value' => -1,
          '#class' => array('crumbs-weight-element'),
        ),
        '#section_key' => 'auto',
        '#crumbs_rule_info' => $meta,
      );
      $element["rules.$key"] = $child;
    }

    if (is_array($element['#value'])) {
      foreach ($element['#value'] as $key => $value) {
        if (isset($element["rules.$key"])) {
          $child = &$element["rules.$key"];
          if (FALSE === $value) {
            $child['#section_key'] = 'disabled';
          }
          elseif (is_numeric($value)) {
            $child['weight']['#default_value'] = $value;
            $child['#section_key'] = 'enabled';
          }
        }
      }
    }

    return $element;
  }
}
