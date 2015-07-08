<?php

use Drupal\crumbs\PluginSystem\Plugin\TitlePluginInterface;

interface crumbs_MultiPlugin_FindTitleInterface extends crumbs_MultiPlugin, TitlePluginInterface {

  /**
   * Find candidates for the title.
   *
   * @param string $path
   *   The path that we want to find a title for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string[]
   *   Title candidates
   */
  function findTitle($path, $item);
}
