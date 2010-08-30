<?php
// $Id$




class crumbs_ReadonlyArrayWrapper implements ArrayAccess {
  
  protected $_array;
  
  function __construct(array &$array) {
    $this->_array = &$array;
  }
  
  function offsetSet($offset, $value) {
    return;
  }
  
  function offsetExists($offset) {
    return isset($this->_array[$offset]);
  }
  
  function offsetUnset($offset) {
    return;
  }
  
  function offsetGet($offset) {
    return isset($this->_array[$offset]) ? $this->_array[$offset] : NULL;
  }
}


class crumbs_ItemWrapper extends crumbs_ReadonlyArrayWrapper {
  
  protected $_extra = NULL;
  
  function __construct(stdClass $extra) {
    $this->_array = $extra->item;
    $this->_extra = $extra;
  }
  
  function getRouterPath() {
    return $this->_array['path'];
  }
  
  function getArg($i) {
    return $this->_array['page_arguments'][$i];
  }
  
  function getMapArg($i) {
    return $this->_array['map'][$i];
  }
  
  function getFragment($i) {
    $fragments = explode('/', $this->_extra->path);
    return $fragments[$i];
  }
  
  function __set($key, $value) {
    switch ($key) {
      case 'title':
        $this->_extra->$key = $value;
    }
  }
}


class crumbs_ItemWrapper_decorate extends crumbs_ItemWrapper {
  
  function __get($key) {
    return $this->_extra->$key;
  }
}


class crumbs_ItemWrapper_theme extends crumbs_ItemWrapper_decorate {
  
  function link($options = array()) {
    return l($this->_extra->title, $this->_extra->path, $options);
  }
  
  function __toString() {
    return $this->_extra->title;
  }
}





