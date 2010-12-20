<?php
// $Id$


/**
 * Implementation of hook_crumbs_plugins()
 */
function crumbs_crumbs_plugins() {
  return array(
    'home_title' => new _crumbs_CrumbsPlugin__home_title,
    // TODO: Check if this is technically possible and reasonable.
    // 'menu_router' => new _crumbs_CrumbsPlugin__menu_router,
  );
}


class _crumbs_CrumbsPlugin__home_title {

  function define($h) {
    return $h->setTitle(t('Set "Home" as the title for the root item.'));
  }

  function findTitle($path, array $item, array $breadcrumb_parents) {
    if (!count($breadcrumb_parents)) {
      return t('Home');
    }
  }
}


/**
 * This plugin allows to put information about the crumbs parent into the
 * router item, via hook_menu() or hook_menu_alter().
 *
 * Syntax in the router item:
 *   $router_item['crumbs_parent_callback'],
 *     giving a callback which takes $path and $item as arguments, and has to
 *     return the parent path.
 *   $router_item['crumbs_parent'],
 *     with $0, $1 etc being replaced with fragments from the original path.
 *
 * TODO: Does this actually work? Will menu_rebuild store the information,
 *   if there is no respective table column?
 *   See http://drupal.org/node/1002116
 */
class _crumbs_CrumbsPlugin__menu_router {

  function define($h) {
    $h->setTitle('Follow the hint in the router item');
  }

  function findParent($path, $item) {
    if (is_string($item['crumbs_parent_callback'])) {
      $function = $item['crumbs_parent_callback'];
      if (function_exists($function)) {
        return $function($path, $item);
      }
    }
    else if (is_string($item['crumbs_parent'])) {
      $replace = array();
      foreach (explode('/', $path) as $i => $fragment) {
        $replace['$'. $i] = $fragment;
      }
      return strtr($item['crumbs_parent'], $replace);
    }
  }
}



