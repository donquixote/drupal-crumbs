<?php

class crumbs_Router {

  function __construct() {
  }

  /**
   * Returns a router item.
   *
   * This is a wrapper around menu_get_item() that sets additional keys
   * (route, link_path, alias, fragments).
   *
   * @param $path
   *   The path for which the corresponding router item is returned.
   *   For example, node/5.
   *
   * @return array|null
   *   The router item.
   */
  function getRouterItem($path) {
    $normalpath = drupal_get_normal_path($path);
    try {
      $item = menu_get_item($normalpath);
    }
    catch (Exception $e) {
      // Some modules throw an exception, if a path has unloadable arguments.
      // We don't care, because we don't actually load this page.
      return NULL;
    }

    // Some additional keys.
    if (!empty($item) && is_array($item)) {
      // 'route' is a less ambiguous name for a router path than 'path'.
      $item['route'] = $item['path'];
      // 'href' sounds more like it had already run through url().
      $item['link_path'] = $normalpath;
      $item['alias'] = drupal_get_path_alias($normalpath);
      $item['fragments'] = explode('/', $normalpath);

      if (!isset($item['localized_options'])) {
        $item['localized_options'] = array();
      }

      if ($normalpath !== $item['href']) {
        $pos = strlen($item['href']);
        $item['variadic_suffix'] = substr($normalpath, $pos);
      }
      else {
        $item['variadic_suffix'] = NULL;
      }

      return $item;
    }
  }

  /**
   * Chop off path fragments until we find a valid path.
   *
   * @param $path
   *   Starting path or alias
   * @param $depth
   *   Max number of fragments we try to chop off. -1 means no limit.
   */
  function reducePath($path, $depth = -1) {
    $fragments = explode('/', $path);
    while (count($fragments) > 1 && $depth !== 0) {
      array_pop($fragments);
      $parent_path = implode('/', $fragments);
      $parent_item = $this->getRouterItem($parent_path);
      if ($parent_item && $parent_item['href'] === $parent_item['link_path']) {
        return $parent_item['link_path'];
      }
      --$depth;
    }
  }
}
