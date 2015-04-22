<?php

use Drupal\crumbs\PluginSystem\Plugin\ParentPluginInterface;

interface crumbs_MultiPlugin_FindParentInterface extends crumbs_MultiPlugin, ParentPluginInterface {

  /**
   * Find candidates for the parent path.
   *
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string[]
   *   Parent path candidates
   */
  function findParent($path, $item);
}
