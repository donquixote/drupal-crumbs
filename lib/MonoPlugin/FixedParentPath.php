<?php

class crumbs_MonoPlugin_FixedParentPath implements crumbs_MonoPlugin_FindParentInterface {

  /**
   * @var string
   */
  protected $parentPath;

  /**
   * @param string $parent_path
   */
  function __construct($parent_path) {
    $this->parentPath = $parent_path;
  }

  /**
   * {@inheritdoc}
   */
  function describe($api) {
    // This is done elsewhere.
  }

  /**
   * {@inheritdoc}
   */
  function findParent($path, $item) {
    return $this->parentPath;
  }
}
