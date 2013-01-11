<?php


function _crumbs_weights_tabledrag() {

  list($plugins, $disabled_keys) = crumbs_get_plugins();
  list($available_keys, $keys_by_plugin) = _crumbs_load_available_keys($plugins);

  $element = array(
    '#theme' => 'crumbs_weights_tabledrag',
  );

  foreach ($available_keys as $key => $description) {
    $element[$key] = array(
      '#type' => 'textfield',
      '#title' => $key,
      '#size' => 10,
    );
    if (is_string($description)) {
      $element[$key]['#description'] = $description;
    }
  }

  foreach ($disabled_keys as $key => $disabled) {
    if (isset($element[$key])) {
      $element[$key]['#_crumbs_default_weight'] = FALSE;
    }
  }

  return $element;
}


function theme_crumbs_weights_tabledrag(&$vars) {

  $element = $vars['element'];

  $sections = array(
    'enabled' => array(),
    'disabled' => array(),
    'disabled_by_default' => array(),
    'inherit' => array(),
  );

  $sort = array();
  foreach (element_children($element) as $key) {
    $weight = @$element['#default_value'];
    if (is_numeric($weight)) {
      $sort[$key] = $weight;
      $sections['enabled'][$key] = TRUE;
    }
    elseif ($weight === FALSE) {
      $sections['disabled'][$key] = TRUE;
    }
    elseif ($key === '*') {
      $sort[$key] = 0;
      $sections['enabled'][$key] = TRUE;
    }
    elseif (@$element['#_crumbs_default_weight'] === FALSE) {
      $sections['disabled_by_default'][$key] = TRUE;
    }
    else {
      $sections['inherit'][$key] = TRUE;
    }
  }

  dpm($sort);
  dpm($sections);
  array_multisort($sort, $sections['enabled']);
  dpm($sections['enabled']);

  return __FUNCTION__;
}
