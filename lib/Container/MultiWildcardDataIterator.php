<?php

class crumbs_Container_MultiWildcardDataIterator extends ArrayIterator {

  protected $container;

  function __construct($container, $keys) {
    $this->container = $container;
    parent::__construct($keys);
  }

  function current() {
    return new crumbs_Container_MultiWildcardDataOffset($this->container, $this->key());
  }
}
