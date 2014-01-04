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
  $base = 'admin/structure/crumbs';
  $items = array();
  $items["$base"] = array(
    'title' => 'Crumbs',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('crumbs_admin_weights_form'),
    'access arguments' => array('administer crumbs'),
    'file' => 'admin/crumbs.admin.inc',
  );
  $items["$base/weights"] = array(
    'title' => 'Plugin weights',
    'type' => MENU_DEFAULT_LOCAL_TASK,
    'weight' => -10,
  );
  $items["$base/display"] = array(
    'title' => 'Display',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('crumbs_admin_display_form'),
    'access arguments' => array('administer crumbs'),
    'file' => 'admin/crumbs.admin.inc',
    'type' => MENU_LOCAL_TASK,
    'weight' => 0,
  );
  $items["$base/entity-parent"] = array(
    'title' => 'Entity parent',
    'weight' => 10,
    'type' => MENU_LOCAL_TASK,
  );
  $items["$base/debug"] = array(
    'title' => 'Debug',
    'page callback' => 'crumbs_debug_page',
    'access arguments' => array('administer crumbs'),
    'file' => 'admin/crumbs.debug.inc',
    'weight' => 20,
    'type' => MENU_LOCAL_TASK,
  );

  foreach (array(
    'node' => 'Node',
    'taxonomy_term' => module_exists('taxonomy') ? 'Taxonomy' : FALSE,
    'user' => 'User',
  ) as $entity_type => $title) {
    if (FALSE !== $title) {
      $entity_slug = str_replace('_', '-', $entity_type);
      $items["$base/entity-parent/$entity_slug"] = array(
        'title' => $title,
        'page callback' => 'drupal_get_form',
        'page arguments' => array('crumbs_admin_entity_parent_form', $entity_type),
        'access arguments' => array('administer crumbs'),
        'file' => 'admin/crumbs.entity_parent.inc',
        'type' => MENU_LOCAL_TASK,
      );
    }
  }

  $items["$base/entity-parent"] += $items["$base/entity-parent/node"];

  $items["$base/entity-parent/node"] = array(
    'title' => 'Node',
    'weight' => -10,
    'type' => MENU_DEFAULT_LOCAL_TASK,
  );

  if (module_exists('menu')) {
    $items['crumbs/special-menu-item/%menu_link'] = array(
      'access callback' => TRUE,
      'page callback' => 'crumbs_special_menu_link_page',
      'page arguments' => array(2),
      'title callback' => 'crumbs_special_menu_link_title',
      'title arguments' => array(2),
      'type' => MENU_CALLBACK,
    );
  }

  return $items;
}
