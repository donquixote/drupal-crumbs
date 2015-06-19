<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface;

interface HookInterface {

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $titleCollectionContainer
   * @param bool[] $uncachableModules
   */
  function invokeAll(
    RoutelessPluginCollectorInterface $parentCollectionContainer,
    RoutelessPluginCollectorInterface $titleCollectionContainer,
    array &$uncachableModules
  );
}
