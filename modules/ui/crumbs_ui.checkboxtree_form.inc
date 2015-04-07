<?php
use Drupal\crumbs_ui\Element\Theme\CheckboxTreeList;
use Drupal\crumbs_ui\Element\Theme\CheckboxTreeTable;
use Drupal\crumbs_ui\Element\WeightsCheckboxTree;

/**
 * @param array $form
 * @param array $form_state
 * @param string $plugin_type
 *   One of 'parent-finding' and 'title-finding'.
 *
 * @return array
 *   The form array.
 */
function _crumbs_ui_checkboxtree_form($form, $form_state, $plugin_type) {

  $form = array();

  $info = crumbs()->pluginInfo;

  // Re-discover plugins, when the admin visits the weights configuration form.
  $info->flushCaches();

  $raw_hierarchy = \Drupal\crumbs_ui\PluginKey\RawHierarchy::createFromKeysMeta($info->availableKeysMeta);

  $form['crumbs_weights'] = array(
    '#title' => t('Weights'),
    '#type' => 'crumbs_ui_element',
    '#crumbs_ui_element_object' => new WeightsCheckboxTree($raw_hierarchy, $info->availableKeysMeta),
    '#crumbs_ui_theme_object' => new CheckboxTreeTable($raw_hierarchy, $info->availableKeysMeta),
    // Fetching the default value is not automated by system_settings_form().
    '#default_value' => $info->userWeights,
    '#crumbs_plugin_info' => $info,
  );

  $form = system_settings_form($form);
  $form['#submit'][] = '_crumbs_admin_flush_cache';
  return $form;
}

