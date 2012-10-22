<?php


/**
 * Implementation of hook_crumbs_plugins()
 */
function menu_crumbs_plugins($api) {
  $api->multiPlugin('hierarchy');
  $api->multiPlugin('link_title');
}


class menu_CrumbsMultiPlugin_hierarchy implements crumbs_MultiPlugin {

  function describe($api) {
    return menu_get_menus();
  }

  function disabledByDefault() {
    return array('*');
  }

  function findParent($path, $item) {

    $q = db_select('menu_links', 'parent');
    $q->innerJoin('menu_links', 'child', 'parent.mlid = child.plid');
    $q->addExpression('parent.link_path', 'parent_path');
    $q->addExpression('parent.menu_name', 'menu_name');
    $q->condition('child.link_path', $path);

    // Top-level links have higher priority.
    $q->orderBy('parent.depth', 'ASC');

    // Ignore placeholder or separator menu items added by special_menu_items.
    $q->condition('parent.link_path', array('<nolink>', '<separator>'), 'NOT IN');

    // Collect candidates for the parent path, keyed by menu name.
    $candidates = array();
    foreach ($q->execute() as $row) {
      if (!isset($candidates[$row->menu_name])) {
        $candidates[$row->menu_name] = $row->parent_path;
      }
    }
    return $candidates;
  }
}


class menu_CrumbsMultiPlugin_link_title implements crumbs_MultiPlugin {

  function describe($api) {
    return menu_get_menus();
  }

  function disabledByDefault() {
    return array('*');
  }

  /**
   * Find all menu links with $path as the link path.
   * For each menu, find the one with the lowest depth.
   */
  function findTitle($path, $item) {

    $q = db_select('menu_links', 'ml');
    $q->fields('ml', array('link_title', 'menu_name'));
    $q->condition('link_path', $path);
    $q->orderBy('ml.depth', 'ASC');

    $titles = array();
    foreach ($q->execute() as $row) {
      if (!isset($titles[$row->menu_name])) {
        $titles[$row->menu_name] = $row->link_title;
      }
    }
    return $titles;
  }
}







