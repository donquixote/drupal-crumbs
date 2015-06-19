<?php

namespace Drupal\crumbs\PluginApi\Family\CallbackRecovery;

use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;
use Drupal\crumbs\PluginApi\PluginOffset\DummyOffset;

class CallbackHookArgument extends RoutelessCallbackMapper implements ArgumentInterface {

  /**
   * @var string|NULL
   */
  private $module;

  /**
   * @return \Drupal\crumbs\PluginApi\Family\FamilyLoreInterface
   */
  function modulePluginFamily() {
    return new CallbackFamilyMapper(
      $this->parentPluginCollector,
      $this->titlePluginCollector,
      $this->module . '.');
  }

  /**
   * Register an entity route.
   * This should be called by those modules that define entity types and routes.
   *
   * @param string $entity_type
   * @param string $route
   * @param string $bundle_key
   * @param string $bundle_name
   */
  function entityRoute($entity_type, $route, $bundle_key, $bundle_name) {
    $this->parentPluginCollector->entityRoute($entity_type, $route, $bundle_key, $bundle_name);
    $this->titlePluginCollector->entityRoute($entity_type, $route, $bundle_key, $bundle_name);
  }

  /**
   * Register a "Mono" plugin that is restricted to a specific route.
   *
   * @param string $route
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeMonoPlugin($route, $key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    // Ignore.
    return new DummyOffset();
  }

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeMultiPlugin($route, $key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    // Ignore.
    return new DummyOffset();
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $parent_path
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeParentPath($route, $key, $parent_path) {
    // Ignore.
    return new DummyOffset();
  }

  /**
   * Register a callback that will determine a parent for a breadcrumb item.
   *
   * @param string $route
   *   The route where this callback should be used, e.g. "node/%".
   * @param string $key
   *   The plugin key under which this callback will be listed on the weights
   *   configuration form.
   * @param callback $callback
   *   The callback, e.g. an anonymous function. The signature must be
   *   $callback(string $path, array $item), like the findParent() method of
   *   a typical crumbs_MonoPlugin.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeParentCallback($route, $key, $callback) {
    return $this->route($route)->parentCallback($key, $callback);
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeTranslateTitle($route, $key, $title) {
    // Ignore.
    return new DummyOffset();
  }

  /**
   * Register a callback that will determine a title for a breadcrumb item.
   *
   * @param string $route
   *   The route where this callback should be used, e.g. "node/%".
   * @param string $key
   *   The plugin key under which this callback will be listed on the weights
   *   configuration form.
   * @param callback $callback
   *   The callback, e.g. an anonymous function. The signature must be
   *   $callback(string $path, array $item), like the findParent() method of
   *   a typical crumbs_MonoPlugin.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeTitleCallback($route, $key, $callback) {
    return $this->route($route)->titleCallback($key, $callback);
  }

  /**
   * @param string $route
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeSkipItem($route, $key) {
    // Ignore.
    return new DummyOffset();
  }
}
