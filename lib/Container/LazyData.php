<?php

class crumbs_Container_LazyData {

  /**
   * @var array
   */
  protected $data = array();

  /**
   * @var crumbs_CurrentPageInfo
   */
  protected $source;

  /**
   * @todo Add an interface for $source.
   *   Don't restrict to crumbs_CurrentPageInfo.
   *
   * @param crumbs_CurrentPageInfo $source
   */
  function __construct($source) {
    $this->source = $source;
  }

  /**
   * @param string $key
   * @return mixed
   */
  function __get($key) {
    if (!isset($this->data[$key])) {
      $this->data[$key] = $this->source->$key($this);
    }
    return $this->data[$key];
  }
}
