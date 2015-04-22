<?php

namespace Drupal\crumbs\PluginSystem\Plugin;

/**
 * @see \crumbs_MonoPlugin_FindParentInterface
 * @see \crumbs_MultiPlugin_FindParentInterface
 */
interface ParentPluginInterface extends \crumbs_PluginInterface {

  /**
   * Find candidates for the parent path.
   *
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string[]|string|NULL
   *   Parent path candidates, or only one parent path candidate, depending
   *   whether this is a mono plugin or a multi plugin.
   */
  function findParent($path, $item);

}
