<?php

class crumbs_MonoPlugin_TitleCallback implements crumbs_MonoPlugin_FindTitleInterface {

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
  function findTitle($path, $item) {
    return call_user_func($this->callback, $path, $item);
  }

}
