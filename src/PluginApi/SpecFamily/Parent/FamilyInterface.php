<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Parent;

/**
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\DefaultImplementation\Family
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\RouteInterface
 */
interface FamilyInterface extends BaseFamilyInterface {

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description);

  /**
   * @return $this
   */
  function disabledByDefault();

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Parent\RouteInterface
   */
  function route($route);

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Parent\FamilyInterface
   */
  function pluginFamily($key);

  /**
   * Register an entity parent plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|string|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function entityParentPlugin($key, $entity_plugin, $types = NULL);

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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function entityParentCallback($key, $callback, $types = NULL);

}
