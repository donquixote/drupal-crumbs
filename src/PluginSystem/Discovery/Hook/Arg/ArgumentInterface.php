<?php
namespace Drupal\crumbs\PluginSystem\Discovery\Hook\Arg;

/**
 * Interface for the argument passed to hook_crumbs_plugins().
 */
interface ArgumentInterface extends \crumbs_InjectedAPI_hookCrumbsPlugins {

  /**
   * @param string $module
   */
  function setModule($module);

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
   * Register an entity parent plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|string|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function entityParentPlugin($key, $entity_plugin = NULL, $types = NULL);

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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function entityParentCallback($key, $callback, $types = NULL);

  /**
   * Register an entity title plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|string|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function entityTitlePlugin($key, $entity_plugin = NULL, $types = NULL);

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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function entityTitleCallback($key, $callback, $types = NULL);

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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   *
   * @throws \Exception
   */
  function monoPlugin($key = NULL, \crumbs_MonoPlugin $plugin = NULL);

  /**
   * Register a "Mono" plugin that is restricted to a specific route.
   *
   * @param string $route
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeMonoPlugin($route, $key = NULL, \crumbs_MonoPlugin $plugin = NULL);

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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key = NULL, \crumbs_MultiPlugin $plugin = NULL);

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeMultiPlugin($route, $key = NULL, \crumbs_MultiPlugin $plugin = NULL);

  /**
   * @param string $route
   * @param string $key
   * @param string $parent_path
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeParentPath($route, $key, $parent_path);

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
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeParentCallback($route, $key, $callback);

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeTranslateTitle($route, $key, $title);

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
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeTitleCallback($route, $key, $callback);

  /**
   * @param string $route
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeSkipItem($route, $key);

  /**
   * Set specific rules as disabled by default.
   *
   * @param array|string $keys
   *   Array of keys, relative to the module name, OR
   *   a single string key, relative to the module name.
   */
  function disabledByDefault($keys = NULL);

  /**
   * @param string $key
   * @param string $description
   */
  function describeFindParent($key, $description);

  /**
   * @param string $key
   * @param string $description
   */
  function describeFindTitle($key, $description);

  /**
   * @param string $key
   * @param string $field_type
   * @param \Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface $plugin
   */
  # function fieldTypeParentPlugin($key, $field_type, FieldTypePluginInterface $plugin);

  /**
   * @param string $key
   * @param string $field_type
   * @param \Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface $plugin
   */
  # function fieldTypeTitlePlugin($key, $field_type, FieldTypePluginInterface $plugin);
}
