<?php

/**
 * Implements hook_permission().
 */
function crumbs_permission() {
  return array(
    'administer crumbs' => array(
      'title' => t('Administer Crumbs'),
    ),
  );
}

/**
 * Implements hook_menu().
 */
function crumbs_menu() {
  $items = array();

  if (!module_exists('crumbs_ui')) {
    $items['admin/structure/crumbs'] = array(
      'title' => 'Crumbs',
      'page callback' => '_crumbs_ui_not_installed_warning_page',
      'access arguments' => array('administer crumbs'),
    );
  }

  if (module_exists('menu')) {
    $items['crumbs/special-menu-item/%menu_link'] = array(
      'access callback' => TRUE,
      'page callback' => '_crumbs_special_menu_link_page',
      'page arguments' => array(2),
      'title callback' => '_crumbs_special_menu_link_title',
      'title arguments' => array(2),
      'type' => MENU_CALLBACK,
    );
  }

  return $items;
}

/**
 * Implements hook_theme().
 */
function crumbs_theme() {
  return array(
    'crumbs_breadcrumb_link' => array(
      'item' => NULL,
      'file' => 'crumbs.theme.inc',
    ),
    'crumbs_breadcrumb_current_page' => array(
      'variables' => array('item' => NULL, 'show_current_page' => TRUE),
      'file' => 'crumbs.theme.inc',
    ),
  );
}

/**
 * Implements hook_theme_registry_alter()
 *
 * @param array $registry
 */
function crumbs_theme_registry_alter(array &$registry) {
  if (!isset($registry['breadcrumb']['function'])) {
    return;
  }

  // Figure out which theme this applies to.
  if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
    $trace = debug_backtrace(0, 3);
  }
  else {
    // Second parameter not supported in PHP < 5.4.0. It would cause a
    // "Warning: debug_backtrace() expects at most 1 parameter, 2 given".
    $trace = debug_backtrace(0);
  }
  if (1
    && isset($trace[2]['function'])
    && '_theme_build_registry' === $trace[2]['function']
    && isset($trace[2]['args'][0])
  ) {
    // Get the first argument of _theme_build_registry().
    $theme = $trace[2]['args'][0];
  }
  else {
    // The hook is called from a weird place.
    return;
  }

  // Remember the original theme function for the settings page.
  $f = $registry['breadcrumb']['function'];
  $originals = variable_get('crumbs_original_theme_breadcrumb', array());
  $originals[$theme->name] = $f;
  variable_set('crumbs_original_theme_breadcrumb', $originals);

  $settings = variable_get('crumbs_override_theme_breadcrumb', array('theme_breadcrumb'));
  if (in_array($f, $settings, TRUE)) {
    $registry['breadcrumb']['includes'][] = drupal_get_path('module', 'crumbs') . '/crumbs.theme.inc';
    $registry['breadcrumb']['function'] = 'crumbs_theme_breadcrumb';
  }
}

/**
 * Implements hook_themekey_properties().
 */
function crumbs_themekey_properties() {
  $attributes = array();
  $attributes['crumbs:trail_paths'] = array(
    'description' => t('Crumbs trail paths'),
    'validator' => '',
    'page cache' => THEMEKEY_PAGECACHE_SUPPORTED,
  );

  $maps = array();
  $maps[] = array(
    'src' => 'drupal:get_q',
    'dst' => 'crumbs:trail_paths',
    'callback' => '_crumbs_themekey_path2trailpaths',
  );

  return array('attributes' => $attributes, 'maps' => $maps);
}
