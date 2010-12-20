<?php
// $Id$


/**
 * Implementation of hook_crumbs_plugins()
 */
function menu_crumbs_plugins() {
  return array(
    'hierarchy'  => new _menu_CrumbsPlugin__hierarchy(),
    'link_title' => new _menu_CrumbsPlugin__link_title(),
    'admin'      => new _menu_CrumbsPlugin__admin(),
  );
}


class _menu_CrumbsPlugin__hierarchy {

  function define($h) {
    $q = db_query($sql = "SELECT menu_name, title FROM {menu_custom}");
    $titles = array();
    while ($row = db_fetch_object($q)) {
      $h->addRule($row->menu_name, $row->title);
    }
  }

  function findParent($path, $item) {
    $q = db_query($sql = "
      SELECT
        parent.link_path parent_path,
        parent.menu_name menu_name
      FROM
        {menu_links} parent
        INNER JOIN {menu_links} child ON (parent.mlid = child.plid)
      WHERE child.link_path = '%s'
      GROUP BY menu_name
    ", $path);
    $candidates = array();
    while ($row = db_fetch_object($q)) {
      $candidates[$row->menu_name] = $row->parent_path;
    }
    return $candidates;
  }
}


class _menu_CrumbsPlugin__link_title {

  function define($h) {
    $q = db_query($sql = "SELECT menu_name, title FROM {menu_custom}");
    $titles = array();
    while ($row = db_fetch_object($q)) {
      $h->addRule($row->menu_name, $row->title);
    }
  }

  function findTitle($path, $item) {
    $q = db_query($sql = "
      SELECT
        link_title,
        menu_name
      FROM
        {menu_links}
      WHERE link_path = '%s'
      GROUP BY menu_name
    ", $path);
    $titles = array();
    while ($row = db_fetch_object($q)) {
      $titles[$row->menu_name] = $row->link_title;
    }
    return $titles;
  }
}


class _menu_CrumbsPlugin__admin {

  /**
   * Router path 'admin/build/menu-customize/%'.
   */
  function findParent__admin_build_menu_customize__($path, $item) {
    return "admin/build/menu";
  }
}







