<?php

/**
 * Implements hook_element_info()
 *
 * @see _crumbs_element_object()
 */
function crumbs_element_info() {
  $types = array();
  // Nice documentation to be found in
  // http://api.drupal.org/api/drupal/includes%21form.inc/function/form_builder/7
  $common = array(
    '#input' => TRUE,
    /** @see _crumbs_element_value_callback() */
    '#value_callback' => '_crumbs_element_value_callback',
    /** @see _crumbs_element_process() */
    '#process' => array('_crumbs_element_process'),
    /** @see _crumbs_element_after_build() */
    '#after_build' => array('_crumbs_element_after_build'),
    /** @see _crumbs_element_validate() */
    '#element_validate' => array('_crumbs_element_validate'),
    /** @see _crumbs_element_pre_render() */
    '#pre_render' => array('_crumbs_element_pre_render'),
    '#tree' => TRUE,
  );
  $types['crumbs_weights_tabledrag'] = array(
    /** @see theme_crumbs_weights_tabledrag() */
    '#theme' => 'crumbs_weights_tabledrag',
  ) + $common;
  $types['crumbs_weights_textual'] = array(
    /** @see theme_crumbs_weights_textual() */
    '#theme' => 'crumbs_weights_textual',
  ) + $common;
  $types['crumbs_weights_expansible'] = array(
    /** @see theme_crumbs_weights_expansible() */
    '#theme' => 'crumbs_weights_expansible',
  ) + $common;
  $types['crumbs_weights_checkboxtree'] = array(
    /** @see theme_crumbs_weights_checkboxtree() */
    '#theme' => 'crumbs_weights_checkboxtree',
  ) + $common;
  return $types;
}
