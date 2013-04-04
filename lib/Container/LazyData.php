<?php

class crumbs_Container_LazyData {

  protected $data = array();
  protected $source;

  function __construct($source) {
    $this->source = $source;
  }

  function __get($key) {
    if (!isset($this->data[$key])) {
      $this->data[$key] = $this->source->$key($this);
    }
    return $this->data[$key];
  }
}
