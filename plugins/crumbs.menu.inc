<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of menu module.
 */
class menu_class_CrumbsParentFinder {
  
  function construct() {
    return array(
      'hierarchy' => new menu_class_CrumbsParentFinder__hierarchy,
      'admin' => new menu_class_CrumbsParentFinder__admin,
      // 'dummy_node_type' => new menu_class_CrumbsParentFinder__dummy,
    );
  }
  
  function getRuleTitles() {
    $q = db_query($sql = "SELECT menu_name, title FROM {menu_custom}");
    $titles = array();
    while ($row = db_fetch_object($q)) {
      $titles[$row->menu_name] = $row->title;
    }
    return $titles;
  }
}


class menu_class_CrumbsParentFinder__admin {
  function find__admin_build_menu_customize__($path, $item) {
    return "admin/build/menu";
  }
}


class menu_class_CrumbsParentFinder__hierarchy {
  
  protected $_titles;
  
  function getRuleTitles() {
    
    $q = db_query($sql = "SELECT menu_name, title FROM {menu_custom}");
    $titles = array();
    while ($row = db_fetch_object($q)) {
      $titles[$row->menu_name] = $row->title;
    }
    return $titles;
  }
  
  
  function find($path, $item) {
    $q = db_query($sql = "
      SELECT
        parent.link_path parent_path,
        parent.menu_name menu_name
      FROM
        {menu_links} parent
        INNER JOIN {menu_links} child ON (parent.mlid = child.plid)
      WHERE child.link_path = '%s'
      GROUP BY menu_name
    ", $path, $this->_menu_name);
    $candidates = array();
    if ($row = db_fetch_object($q)) {
      $candidates[$row->menu_name] = $row->parent_path;
    }
    return $candidates;
  }
  
  
  function prepareForItem($path, $item) {
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
    $this->_titles = $titles;
  }
  
  
  function decorate($path, $item, $menu_name) {
    if (isset($this->_titles[$menu_name])) {
      $item->title = $this->_titles[$menu_name];
    }
  }
}


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of menu module.
 */
class menu_class_CrumbsParentFinder__dummy {
  
  function find($path) {
    $q = db_query($sql = "
      SELECT
        parent.link_path parent_path,
        parent.menu_name menu_name
      FROM
        {menu_links} parent
        INNER JOIN {menu_links} child ON (parent.mlid = child.plid)
      WHERE child.link_path = '%s'
      GROUP BY menu_name
    ", $path, $this->_menu_name);
    $candidates = array();
    if ($row = db_fetch_object($q)) {
      $candidates[$row->menu_name] = $row->parent_path;
    }
    return $candidates;
  }
  
  
  function decorate($path, $item, $menu_name, $subkey_order) {
    $and = $menu_name ? " AND menu_name = '%s'" : '';
    $q = db_query($sql = "
      SELECT
        link_title,
        menu_name
      FROM
        {menu_links}
      WHERE link_path = '%s' $and
      GROUP BY menu_name
    ", $path, $menu_name);
    $titles = array();
    while ($row = db_fetch_object($q)) {
      if ($menu_name) {
        $item->title = $row->link_title;
        return;
      }
      $titles[$row->menu_name] = $row->link_title;
    }
    foreach ($subkey_order as $subkey => $enabled) {
      if (isset($titles[$subkey])) {
        $item->title = $titles[$subkey];
        break;
      }
    }
  }
}







