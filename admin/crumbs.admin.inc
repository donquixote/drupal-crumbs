<?php

/**
 * The form displayed on admin/structure/crumbs(/weights)
 */
function crumbs_admin_weights_form($form, $form_state, $type = 'tabledrag') {

  $form = array();

  $form['crumbs_weights'] = array(
    '#title' => t('Weights'),
    // Fetching the default value is not automated by system_settings_form().
    '#default_value' => variable_get('crumbs_weights', array()),
  );

  switch ($type) {
    case 'textual':
      // You need to enable crumbs_labs to get this.
      $form['crumbs_weights']['#type'] = 'crumbs_weights_textual';
      break;
    case 'tabledrag':
    default:
      $form['crumbs_weights']['#type'] = 'crumbs_weights_tabledrag';
  }

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
