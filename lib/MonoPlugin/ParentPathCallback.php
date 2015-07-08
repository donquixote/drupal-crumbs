<?php

class crumbs_MonoPlugin_ParentPathCallback implements crumbs_MonoPlugin_FindParentInterface {

  /**
   * @var callback
   */
  protected $callback;

  /**
   * @param callback $callback
   */
  function __construct($callback) {
    $this->callback = $callback;
  }

  /**
   * {@inheritdoc}
   */
  function findParent($path, $item) {
    return call_user_func($this->callback, $path, $item);
  }

}
