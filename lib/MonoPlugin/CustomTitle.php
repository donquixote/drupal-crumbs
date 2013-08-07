<?php

class crumbs_MonoPlugin_CustomTitle implements crumbs_MonoPlugin_FindTitleInterface {

  protected $title;

  function __construct(array $title) {
    $this->title = $title;
  }

  /**
   * @inheritdoc
   */
  function describe($api) {
    $api->titleWithLabel($this->title, t('Title'));
  }

  /**
   * @inheritdoc
   */
  function findTitle($path, $item) {
    return $this->title;
  }
}