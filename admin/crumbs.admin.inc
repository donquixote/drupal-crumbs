<?php

/**
 * The form displayed on admin/structure/crumbs(/weights)
 */
function crumbs_admin_weights_form($form, $form_state, $type = 'tabledrag') {

  $form = array();

  $info = crumbs('pluginInfo');

  // Re-discover plugins, when the admin visits the weights configuration form.
  $info->flushCaches();

  $form['crumbs_weights'] = array(
    '#title' => t('Weights'),
    // Fetching the default value is not automated by system_settings_form().
    '#default_value' => variable_get('crumbs_weights', array()),
    '#crumbs_plugin_info' => $info,
  );

  switch ($type) {
    case 'textual':
      // You need to enable crumbs_labs to get this.
      $form['crumbs_weights']['#type'] = 'crumbs_weights_textual';
      break;
    case 'expansible':
      // You need to enable crumbs_labs to get this.
      $form['crumbs_weights']['#type'] = 'crumbs_weights_expansible';
      break;
    case 'tabledrag':
    default:
      $form['crumbs_weights']['#type'] = 'crumbs_weights_tabledrag';
  }

  $form = system_settings_form($form);
  $form['#submit'][] = '_crumbs_admin_flush_cache';
  return $form;
}

/**
 * The form displayed on admin/structure/crumbs/display
 */
function crumbs_admin_display_form() {
  $form = array();

  // TODO:
  //   Add options to
  //   - hide the "Home" link
  //   - hide the breadcrumb if it only contains the "Home" link.

  $form['crumbs_show_front_page'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the home page link (recommended).'),
    '#default_value' => variable_get('crumbs_show_front_page', TRUE),
  );

  $form['crumbs_show_current_page'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the current page at the end of the breadcrumb trail.'),
    '#default_value' => variable_get('crumbs_show_current_page', FALSE),
  );

  $home = t('Home');
  $current = t('Current page');
  $intermediate = t('Intermediate page');
  $form['crumbs_minimum_trail_items'] = array(
    '#type' => 'radios',
    '#title' => t('Shortest visible breadcrumb'),
    '#description' => t('If the trail has fewer items than specified here, the breadcrumb will be hidden.'),
    '#default_value' => variable_get('crumbs_minimum_trail_items', 2),
    '#options' => array(
      1 => "($home)",
      2 => "(<a href='#'>$home</a>) &raquo; ($current)",
      3 => "(<a href='#'>$home</a>) &raquo; <a href='#'>$intermediate</a> &raquo; ($current)",
    ),
  );

  $form = system_settings_form($form);
  $form['#submit'][] = '_crumbs_admin_flush_cache';
  return $form;
}
