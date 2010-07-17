<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of menu module.
 */
class nodereference_class_CrumbsParentFinder {
  
  protected $_field_key;
  protected $_field_info;
  
  static function construct() {
    $result = array();
    foreach (content_fields() as $key => $info) {
      if ($info['type'] == 'nodereference' && $info['multiple'] == 0) {
        $result[$key] = new self($key, $info);
      }
    }
    return $result;
  }
  
  protected function __construct($field_key, $field_info) {
    $this->_field_key = $field_key;
    $this->_field_info = $field_info;
  }

  static function enabledByDefault_static() {
    return FALSE;
  }
  
  /**
   * Tell crumbs that this object defines more than one rule.
   * Each returned rule will be shown in the config form to move up and down.
   */
  function getRuleTitles() {
    $titles = array();
    foreach ($this->_field_info['referenceable_types'] as $type_name) {
      $titles[$type_name] = '';
    }
    return $titles;
  }
  
  /**
   * Match node/% router path
   */
  function find__node__($path, $item) {
    $node = $item->getArg(0);
    $result = array();
    if ($ref_nid = $this->_readParentNodeReference($node)) {
      $result[$node->type] = $ref_nid;
    }
    // return all candidates, so crumbs can sort them according to priority settings.
    return $result;
  }
  
  protected function _readParentNodeReference($node) {
    $values = array();
    $field_key = $this->_field_key;
    $field = $node->$field_key;
    if (is_array($field) && !empty($field[0]['nid'])) {
      return 'node/' . (int)$field[0]['nid'];
    }
  }
}







