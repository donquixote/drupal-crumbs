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
    $h->addRule('alias', t('existing alias for the given path'));
  }

  function disabledByDefault() {
    return array('*');
  }
  
  function findParent($path) {
    if ($alias = drupal_lookup_path($path)) {
      $parent_path = crumbs_reduce_path($alias, 1);
      return isset($parent_path) ? array('alias' => $parent_path) : NULL;
    }
  }
}







