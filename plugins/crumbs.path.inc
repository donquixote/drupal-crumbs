<?php
// $Id$


function path_crumbs_plugins() {
  return new _path_CrumbsPlugin();
}


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of path module.
 */
class _path_CrumbsPlugin {
  
  function define($h) {
    $h->setTitle(t('existing alias for the given path'));
  }
  
  function findParent($path) {
    $alias = drupal_get_path_alias($path);
    $fragments = explode('/', $alias);
    if (count($fragments) > 1) {
      array_pop($fragments);
      return implode('/', $fragments);
    }
  }
}







