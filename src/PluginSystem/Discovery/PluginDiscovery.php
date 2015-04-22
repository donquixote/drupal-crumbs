<?php

namespace Drupal\crumbs\PluginSystem\Discovery;

use Drupal\crumbs\PluginSystem\Discovery\Collection\EntityPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\PluginCollectionArg;
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
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $parentPluginCollection
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $titlePluginCollection
   */
  function discoverPlugins(
    RawPluginCollection $parentPluginCollection,
    RawPluginCollection $titlePluginCollection
  ) {
    $entityParentPluginCollection = new EntityPluginCollection();
    $entityTitlePluginCollection = new EntityPluginCollection();

    $api = new PluginCollectionArg(
      $parentPluginCollection,
      $titlePluginCollection,
      $entityParentPluginCollection,
      $entityTitlePluginCollection);

    $this->hook->invokeAll($api);

    $entityParentPluginCollection->finalize($parentPluginCollection, TRUE);
    $entityTitlePluginCollection->finalize($titlePluginCollection, FALSE);
  }
}
