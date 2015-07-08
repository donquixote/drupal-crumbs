<?php

namespace Drupal\crumbs\PluginSystem\Plugin\LegacyWrapper;

class MonoParentPluginLegacyWrapper implements \crumbs_MonoPlugin_FindParentInterface {

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
  function findParent($path, $item) {
    return $this->wrappedPlugin->{$this->method}($path, $item);
  }

}
