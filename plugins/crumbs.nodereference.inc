<?php
// $Id$


function nodereference_crumbs_plugins() {
  $plugins = array();
  foreach (content_fields() as $key => $info) {
    if ($info['type'] === 'nodereference' && $info['multiple'] === 0) {
      $plugins[$key] = new _nodereference_CrumbsPlugin($key, $info);
    }
  }
  return $plugins;
}


/**
 * Implementation of class hook CrumbsPlugin
 * on the behalf of menu module.
 */
class _nodereference_CrumbsPlugin {
  
  protected $_field_key;
  protected $_field_info;
  
  function __construct($field_key, $field_info) {
    $this->_field_key = $field_key;
    $this->_field_info = $field_info;
  }
  
  /**
   * Tell crumbs that this object defines more than one rule.
   * Each returned rule will be shown in the config form to move up and down.
   */
  function define($h) {
    $h->enabledByDefault(FALSE);
    foreach ($this->_getReferenceableTypes() as $type_name) {
      $h->addRule($type_name);
    }
  }
  
  /**
   * Match "node/%" router path
   */
  function findParent__node__($path, $item) {
    $node = $item['map'][0];
    $result = array();
    if ($ref_nid = $this->_readParentNodeReference($node)) {
      $result[$node->type] = 'node/'. $ref_nid;
    }
    // return all candidates, so crumbs can sort them according to priority settings.
    return $result;
  }
  
  protected function _getReferenceableTypes() {
    $types = $this->_field_info['referenceable_types'];
    return is_array($types) ? $types : array();
  }
  
  protected function _readParentNodeReference($node) {
    $values = array();
    $field_key = $this->_field_key;
    $field = $node->$field_key;
    if (is_array($field) && !empty($field[0]['nid'])) {
      return (int)$field[0]['nid'];
    }
  }
}







