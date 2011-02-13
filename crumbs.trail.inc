<?php
// $Id$


function crumbs_build_trail($path) {
  $finder = _crumbs_get_finder();
  return $finder->buildTrail($path);
}


function crumbs_get_trail_finder() {
  return _crumbs_get_finder();
}


function _crumbs_get_finder() {
  static $_trailFinder;
  if (!isset($_trailFinder)) {
    $parent_finder = crumbs_get_parent_finder();
    $_trailFinder = new _crumbs_TrailFinder($parent_finder);
  }
  return $_trailFinder;
}


function crumbs_get_parent_finder() {
  static $_parentFinder;
  if (!isset($_parentFinder)) {
    $plugin_engine = crumbs_get_plugin_engine();
    $_parentFinder = new crumbs_ParentFinder($plugin_engine);
  }
  return $_parentFinder;
}


/**
 * Very similar to menu_get_item(),
 * but with some little extra info stored in the result array.
 */
function crumbs_get_router_item($path) {
  $item = menu_get_item($path);
  // 'route' is a less ambiguous name for a router path than 'path'.
  $item['route'] = $item['path'];
  // 'href' sounds more like it had already run through url().
  $item['link_path'] = drupal_get_normal_path($path);
  $item['alias'] = drupal_get_path_alias($path);
  $item['fragments'] = explode('/', $path);
  return $item;
}


/**
 * Chop off path fragments until we find a valid path.
 * @param $path
 *   starting path or alias
 * @param $depth
 *   max number of fragments we try to chop off. -1 means no limit.
 */
function crumbs_reduce_path($path, $depth = -1) {
  $fragments = explode('/', $path);
  while (count($fragments) > 1 && $depth !== 0) {
    array_pop($fragments);
    $parent_path = implode('/', $fragments);
    $parent_item = crumbs_get_router_item($parent_path);
    if ($parent_item['href'] === $parent_item['link_path']) {
      return $parent_item['link_path'];
    }
    --$depth;
  }
}


class _crumbs_TrailFinder {

  protected $_parent_finder;
  protected $_front_menu_path;
  protected $_front_menu_item;

  function __construct($parent_finder) {
    $this->_parent_finder = $parent_finder;
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
    $front_menu_item = crumbs_get_router_item($front_normal_path);
    while (strlen($path) && $path !== '<front>' && $path !== $front_normal_path) {
      if (isset($trail_reverse[$path])) {
        // We found a loop! To prevent infinite recursion, we
        // remove the loopy paths from the trail and finish directly with <front>.
        while (isset($trail_reverse[$path])) {
          array_pop($trail_reverse);
        }
        break;
      }
      $item = crumbs_get_router_item($path);
      // if menu_get_item() does not resolve as a valid router item,
      // we skip this path.
      if ($item) {
        $trail_reverse[$path] = $item;
      }
      $parent_path = $this->_parent_finder->getParentPath($path, $item);
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
}


/**
 * Can find a parent path for a given path.
 * Contains a cache.
 */
class crumbs_ParentFinder {

  protected $_pluginEngine;

  // cached parent paths
  protected $_parents = array();
  protected $_log = array();

  function __construct($pluginEngine) {
    $this->_pluginEngine = $pluginEngine;
  }

  function getParentPath($path, &$item) {
    if (!isset($this->_parents[$path])) {
      $parent_path = $this->_findParentPath($path, $item);
      $this->_parents[$path] = drupal_get_normal_path($parent_path);
    }
    return $this->_parents[$path];
  }

  function getLoggedCandidates($path) {
    if (is_array($this->_log[$path])) {
      return $this->_log[$path];
    }
    else {
      return array();
    }
  }

  protected function _findParentPath($path, &$item) {
    if ($item) {
      $invoke_action = new _crumbs_InvokeAction_findParent($path, $item);
      $this->_pluginEngine->invokeAll_find($invoke_action);
      $parent_path = $invoke_action->getValue();
      $this->_log[$path] = $invoke_action->getLoggedCandidates();
      if (isset($parent_path)) {
        $item['crumbs_candidate_key'] = $invoke_action->getCandidateKey();
        return $parent_path;
      }
    }
    // fallback: chop off the last fragment of the system path.
    $parent_path = crumbs_reduce_path($path);
    return isset($parent_path) ? $parent_path : FALSE;
  }
}


class _crumbs_InvokeAction_findParent extends crumbs_InvokeAction_findForPath {

  protected $_method = 'findParent';

  protected function _invoke($plugin, $method) {
    $result = $plugin->$method($this->_path, $this->_item);
    return $result;
  }
}





