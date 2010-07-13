<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of path module.
 */
class pathauto_class_CrumbsParentFinder {
  
  function __construct() {
    module_load_include('inc', 'pathauto', 'pathauto');
  }
  
  function getRuleTitle() {
    return 'the alias that pathauto would assign to this path.';
  }
  
  function find__node__($path, $menu_item) {
    $node = $menu_item['page_arguments'][0];
    if (is_object($node)) {
      $placeholders = pathauto_get_placeholders('node', $node);
      $alias = pathauto_create_alias('node', 'return', $placeholders, $path, $node->nid, $node->type, $node->language);
      $fragments = explode('/', $alias);
      if (count($fragments) > 1) {
        array_pop($fragments);
        return implode('/', $fragments);
      }
    }
  }
}







