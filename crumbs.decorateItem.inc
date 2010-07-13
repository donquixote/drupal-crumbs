<?php
// $Id$





class _crumbs_InvokeAction_decorateItem {
  
  protected $_path;
  protected $_item;
  protected $_method_suffix;
  protected $_objects_prepared = array();
  
  function __construct($path, $extra) {
    $this->_path = $path;
    $item = new crumbs_ItemWrapper_decorateItem($extra);
    $this->_item = $item;
    $this->_method_suffix = '__' . preg_replace('/[^a-z0-9]/', '_', strtolower($extra->item['path']));
  }
  
  
  function invoke($object, $object_key, $subkey) {
    if (!$this->_objects_prepared[$object_key]) {
      $this->_invoke($object, 'prepareForItem');
      $this->_objects_prepared[$object_key] = TRUE;
    }
    $this->_invoke($object, 'decorate', $subkey);
  }
  
  
  protected function _invoke($object, $m, $subkey = NULL) {
    foreach (array($m . $this->_method_suffix, $m) as $method) {
      if (method_exists($object, $method)) {
        return $object->$method($this->_path, $this->_item, $subkey);
      }
    }
  }
}


class crumbs_ItemWrapper_decorateItem extends crumbs_ItemWrapper_decorate {
  // no changes
}


