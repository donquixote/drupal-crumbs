<?php

class crumbs_Container_MultiWildcardDataOffset {

  protected $container;
  protected $key;

  function __construct($container, $key) {
    $this->container = $container;
    $this->key = $key;
  }

  function __get($key) {
    return $this->container->__get($key)->valueAtKey($this->key);
  }

  function getAll($key) {
    return $this->container->__get($key)->getAll($this->key);
  }

  function getAllMerged($key) {
    return $this->container->__get($key)->getAllMerged($this->key);
  }
}
