<?php

namespace Drupal\crumbs\Router;

/**
 * @see \crumbs_Router
 */
interface RouterInterface {

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
  function getRouterItem($path);

  /**
   * Chop off path fragments until we find a valid path.
   *
   * @param string $path
   *   Starting path or alias
   * @param int $depth
   *   Max number of fragments we try to chop off. -1 means no limit.
   *
   * @return string|null
   */
  function reducePath($path, $depth = -1);

  /**
   * @param string $path
   *
   * @return string
   */
  function getNormalPath($path);

  /**
   * @param string $url
   *
   * @return bool
   *   TRUE, if external path.
   */
  function urlIsExternal($url);

  /**
   * @return string
   */
  function getFrontNormalPath();

}
