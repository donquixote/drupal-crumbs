<?php

class crumbs_MonoPlugin_ParentPathPattern implements crumbs_MonoPlugin_FindParentInterface {

  /**
   * @var string
   */
  protected $parentPathPattern;

  /**
   * @param string $parent_path_pattern
   */
  function __construct($parent_path_pattern) {
    $this->parentPathPattern = $parent_path_pattern;
  }

  /**
   * @inheritdoc
   */
  function describe($api) {
    $api->titleWithLabel($this->parentPathPattern, t('Parent'));
  }

  /**
   * @inheritdoc
   */
  function findParent($path, $item) {
    $replacements = array();
    foreach ($item['fragments'] as $i => $fragment) {
      $replacements['{' . $i . '}'] = $fragment;
    }
    return strtr($this->parentPathPattern, $replacements);
  }
}