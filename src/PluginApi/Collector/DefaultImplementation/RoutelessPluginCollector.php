<?php

namespace Drupal\crumbs\PluginApi\Collector\DefaultImplementation;

use Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface;
use Drupal\crumbs\PluginApi\PluginOffset\PluginOffset;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\EntityPluginCollection;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollectionInterface;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollection;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\TreeCollection;

/**
 * Plugin collections by route.
 */
class RoutelessPluginCollector extends PluginCollector implements RoutelessPluginCollectorInterface {

  /**
   * @var PluginCollectionInterface[]
   */
  protected $collections = array();

  /**
   * @var \Drupal\crumbs\PluginSystem\Collection\PluginCollection\EntityPluginCollection
   */
  private $entityPluginCollection;

  /**
   * @var bool
   */
  private $isFindParent;

  /**
   * Constructor.
   *
   * @param bool $isFindParent
   */
  function __construct($isFindParent) {
    parent::__construct(
      $this->createPluginCollection(),
      new TreeCollection());
    $this->entityPluginCollection = new EntityPluginCollection();
    $this->isFindParent = $isFindParent;
  }

  /**
   * @param string $route
   *
   * @return RoutelessPluginCollectorInterface
   */
  function route($route) {
    return new PluginCollector(
      $this->routeGetPluginCollection($route),
      $this->getTreeCollection());
  }

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollectionInterface
   */
  protected function routeGetPluginCollection($route) {
    return isset($this->collections[$route])
      ? $this->collections[$route]
      : $this->collections[$route] = $this->createPluginCollection();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollectionInterface
   */
  protected function createPluginCollection() {
    return new PluginCollection();
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
    $this->entityPluginCollection->entityRoute($entity_type, $route, $bundle_key, $bundle_name);
  }

  /**
   * Register an entity parent/title plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function entityPlugin($key, \crumbs_EntityPlugin $entity_plugin, $types) {
    $this->entityPluginCollection->entityPlugin($key, $entity_plugin, $types);
    return new PluginOffset($this->treeCollection, $key . '.*');
  }

  /**
   * Finalize.
   */
  function finalize() {
    $this->entityPluginCollection->finalize($this, $this->isFindParent);
  }
}
