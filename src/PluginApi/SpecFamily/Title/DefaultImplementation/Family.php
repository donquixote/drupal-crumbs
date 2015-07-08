<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Title\DefaultImplementation;

use Drupal\crumbs\PluginApi\SpecFamily\Title\FamilyInterface;

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
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Title\RouteInterface
   */
  function route($route) {
    return new Route($this->getTreeNode(), $route);
  }

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\SpecFamily\Title\FamilyInterface
   */
  function pluginFamily($key) {
    return new Family($this->getTreeNode()->child($key, FALSE));
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
  function entityTitlePlugin($key, $entity_plugin, $types = NULL) {
    if (is_string($types)) {
      $types = array($types);
    }
    return $this->getTreeNode()
      ->child($key, FALSE)
      ->setEntityPlugin($entity_plugin, $types)
      ->offset();
  }

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
  function entityTitleCallback($key, $callback, $types = NULL) {
    // @todo Create the callback plugin object.
    /** @var \crumbs_EntityPlugin $entity_plugin */
    return $this->entityTitlePlugin($key, $entity_plugin, $types);
  }

}
