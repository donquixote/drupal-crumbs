<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of node module.
 */


class crumbs_class_CrumbsParentFinder {
  
  function getRuleTitle() {
    return 'Set Home as the title for the root item.';
  }

  function decorateTrail($trail) {
    // use a foreach to get the first item..
    foreach ($trail as $item) {
      $item->title = t('Home');
      break;
    }
  }
}






