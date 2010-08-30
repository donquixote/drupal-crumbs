<?php
// $Id$



function _crumbs_get_finder() {
  static $_finder;
  if (!isset($_finder)) {
    $pluginEngine = _crumbs_load_plugin_engine();
    $_finder = new _crumbs_TrailFinder($pluginEngine);
  }
  return $_finder;
}




class _crumbs_TrailFinder {
  
  protected $_pluginEngine;
  
  function __construct($pluginEngine) {
    $this->_pluginEngine = $pluginEngine;
  }
  
  
  function buildTrail($path) {
    $path = drupal_get_normal_path($path);
    $trail_reverse = array();
    $front_normal_path = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
    $front_menu_item = menu_get_item($front_normal_path);
    while (strlen($path) && $path !== '<front>' && $path != $front_normal_path) {
      if (isset($trail_reverse[$path])) {
        // We found a loop! To prevent infinite recursion, we
        // remove the loopy paths from the trail and finish directly with <front>.
        while (isset($trail_reverse[$path])) {
          array_pop($trail_reverse);
        }
        break;
      }
      $menu_item = menu_get_item($path);
      $extra = (object)array(
        'title' => $menu_item['title'],
        'path' => $path,
        'item' => $menu_item,
        'alias' => drupal_get_path_alias($path),
      );
      $parent_path = $this->_findParentPath($path, $extra);
      $parent_path = drupal_get_normal_path($parent_path);
      if ($menu_item) {
        if (count(explode('/', $menu_item['path'])) <= count(explode('/', $path))) {
          $this->_decorateItem($path, $extra, $trail_reverse);
          if (!$extra->skip_in_breadcrumb && $menu_item['access']) {
            $trail_reverse[$path] = $extra;
          }
        }
      }
      if ($parent_path == $path) {
        // This is again a loop, but with just one step.
        // Not as evil as the other kind of loop.
        break;
      }
      $path = $parent_path;
    }
    unset($trail_reverse[$front_normal_path]);
    $trail_reverse['<front>'] = (object)array(
      'title' => $front_menu_item['title'],
      'path' => $front_normal_path,
      'item' => $front_menu_item,
    );
    $trail = array_reverse($trail_reverse);
    $this->_decorateTrail($trail);
    return $trail;
  }
  
  
  function invokeInit($trail) {
    $invokeAction = new _crumbs_InvokeAction_simple('init', array($trail));
    $this->_pluginEngine->invokeAll($invokeAction);
  }
  
  
  function invokePreprocessPage(&$vars, $trail) {
    $invokeAction = new _crumbs_InvokeAction_simple('preprocessPage', array(&$vars, $trail));
    $this->_pluginEngine->invokeAll($invokeAction);
  }
  
  
  /**
   * This function is called when the full trail has been built.
   * Other modules get a chance to modify the title.
   */
  protected function _decorateTrail(array $trail) {
    $trail_wrapped = array();
    foreach ($trail as $path => $extra) {
      $trail_wrapped[$path] = new crumbs_ItemWrapper_decorateTrail($extra);
    }
    $invokeAction = new _crumbs_InvokeAction_simple('decorateTrail', array($trail_wrapped));
    $this->_pluginEngine->invokeAll($invokeAction);
  }
  
  
  protected function _decorateItem($path, stdClass $extra) {
    if ($extra->item) {
      $invokeAction = new _crumbs_InvokeAction_decorateItem($path, $extra);
      // run this in reverse direction ..
      $this->_pluginEngine->invokeAll($invokeAction, TRUE);
    }
  }
  
  
  protected function _findParentPath($path, stdClass $extra) {
    if ($extra->item) {
      $invokeAction = new _crumbs_InvokeAction_findParent($path, $extra);
      $this->_pluginEngine->invokeAll($invokeAction);
      $parent_path = $invokeAction->getParentPath();
      if ($parent_path) {
        $order_key = $invokeAction->getSuccessfulOrderKey();
        return $parent_path;
      }
    }
    // fallback: chop off the last fragment of the system path.
    $fragments = explode('/', $path);
    $parent_path = implode('/', array_slice($fragments, 0, -1));
    return $parent_path;
  }
}


