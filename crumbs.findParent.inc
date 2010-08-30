<?php
// $Id$




class _crumbs_InvokeAction_findParent {
  
  protected $_path;
  protected $_item;
  protected $_method;
  protected $_order_key;
  protected $_parent_path;
  protected $_candidates = array();
  
  function __construct($path, $extra) {
    $this->_path = $path;
    $item = new crumbs_ItemWrapper_findParent($extra);
    $this->_item = $item;
    $this->_method = 'find__' . preg_replace('/[^a-z0-9]/', '_', strtolower($extra->item['path']));
  }
  
  function invoke($object, $object_key, $subkey) {
    $order_key = "$object_key:$subkey";
    if (isset($this->_candidates[$order_key])) {
      $this->_order_key = $order_key;
      $this->_parent_path = $this->_candidates[$order_key];
      return TRUE;
    }
    $path_method = $this->_method;
    foreach (array($path_method, 'find') as $method) {
      if (method_exists($object, $method)) {
        $result = $object->$method($this->_path, $this->_item);
        if (is_array($result)) {
          foreach ($result as $x_subkey => $parent_path) {
            $this->_candidates["$object_key:$x_subkey"] = $parent_path;
          }
        }
        else if (is_string($result) || is_numeric($result)) {
          $this->_candidates[$order_key] = $result;
        }
        break;
      }
    }
    if (isset($this->_candidates[$order_key])) {
      $this->_order_key = $order_key;
      $this->_parent_path = $this->_candidates[$order_key];
      return TRUE;
    }
  }
  
  function getParentPath() {
    return $this->_parent_path;
  }
  
  function getSuccessfulOrderKey() {
    return $this->_order_key;
  }
}




class crumbs_ItemWrapper_findParent extends crumbs_ItemWrapper {
  
  function skipInBreadcrumb() {
    $this->_extra->skip_in_breadcrumb = TRUE;
  }
  
  function __get($key) {
    return $this->_extra->$key;
  }
  
  /**
   * legacy support
   */
  function offsetSet($offset, $value) {
    switch ($offset) {
      case 'skip_in_breadcrumb':
      case 'title':
        $this->_extra->$offset = $value;
    }
  }
}






