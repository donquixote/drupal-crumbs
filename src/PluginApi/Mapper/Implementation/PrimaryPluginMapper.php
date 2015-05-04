<?php

namespace Drupal\crumbs\PluginApi\Mapper\Implementation;

use Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface;
use Drupal\crumbs\PluginApi\Mapper\PrimaryPluginMapperInterface;
use Drupal\crumbs\PluginSystem\Callback\CallbackWrapperInterface;

class PrimaryPluginMapper extends BasePluginMapper implements PrimaryPluginMapperInterface {

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface
   */
  protected $parentCollectionContainer;

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface
   */
  protected $titleCollectionContainer;

  /**
   * Constructor.
   * Overrides parent constructor signature, narrowing down the types.
   *
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $titleCollectionContainer
   * @param \Drupal\crumbs\PluginSystem\Callback\CallbackWrapperInterface $callbackWrapper
   * @param string $prefix
   */
  function __construct(
    PluginCollectorInterface $parentCollectionContainer,
    PluginCollectorInterface $titleCollectionContainer,
    CallbackWrapperInterface $callbackWrapper,
    $prefix
  ) {
    parent::__construct(
      $parentCollectionContainer,
      $titleCollectionContainer,
      $callbackWrapper,
      $prefix);
  }

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginApi\Mapper\BasePluginMapperInterface
   */
  function route($route) {
    return (new RoutePluginMapper(
      $this->parentCollectionContainer->route($route),
      $this->titleCollectionContainer->route($route),
      $this->callbackWrapper,
      $this->prefix));
  }

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\Mapper\PluginFamilyInterface
   */
  function pluginFamily($key) {
    return (new PluginFamilyMapper(
      $this->parentCollectionContainer,
      $this->titleCollectionContainer,
      $this->callbackWrapper,
      $this->prefix . $key . '.'));
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
    return $this->parentCollectionContainer->entityPlugin($key, $entity_plugin, $types);
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
    $entity_plugin = $this->callbackWrapper->wrapEntityParentCallback($callback, $key);
    return $this->parentCollectionContainer->entityPlugin($key, $entity_plugin, $types);
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
    $this->titleCollectionContainer->entityPlugin($key, $entity_plugin, $types);
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
    $entity_plugin = $this->callbackWrapper->wrapEntityTitleCallback($callback, $key);
    return $this->titleCollectionContainer->entityPlugin($key, $entity_plugin, $types);
  }
}
