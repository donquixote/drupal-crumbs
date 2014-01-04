<?php


// Menu system callbacks
// -----------------------------------------------------------------------------

/**
 * Page callback for 'crumbs/special-menu-item/%menu_link'
 *
 * @param array $menu_link
 */
function crumbs_special_menu_link_page(array $menu_link) {
  drupal_goto('<front>');
}

/**
 * Title callback for 'crumbs/special-menu-item/%menu_link'
 *
 * @param array $menu_link
 */
function crumbs_special_menu_link_title(array $menu_link) {
  return $menu_link['title'];
}


// Other callbacks
// -----------------------------------------------------------------------------

/**
 * Callback for themekey integration.
 */
function _crumbs_themekey_path2trailpaths($path) {
  $trail = crumbs_get_trail($path);
  $paths = array();
  foreach ($trail as $k => $item) {
    $paths[$item['alias']] = TRUE;
    $paths[$item['route']] = TRUE;
    $paths[$k] = TRUE;
  }
  return array_keys($paths);
}

/**
 * Submit callback registered on crumbs admin forms in addition to the usual
 * system_settings_form stuff.
 */
function _crumbs_admin_flush_cache() {

  // This will only hit the 'cache_page' and 'cache_block' cache bins.
  cache_clear_all();

  // Clear plugin info in 'cache' cache bin.
  crumbs()->pluginInfo->flushCaches();
}

/**
 * Callback to be registered with ini_set('unserialize_callback_func', *)
 *
 * @param string $class
 * @throws crumbs_UnserializeException
 */
function _crumbs_unserialize_failure($class) {
  throw new crumbs_UnserializeException();
}