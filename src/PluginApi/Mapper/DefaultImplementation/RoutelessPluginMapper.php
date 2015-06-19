<?php

namespace Drupal\crumbs\PluginApi\Mapper\DefaultImplementation;

use Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface;
use Drupal\crumbs\PluginApi\Mapper\RoutelessPluginMapperInterface;

class RoutelessPluginMapper extends BasePluginMapper implements RoutelessPluginMapperInterface {

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface
   */
  protected $parentPluginCollector;

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface
   */
  protected $titlePluginCollector;

  /**
   * Constructor.
   * Overrides parent constructor signature, narrowing down the types.
   *
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $parentPluginCollector
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $titlePluginCollector
   * @param bool $hasUncachablePlugins
   * @param string $prefix
   */
  function __construct(
    RoutelessPluginCollectorInterface $parentPluginCollector,
    RoutelessPluginCollectorInterface $titlePluginCollector,
    &$hasUncachablePlugins,
    $prefix
  ) {
    parent::__construct(
      $parentPluginCollector,
      $titlePluginCollector,
      $hasUncachablePlugins,
      $prefix);
  }

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginApi\Mapper\RoutePluginMapperInterface
   */
  function route($route) {
    return new RoutePluginMapper(
      $this->parentPluginCollector->route($route),
      $this->titlePluginCollector->route($route),
      $this->hasUncachablePlugins,
      $this->prefix);
  }

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\Mapper\PluginFamilyInterface
   */
  function pluginFamily($key) {
    return new PluginFamilyMapper(
      $this->parentPluginCollector,
      $this->titlePluginCollector,
      $this->hasUncachablePlugins,
      $this->prefix . $key . '.');
  }

  /**
   * Register an entity parent plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function entityParentPlugin($key, $entity_plugin, $types = NULL) {
    $key = $this->prefix . $key;
    return $this->parentPluginCollector->entityPlugin($key, $entity_plugin, $types);
  }

  /**
   * Register a callback that will determine a parent path for a breadcrumb item
   * with an entity route. The behavior will be available for all known entity
   * routes, e.g. node/% or taxonomy/term/%, with different plugin keys.
   *
   * @param string $key
   * @param callable $callback
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function entityParentCallback($key, $callback, $types = NULL) {
    $key = $this->prefix . $key;
    $this->hasUncachablePlugins = TRUE;
    // Ignore this plugin, because it is not cacheable.
    return $this->parentPluginCollector->pluginOffset($key . '.*');
  }

  /**
   * Register an entity title plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function entityTitlePlugin($key, $entity_plugin = NULL, $types = NULL) {
    $key = $this->prefix . $key;
    $this->titlePluginCollector->entityPlugin($key, $entity_plugin, $types);
  }

  /**
   * Register a callback that will determine a title for a breadcrumb item with
   * an entity route. The behavior will be available for all known entity
   * routes, e.g. node/% or taxonomy/term/%, with different plugin keys.
   *
   * @param string $key
   *   The plugin key under which this callback will be listed on the weights
   *   configuration form.
   * @param callback $callback
   *   The callback, e.g. an anonymous function. The signature must be
   *   $callback(stdClass $entity, string $entity_type, string
   *   $distinction_key), like the findCandidate() method of a typical
   *   crumbs_EntityPlugin.
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function entityTitleCallback($key, $callback, $types = NULL) {
    $key = $this->prefix . $key;
    $this->hasUncachablePlugins = TRUE;
    // Ignore this plugin, because it is not cacheable.
    return $this->titlePluginCollector->pluginOffset($key . '.*');
  }
}
