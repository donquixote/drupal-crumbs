<?php

namespace Drupal\crumbs\PluginApi\Family\DefaultImplementation;

use Drupal\crumbs\PluginApi\Family\FamilyInterface;

class Family extends BasePluginFamily implements FamilyInterface {

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginApi\Family\RouteInterface
   */
  function route($route) {
    return new Route($this->getFindParentTreeNode(), $this->getFindTitleTreeNode(), $route);
  }

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\Family\LoreFamilyInterface
   */
  function pluginFamily($key) {
    return new LoreFamily(
      $this->getFindParentTreeNode()->child($key, FALSE),
      $this->getFindTitleTreeNode()->child($key, FALSE));
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function entityParentPlugin($key, $entity_plugin, $types = NULL) {
    if (is_string($types)) {
      $types = array($types);
    }
    return $this->getFindParentTreeNode()
      ->child($key, FALSE)
      ->setEntityPlugin($entity_plugin, $types)
      ->offset();
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function entityParentCallback($key, $callback, $types = NULL) {
    // @todo Create the callback plugin object.
    /** @var \crumbs_EntityPlugin $entity_plugin */
    return $this->entityParentPlugin($key, $entity_plugin, $types);
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function entityTitlePlugin($key, $entity_plugin = NULL, $types = NULL) {
    if (is_string($types)) {
      $types = array($types);
    }
    return $this->getFindTitleTreeNode()
      ->child($key, FALSE)
      ->setEntityPlugin($entity_plugin, $types)
      ->offset();
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function entityTitleCallback($key, $callback, $types = NULL) {
    // @todo Create the callback plugin object.
    /** @var \crumbs_EntityPlugin $entity_plugin */
    return $this->entityTitlePlugin($key, $entity_plugin, $types);
  }
}
