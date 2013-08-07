<?php

class crumbs_MonoPlugin_CustomParentPath implements crumbs_MonoPlugin_FindParentInterface {

  protected $parentPath;

  function __construct(array $parent_path) {
    $this->parentPath = $parent_path;
  }

  /**
   * @inheritdoc
   */
  function describe($api) {
    $api->titleWithLabel($this->parentPath, t('Parent'));
  }

  /**
   * @inheritdoc
   */
  function findParent($path, $item) {
    return $this->parentPath;
  }
}