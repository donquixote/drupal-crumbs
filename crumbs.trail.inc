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

  // cached parent paths
  protected $_parents = array();

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
    $front_normal_path = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
    $front_menu_item = menu_get_item($front_normal_path);
    while (strlen($path) && $path !== '<front>' && $path !== $front_normal_path) {
      if (isset($trail_reverse[$path])) {
        // We found a loop! To prevent infinite recursion, we
        // remove the loopy paths from the trail and finish directly with <front>.
        while (isset($trail_reverse[$path])) {
          array_pop($trail_reverse);
        }
        break;
      }
      $item = $this->_getRouterItem($path);
      // if menu_get_item() does not resolve as a valid router item,
      // we skip this path.
      if ($item) {
        $trail_reverse[$path] = $item;
      }
      $parent_path = $this->_getParentPath($path, $item);
      if ($parent_path === $path) {
        // This is again a loop, but with just one step.
        // Not as evil as the other kind of loop.
        break;
      }
      $path = $parent_path;
    }
    unset($trail_reverse['<front>']);
    $trail_reverse[$front_normal_path] = $front_menu_item;
    return array_reverse($trail_reverse);
  }

  protected function _getRouterItem($path) {
    $item = menu_get_item($path);
    // 'route' is a less ambiguous name for a router path than 'path'.
    $item['route'] = $item['path'];
    // 'href' sounds more like it had already run through url().
    $item['link_path'] = $item['href'];
    $item['alias'] = drupal_get_path_alias($path);
    $item['fragments'] = explode('/', $path);
    return $item;
  }

  protected function _getParentPath($path, &$item) {
    if (!isset($this->_parents[$path])) {
      $parent_path = $this->_findParentPath($path, $item);
      $this->_parents[$path] = drupal_get_normal_path($parent_path);
    }
    return $this->_parents[$path];
  }

  protected function _findParentPath($path, &$item) {
    if ($item) {
      $invokeAction = new _crumbs_InvokeAction_findParent($path, $item);
      $this->_pluginEngine->invokeAll_find($invokeAction);
      $parent_path = $invokeAction->getValue();
      if ($parent_path) {
        $item['crumbs_candidate_key'] = $invokeAction->getCandidateKey();
        return $parent_path;
      }
    }
    // fallback: chop off the last fragment of the system path.
    $fragments = explode('/', $path);
    $parent_path = implode('/', array_slice($fragments, 0, -1));
    return $parent_path;
  }
}


class _crumbs_InvokeAction_findParent extends crumbs_InvokeAction_findForPath {

  protected $_method = 'findParent';

  protected function _invoke($plugin, $method) {
    $result = $plugin->$method($this->_path, $this->_item);
    return $result;
  }
}





