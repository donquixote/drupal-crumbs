<?php



/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of menu module.
 */
class menu_class_CrumbsParentFinder {
  
  protected $_menu_name;
  protected $_menu_title;
  
  protected function __construct($menu_name, $menu_title) {
    $this->_menu_name = $menu_name;
    $this->_menu_title = $menu_title;
  }
  
  function construct() {
    $q = db_query($sql = "SELECT menu_name, title FROM {menu_custom}");
    $objects = array();
    while ($row = db_fetch_object($q)) {
      $objects[$row->menu_name] = new self($row->menu_name, $row->title);
    }
    return $objects;
  }
  
  function getRuleTitle() {
    $title = $this->_menu_title;
    return "menu: $title";
  }
  
  function find($path) {
    $q = db_query($sql = "
      SELECT
        parent.link_path parent_path
      FROM
        {menu_links} parent
        INNER JOIN {menu_links} child ON (parent.mlid = child.plid)
      WHERE child.link_path = '%s' AND child.menu_name = '%s' AND parent.menu_name = '%s'
    ", $path, $this->_menu_name, $this->_menu_name);
    if ($row = db_fetch_array($q)) {
      return $row['parent_path'];
    }
  }
  
  function decorate($path, &$menu_item) {
    $q = db_query($sql = "
      SELECT
        link_title
      FROM
        {menu_links}
      WHERE link_path = '%s' AND menu_name = '%s'
    ", $path, $this->_menu_name);
    while ($row = db_fetch_array($q)) {
      if ($row['link_title']) {
        $menu_item['title'] = $row['link_title'];
        break;
      }
    }
  }
}







