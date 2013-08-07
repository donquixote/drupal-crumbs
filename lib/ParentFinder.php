<?php


/**
 * Can find a parent path for a given path.
 * Contains a cache.
 */
class crumbs_ParentFinder {

  /**
   * @var crumbs_PluginEngine
   */
  protected $pluginEngine;

  /**
   * @var array
   *   Cached parent paths
   */
  protected $parents = array();

  /**
   * @param crumbs_PluginEngine $pluginEngine
   */
  function __construct($pluginEngine) {
    $this->pluginEngine = $pluginEngine;
  }

  /**
   * @param string $path
   * @param array &$item
   * @return string
   */
  function getParentPath($path, &$item) {
    if (!isset($this->parents[$path])) {
      $parent_path = $this->_findParentPath($path, $item);
      if (is_string($parent_path)) {
        $parent_path = drupal_get_normal_path($parent_path);
      }
      $this->parents[$path] = $parent_path;
    }
    return $this->parents[$path];
  }

  protected function _findParentPath($path, &$item) {
    if ($item) {
      if (!$item['access']) {
        // Parent should be the front page.
        return FALSE;
      }
      $parent_path = $this->pluginEngine->findParent($path, $item);
      if (isset($parent_path)) {
        return $parent_path;
      }
    }
    // fallback: chop off the last fragment of the system path.
    $parent_path = crumbs_reduce_path($path);
    return isset($parent_path) ? $parent_path : FALSE;
  }
}
