<?php

namespace Drupal\crumbs\PluginSystem\Plugin\LegacyWrapper;

class MonoTitlePluginLegacyWrapper implements \crumbs_MonoPlugin_FindTitleInterface {

  /**
   * @var \crumbs_MonoPlugin
   */
  private $wrappedPlugin;

  /**
   * @var string
   */
  private $method;

  /**
   * @param \crumbs_MonoPlugin $wrappedPlugin
   * @param string $method
   */
  function __construct(\crumbs_MonoPlugin $wrappedPlugin, $method) {
    $this->wrappedPlugin = $wrappedPlugin;
    $this->method = $method;
  }

  /**
   * Find candidates for the title.
   *
   * @param string $path
   *   The path that we want to find a title for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string|null
   *   Title candidate, or NULL if none found.
   */
  function findTitle($path, $item) {
    return $this->wrappedPlugin->{$this->method}($path, $item);
  }

}
