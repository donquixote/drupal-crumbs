<?php


function path_crumbs_plugins() {
  return array(
    'alias' => new _path_CrumbsPlugin(),
  );
}


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of path module.
 */
class _path_CrumbsPlugin {
  
  function defineOne($h) {
    $h->setTitle(t('existing alias for the given path'));
  }

  function disabledByDefault() {
    return array('');
  }
  
  function findParent($path, $item) {
    if ($alias = $item['alias']) {
      return crumbs_reduce_path($alias, 1);
    }
  }
}







