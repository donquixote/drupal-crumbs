<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface;

interface HookInterface {

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $titleCollectionContainer
   */
  function invokeAll(
    PrimaryPluginCollectorInterface $parentCollectionContainer,
    PrimaryPluginCollectorInterface $titleCollectionContainer
  );
}
