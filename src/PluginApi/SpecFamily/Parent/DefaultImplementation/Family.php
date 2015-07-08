<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Parent\DefaultImplementation;

use Drupal\crumbs\PluginApi\SpecFamily\Parent\FamilyInterface;

class Family extends BaseFamily implements FamilyInterface {

  /**
   * Set specific rules as disabled by default.
   *
   * @return $this
   */
  function disabledByDefault() {
    $this->getTreeNode()->disabledByDefault();
    return $this;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  function describe($description) {
    $this->getTreeNode()->describe($description);
    return $this;
  }

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Parent\RouteInterface
   */
  function route($route) {
    return new Route($this->getTreeNode(), $route);
  }

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Parent\FamilyInterface
   */
  function pluginFamily($key) {
    return new Family($this->getTreeNode()->child($key, FALSE));
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
    return $this->getTreeNode()
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

}
