<?php
// $Id$


function crumbs_build_trail($path) {
  $finder = _crumbs_get_finder();
  return $finder->buildTrail($path);
}


function _crumbs_get_finder() {
  static $_finder;
  if (!isset($_finder)) {
    $pluginEngine = crumbs_get_plugin_engine();
    $_finder = new _crumbs_TrailFinder($pluginEngine);
  }
  return $_finder;
}


class _crumbs_TrailFinder {

  protected $_pluginEngine;
  protected $_cache;

  protected $_front_menu_path;
  protected $_front_menu_item;

  function __construct($pluginEngine) {
    $this->_pluginEngine = $pluginEngine;
    $this->_front_normal_path = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
    $this->_front_menu_item = menu_get_item($front_normal_path);
  }

  /**
   * Build the raw trail,
   * with no respect to title, access check, or skip-in-breadcrumb.
   */
  function buildTrail($path) {
    $path = drupal_get_normal_path($path);
    $trail_reverse = array();
    while (strlen($path) && $path !== '<front>' && $path != $front_normal_path) {
      if (isset($trail_reverse[$path])) {
        // We found a loop! To prevent infinite recursion, we
        // remove the loopy paths from the trail and finish directly with <front>.
        while (isset($trail_reverse[$path])) {
          array_pop($trail_reverse);
        }
        break;
      }
      $item = menu_get_item($path);
      $item['alias'] = drupal_get_path_alias($path);
      $item['fragments'] = explode('/', $path);
      $parent_path = $this->_findParentPath($path, $item);
      $parent_path = drupal_get_normal_path($parent_path);
      if ($item) {
        $trail_reverse[$path] = $item;
      }
      if ($parent_path == $path) {
        // This is again a loop, but with just one step.
        // Not as evil as the other kind of loop.
        break;
      }
      $path = $parent_path;
    }
    unset($trail_reverse[$front_normal_path]);
    $trail_reverse['<front>'] = $front_menu_item;
    return array_reverse($trail_reverse);
  }

  protected function _findParentPath($path, $item) {
    if ($item) {
      $invokeAction = new _crumbs_InvokeAction_findParent($path, $item);
      $this->_pluginEngine->invokeAll($invokeAction);
      $parent_path = $invokeAction->getValue();
      if ($parent_path) {
        return $parent_path;
      }
    }
    // fallback: chop off the last fragment of the system path.
    $fragments = explode('/', $path);
    $parent_path = implode('/', array_slice($fragments, 0, -1));
    return $parent_path;
  }
}


class _crumbs_InvokeAction_findParent extends crumbs_InvokeAction_findSomething {

  protected $_method = 'findParent';

  protected function _invoke($plugin, $method) {
    return $plugin->$method($this->_path, $this->_item);
  }
}





