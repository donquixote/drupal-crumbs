<?php
// $Id$


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of menu module.
 */
class views_ui_class_CrumbsParentFinder {
  
  function getRuleTitle() {
    return t('Define more meaningful titles for views_ui pages.');
  }
  
  function decorate__admin_build_views_export__($path, $item) {
    $view = $item['map'][4];
    $item->title = t('Export view "@view"', array('@view' => $view->name));
  }
  
  function decorate__admin_build_views_clone__($path, $item) {
    $view = $item['map'][4];
    $item->title = t('Clone view "@view"', array('@view' => $view->name));
  }
  
  function decorate__admin_build_views_edit__($path, $item) {
    $view = $item['map'][4];
    $item->title = t('Edit view "@view"', array('@view' => $view->name));
  }
  
  function decorate__admin_build_views_delete__($path, $item) {
    $view = $item['map'][4];
    $item->title = t('Delete view "@view"', array('@view' => $view->name));
  }
}







