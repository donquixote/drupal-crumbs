<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface;
use Drupal\crumbs\PluginApi\Mapper\DefaultImplementation\HookArgument;

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
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $titleCollectionContainer
   * @param bool[] $uncachableModules
   */
  function invokeAll(
    RoutelessPluginCollectorInterface $parentCollectionContainer,
    RoutelessPluginCollectorInterface $titleCollectionContainer,
    array &$uncachableModules
  ) {
    foreach ($this->implementations as $module => $callback) {
      $hasUncacheablePlugins = FALSE;
      $api = new HookArgument(
        $parentCollectionContainer,
        $titleCollectionContainer,
        $hasUncacheablePlugins,
        $module);
      $callback($api);
      if ($hasUncacheablePlugins) {
        $uncachableModules[$module] = TRUE;
      }
    }
  }
}
