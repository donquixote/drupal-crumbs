<?php

namespace Drupal\crumbs\PluginApi\HookArgument;

interface LegacyArgumentInterface {

  /**
   * Register a "Mono" plugin that is restricted to a specific route.
   *
   * @param string $route
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeMonoPlugin($route, $key = NULL, \crumbs_MonoPlugin $plugin = NULL);

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeMultiPlugin($route, $key = NULL, \crumbs_MultiPlugin $plugin = NULL);

  /**
   * @param string $route
   * @param string $key
   * @param string $parent_path
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeParentCallback($route, $key, $callback);

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeTitleCallback($route, $key, $callback);

  /**
   * @param string $route
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeSkipItem($route, $key);

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
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * Overrides the parent method, to make the arguments optional.
   * (for backwards compatibility with older versions of Crumbs)
   *
   * @param string $key
   *   Rule key, relative to module name.
   * @param \crumbs_MonoPlugin $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function monoPlugin($key = NULL, \crumbs_MonoPlugin $plugin = NULL);

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * Overrides the parent method, to make the arguments optional.
   * (for backwards compatibility with older versions of Crumbs)
   *
   * @param string|null $key
   *   Rule key, relative to module name.
   * @param \crumbs_MultiPlugin|null $plugin
   *   Plugin object. Needs to implement crumbs_MultiPlugin.
   *   Or NULL, to have the plugin object automatically created based on a
   *   class name guessed from the $key parameter and the module name.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key = NULL, \crumbs_MultiPlugin $plugin = NULL);

}
