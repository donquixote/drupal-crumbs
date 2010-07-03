<?php



/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of path module.
 */
class path_class_CrumbsParentFinder {
  
  function getRuleTitle() {
    return 'existing alias';
  }
  
  function find($path) {
    $alias = drupal_get_path_alias($path);
    $fragments = explode('/', $alias);
    if (count($fragments) > 1) {
      array_pop($fragments);
      return implode('/', $fragments);
    }
  }
}







