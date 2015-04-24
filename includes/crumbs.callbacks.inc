<?php


// Menu system callbacks
// -----------------------------------------------------------------------------
use Drupal\crumbs\Exception\UnserializeException;

/**
 * Page callback for 'crumbs/special-menu-item/%menu_link'
 *
 * @param array $menu_link
 */
function _crumbs_special_menu_link_page(array $menu_link) {
  drupal_goto('<front>');
}

/**
 * Title callback for 'crumbs/special-menu-item/%menu_link'
 *
 * @param array $menu_link
 */
function _crumbs_special_menu_link_title(array $menu_link) {
  return $menu_link['title'];
}

/**
 * Page callback for admin/structure/crumbs.
 *
 * @return string
 */
function _crumbs_ui_not_installed_warning_page() {
  if (!module_exists('crumbs_ui')) {
    return t('The Crumbs UI (crumbs_ui) module is currently not installed.');
  }
  else {
    menu_rebuild();
    return t('The Crumbs UI (crumbs_ui) module is installed, but the menu had to be rebuilt. Please refresh the page.');
  }
}

/**
 * Access callback that checks more than one permission with AND.
 *
 * Used for admin/structure/crumbs/debug/devel
 *
 * @param array $permissions
 * @param object|null $account
 *
 * @return bool
 */
function _crumbs_user_access_and(array $permissions, $account = NULL) {
  foreach ($permissions as $permission) {
    if (is_string($permission)) {
      if (!user_access($permission, $account)) {
        return FALSE;
      }
    }
    elseif (is_array($permission)) {
      if (!_crumbs_user_access_or($permission, $account)) {
        return FALSE;
      }
    }
  }
  return TRUE;
}

/**
 * Access callback that checks more than one permission with OR.
 *
 * @param string[] $permissions
 * @param object|null $account
 *
 * @return bool
 */
function _crumbs_user_access_or(array $permissions, $account = NULL) {
  foreach ($permissions as $permission) {
    if (is_string($permission)) {
      if (user_access($permission, $account)) {
        return TRUE;
      }
    }
    elseif (is_array($permission)) {
      if (_crumbs_user_access_and($permission, $account)) {
        return TRUE;
      }
    }
  }
  return FALSE;
}


// Other callbacks
// -----------------------------------------------------------------------------

/**
 * Callback for themekey integration.
 *
 * @param string $path
 *
 * @return array
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
  cache_clear_all('crumbs:', 'cache', TRUE);
}

/**
 * Callback to be registered with ini_set('unserialize_callback_func', *)
 *
 * @param string $class
 *
*@throws UnserializeException
 */
function _crumbs_unserialize_failure($class) {
  throw new UnserializeException();
}
