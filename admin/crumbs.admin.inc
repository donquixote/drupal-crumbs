<?php


function crumbs_admin_form() {
  $form = array();

  // drupal_add_js(drupal_get_path('module', 'crumbs') .'/crumbs.admin.js');
  // drupal_add_css(drupal_get_path('module', 'crumbs') .'/crumbs.admin.css');

  module_load_include('tabledrag.inc', 'crumbs', 'admin/crumbs');
  $form['crumbs_weights'] = _crumbs_weights_tabledrag();

  $form['crumbs_show_current_page'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show the current page at the end of the breadcrumb trail.'),
    '#default_value' => variable_get('crumbs_show_current_page', FALSE),
  );

  return system_settings_form($form);
}


function _crumbs_load_available_keys(array $plugins) {
  // we can't use the plugin engine,
  // or else we would miss disabled plugins.
  $plugin_operation = new crumbs_PluginOperation_describe();
  foreach ($plugins as $plugin_key => $plugin) {
    $plugin_operation->invoke($plugin, $plugin_key);
  }
  return array($plugin_operation->getKeys(), $plugin_operation->getKeysByPlugin());
}
