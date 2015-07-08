<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Title;

/**
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Parent\DefaultImplementation\Family
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Parent\RouteInterface
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
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Title\RouteInterface
   */
  function route($route);

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Title\FamilyInterface
   */
  function pluginFamily($key);

  /**
   * Register an entity title plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|string|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function entityTitlePlugin($key, $entity_plugin, $types = NULL);

  /**
   * Register a callback that will determine a title for a breadcrumb item
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
  function entityTitleCallback($key, $callback, $types = NULL);

}
