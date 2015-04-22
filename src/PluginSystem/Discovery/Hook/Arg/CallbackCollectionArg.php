<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook\Arg;

use Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface;

class CallbackCollectionArg implements ArgumentInterface {

  /**
   * @var string
   */
  private $module;

  /**
   * @var \crumbs_InjectedAPI_Collection_CallbackCollection
   */
  private $callbackCollection;

  /**
   * @param \crumbs_InjectedAPI_Collection_CallbackCollection $callbackCollection
   */
  function __construct(\crumbs_InjectedAPI_Collection_CallbackCollection $callbackCollection) {
    $this->callbackCollection = $callbackCollection;
  }

  /**
   * @param string $module
   */
  function setModule($module) {
    $this->module = $module;
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
    // Do nothing, because this is not a callback.
  }

  /**
   * Register an entity parent plugin.
   *
   * @param string $key
   * @param string|\crumbs_EntityPlugin $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   */
  function entityParentPlugin($key, $entity_plugin = NULL, $types = NULL) {
    // Do nothing, because this is not a callback.
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
   */
  function entityParentCallback($key, $callback, $types = NULL) {
    $this->callbackCollection->addCallback($this->module, 'entityParent', $key, $callback);
  }

  /**
   * Register an entity title plugin.
   *
   * @param string $key
   * @param string|\crumbs_EntityPlugin $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   */
  function entityTitlePlugin($key, $entity_plugin = NULL, $types = NULL) {
    // Do nothing, because this is not a callback.
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
   */
  function entityTitleCallback($key, $callback, $types = NULL) {
    $this->callbackCollection->addCallback($this->module, 'entityTitle', $key, $callback);
  }

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * @param string $key
   *   Rule key, relative to module name.
   * @param \crumbs_MonoPlugin $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   *
   * @throws \Exception
   */
  function monoPlugin($key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    // Do nothing, because this is not a callback.
  }

  /**
   * Register a "Mono" plugin that is restricted to a specific route.
   *
   * @param string $route
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   */
  function routeMonoPlugin($route, $key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    // Do nothing, because this is not a callback.
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string|null $key
   *   Rule key, relative to module name.
   * @param \crumbs_MultiPlugin|null $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   *
   * @throws \Exception
   */
  function multiPlugin($key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    // Do nothing, because this is not a callback.
  }

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   */
  function routeMultiPlugin($route, $key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    // Do nothing, because this is not a callback.
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $parent_path
   */
  function routeParentPath($route, $key, $parent_path) {
    // Do nothing, because this is not a callback.
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
   */
  function routeParentCallback($route, $key, $callback) {
    $this->callbackCollection->addCallback($this->module, 'routeParent', $key, $callback);
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   */
  function routeTranslateTitle($route, $key, $title) {
    // Do nothing, because this is not a callback.
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
   */
  function routeTitleCallback($route, $key, $callback) {
    $this->callbackCollection->addCallback($this->module, 'routeTitle', $key, $callback);
  }

  /**
   * @param string $route
   * @param string $key
   */
  function routeSkipItem($route, $key) {
    // Do nothing, because this is not a callback.
  }

  /**
   * Set specific rules as disabled by default.
   *
   * @param array|string $keys
   *   Array of keys, relative to the module name, OR
   *   a single string key, relative to the module name.
   */
  function disabledByDefault($keys = NULL) {
    // Do nothing, because this is not a callback.
  }

  /**
   * @param string $key
   * @param string $description
   *
   * @return mixed
   */
  function describe($key, $description) {
    // Do nothing, because this is not a callback.
  }

  /**
   * @param string $key
   * @param string $description
   */
  function describeFindParent($key, $description) {
    // Do nothing.
  }

  /**
   * @param string $key
   * @param string $description
   */
  function describeFindTitle($key, $description) {
    // Do nothing.
  }

  /**
   * @param string $key
   * @param string $field_type
   * @param \Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface $plugin
   */
  function fieldTypeTitlePlugin($key, $field_type, FieldTypePluginInterface $plugin) {
    // Do nothing, because this is not a callback.
  }
}
