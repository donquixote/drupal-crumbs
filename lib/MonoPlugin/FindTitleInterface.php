<?php

use Drupal\crumbs\PluginSystem\Plugin\TitlePluginInterface;

interface crumbs_MonoPlugin_FindTitleInterface extends crumbs_MonoPlugin, TitlePluginInterface {

  /**
   * Find candidates for the parent path.
   *
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string|null
   *   Title candidate.
   */
  function findTitle($path, $item);
}
