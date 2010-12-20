<?php
// $Id$


function pathauto_crumbs_plugins() {
  return new _pathauto_CrumbsPlugin();
}


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of path module.
 */
class _pathauto_CrumbsPlugin {
  
  function __construct() {
    module_load_include('inc', 'pathauto', 'pathauto');
  }
  
  function define($h) {
    $h->setTitle(t('the alias that pathauto would assign to this path.'));
  }
  
  function findParent__node__($path, $item) {
    $node = $item['page_arguments'][0];
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







