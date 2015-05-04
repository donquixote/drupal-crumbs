<?php

namespace Drupal\crumbs\PluginSystem\Discovery;

use Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface;
use Drupal\crumbs\PluginSystem\Discovery\Hook\HookCrumbsPlugins;
use Drupal\crumbs\PluginSystem\Discovery\Hook\HookInterface;

class PluginDiscovery {

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Hook\HookInterface
   */
  protected $hook;

  /**
   * @return static
   */
  static function create() {
    return new static(new HookCrumbsPlugins());
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\HookInterface $hook
   */
  function __construct(HookInterface $hook) {
    $this->hook = $hook;
  }

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $titleCollectionContainer
   */
  function discoverPlugins(
    PrimaryPluginCollectorInterface $parentCollectionContainer,
    PrimaryPluginCollectorInterface $titleCollectionContainer
  ) {
    $this->hook->invokeAll($parentCollectionContainer, $titleCollectionContainer);

    $parentCollectionContainer->finalize();
    $titleCollectionContainer->finalize();
  }
}
