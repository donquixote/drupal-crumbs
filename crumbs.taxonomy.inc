<?php



/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of taxonomy module.
 */
class taxonomy_class_CrumbsParentFinder {
  
  protected $_voc;
  
  protected function __construct($voc) {
    $this->_voc = $voc;
  }
  
  function construct() {
    $objects = array();
    foreach (taxonomy_get_vocabularies() as $voc_id => $voc) {
      $objects[$voc_id] = new self($voc);
    }
    return $objects;
  }
  
  function getRuleTitle() {
    $name = $this->_voc->name;
    return "vocabulary: $name";
  }
  
  function find__node__($path, $menu_item) {
    $node = $menu_item['page_arguments'][0];
    if (is_array($node->taxonomy)) {
      foreach ($node->taxonomy as $tid => $term) {
        if ($this->_voc->vid == $term->vid) {
          return "taxonomy/term/$tid";
        }
      }
    }
  }
  
  function decorate__taxonomy_term__($path, &$menu_item) {
    if (!empty($menu_item['title'])) {
      return;
    }
    $terms = $menu_item['page_arguments'][2];
    if (!is_array($terms)) {
      $terms = taxonomy_terms_parse_string($terms);
    }
    if (!is_array($terms['tids']) || !count($terms['tids'])) {
      return;
    }
    $db_placeholders = db_placeholders($terms['tids']);
    $q = db_query($sql_r = db_rewrite_sql($sql = "
      SELECT t.tid, t.name
      FROM {term_data} t
      WHERE t.tid IN ($db_placeholders)
    ", 't', 'tid'), $terms['tids']);
    // we rebuild the $names-array so it only contains terms the user has access to.
    $names = array();
    while ($term = db_fetch_object($q)) {
      $names[$term->tid] = $term->name;
    }
    $menu_item['title'] = implode(',', $names);
  }
  
  function find__taxonomy_term__($path, $menu_item) {
    $terms = $menu_item['page_arguments'][2];
    if (!is_array($terms)) {
      $terms = taxonomy_terms_parse_string($terms);
    }
    if (!isset($terms['tids']) || !count($terms['tids']) !== 1) {
      return;
    }
    $tid = $terms['tids'][0];
    $q = db_query($sql = "SELECT parent FROM {term_hierarchy} WHERE tid = %d", $tid);
    if ($row = db_fetch_object($q)) {
      return 'taxonomy/term/'.$row->parent;
    }
  }
}




