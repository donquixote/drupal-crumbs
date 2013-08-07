<?php

class crumbs_Container_MultiWildcardData implements ArrayAccess, IteratorAggregate {

  protected $keys;
  protected $containers = array();

  function __construct($keys) {
    $this->keys = $keys;
  }

  function __get($key) {
    if (!isset($this->containers[$key])) {
      $this->containers[$key] = new crumbs_Container_WildcardData();
    }
    return $this->containers[$key];
  }

  function __set($key, $data) {
    $this->containers[$key] = new crumbs_Container_WildcardData($data);
  }

  function getIterator() {
    return new crumbs_Container_MultiWildcardDataIterator($this, $this->keys);
  }

  function offsetGet($key) {
    return new crumbs_Container_MultiWildcardDataOffset($this, $key);
  }

  function offsetSet($key, $value) {
    throw new Exception("offsetSet not supported.");
  }

  function offsetExists($key) {
    return isset($this->keys[$key]);
  }

  function offsetUnset($key) {
    throw new Exception("offsetUnset not supported.");
  }
}
