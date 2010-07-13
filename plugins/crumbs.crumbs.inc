<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * for crumbs itself.
 */
class crumbs_class_CrumbsParentFinder {
  function construct() {
    return array(
      'home_title' => new crumbs_class_CrumbsParentFinder__home_title,
    );
  }
}


class crumbs_class_CrumbsParentFinder__home_title {
  
  function getRuleTitle() {
    return 'Set "Home" as the title for the root item.';
  }

  function decorateTrail($trail) {
    // use a foreach to get the first item..
    foreach ($trail as $item) {
      $item->title = t('Home');
      break;
    }
  }
}






