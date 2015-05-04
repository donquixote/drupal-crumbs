<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface;
use Drupal\crumbs\PluginApi\HookArgument\PluginCollectionArg;

class MockHookCrumbsPlugins implements HookInterface {

  /**
   * @var callable[]
   *   Format: $[$module] = $callback
   */
  private $implementations = array();

  /**
   * @param string $module
   * @param callable $callback
   */
  function addImplementation($module, $callback) {
    if (!is_callable($callback)) {
      throw new \InvalidArgumentException("Callback must be callable.");
    }
    $this->implementations[$module] = $callback;
  }

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $titleCollectionContainer
   */
  function invokeAll(
    PrimaryPluginCollectorInterface $parentCollectionContainer,
    PrimaryPluginCollectorInterface $titleCollectionContainer
  ) {
    foreach ($this->implementations as $module => $callback) {
      $api = new PluginCollectionArg($parentCollectionContainer, $titleCollectionContainer, $module);
      $callback($api);
    }
  }
}
