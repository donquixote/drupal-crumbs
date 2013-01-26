<?php

/**
 * The form displayed on admin/structure/crumbs(/weights)
 */
function crumbs_admin_weights_form() {
  $form = array();

  $form['crumbs_weights'] = array(
    '#title' => t('Weights'),
    '#type' => 'crumbs_weights_tabledrag',
    // Fetching the default value is not automated by system_settings_form().
    '#default_value' => variable_get('crumbs_weights', array()),
  );

  return system_settings_form($form);
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

  $form['crumbs_show_current_page'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the current page at the end of the breadcrumb trail.'),
    '#default_value' => variable_get('crumbs_show_current_page', FALSE),
  );

  return system_settings_form($form);
}
