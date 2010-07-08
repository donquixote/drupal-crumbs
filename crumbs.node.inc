<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of node module.
 */


class node_class_CrumbsParentFinder {
  
  function getRuleTitle() {
    return t('Experimental feature. Disable if it makes problems.');
  }

  function find__node__($path, $menu_item) {
    $node = $menu_item['page_arguments'][0];
    $type_url_str = str_replace('_', '-', $node->type);
    return 'crumbs/node-type-dummy-path/'. $type_url_str;
  }
  
  function decorate__crumbs_node_type_dummy_path__($path, $menu_item) {
    $menu_item['skip_in_breadcrumb'] = TRUE;
  }
}






