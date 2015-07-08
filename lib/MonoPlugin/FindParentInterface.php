<?php

use Drupal\crumbs\PluginSystem\Plugin\ParentPluginInterface;

interface crumbs_MonoPlugin_FindParentInterface extends crumbs_MonoPlugin, ParentPluginInterface {

  /**
   * Find candidates for the parent path.
   *
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string|null
   *   Parent path candidate, or NULL if none found.
   */
  function findParent($path, $item);
}
