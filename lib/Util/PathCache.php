<?php

class crumbs_Util_PathCache {

  protected $data = array();
  protected $source;

  function __construct($source) {
    $this->source = $source;
  }

  function getForPath($path) {
    if (!isset($this->data[$path])) {
      $this->data[$path] = $this->source->getForPath($path);
    }
    return $this->data[$path];
  }
}
