<?php

namespace Drupal\crumbs\PluginApi\Collector;

interface PrimaryPluginCollectorInterface extends PluginCollectorInterface {

  /**
   * @param string $key
   *
   * @return PluginCollectorInterface
   */
  function route($key);

  /**
   * Register an entity parent plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function entityPlugin($key, \crumbs_EntityPlugin $entity_plugin, $types);

  /**
   * Register an entity route.
   * This should be called by those modules that define entity types and routes.
   *
   * @param string $entity_type
   * @param string $route
   * @param string $bundle_key
   * @param string $bundle_name
   */
  function entityRoute($entity_type, $route, $bundle_key, $bundle_name);

  /**
   * Finalize.
   */
  function finalize();

}
