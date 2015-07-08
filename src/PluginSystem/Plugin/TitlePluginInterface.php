<?php

namespace Drupal\crumbs\PluginSystem\Plugin;

/**
 * @see \crumbs_MonoPlugin_FindTitleInterface
 * @see \crumbs_MultiPlugin_FindTitleInterface
 */
interface TitlePluginInterface extends \crumbs_PluginInterface {

  /**
   * Find candidates for the title.
   *
   * @param string $path
   *   The path that we want to find a title for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string[]|string|NULL
   *   Title candidates, or only one title candidate, depending
   *   whether this is a mono plugin or a multi plugin.
   */
  function findTitle($path, $item);

}
