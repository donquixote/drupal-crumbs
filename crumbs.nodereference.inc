<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of menu module.
 */
class nodereference_class_CrumbsParentFinder {
  
  /**
   * Tell crumbs that this object defines more than one rule.
   * Each returned rule will be shown in the config form to move up and down.
   */
  function getRuleTitles() {
    $titles = array();
    foreach ($this->_getParentNodeReferenceFields() as $field_name => $field_title) {
      $titles[$field_name] = "nodereference: " . $field_title;
    }
    return $titles;
  }
  
  /**
   * Match node/% router path
   */
  function find__node__($path, $item) {
    $node = $item->getArg(0);
    $result = array();
    foreach ($this->_readParentNodeReferences($node) as $field_name => $ref_nid) {
      $result[$field_name] = $ref_nid;
    }
    // return all candidates, so crumbs can sort them according to priority settings.
    return $result;
  }
  
  protected function _readParentNodeReferences($node) {
    $fields = $this->_getParentNodeReferenceFields();
    $values = array();
    foreach ($fields as $field_key => $field_title) {
      $field = $node->$field_key;
      if (is_array($field) && !empty($field[0]['nid'])) {
        $values[$field_key] = 'node/' . (int)$field[0]['nid'];
      }
    }
    return $values;
  }
  
  protected function _getParentNodeReferenceFields() {
    $result = array();
    foreach (content_fields() as $field_key => $info) {
      if ($info['type'] == 'nodereference' && preg_match('/_parent_/', $field_key)) {
        $result[$field_key] = $field_key;
      }
    }
    return $result;
  }
}







