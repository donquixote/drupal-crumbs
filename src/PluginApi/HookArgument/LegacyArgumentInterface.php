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
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeMonoPlugin($route, $key = NULL, \crumbs_MonoPlugin $plugin = NULL);

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeMultiPlugin($route, $key = NULL, \crumbs_MultiPlugin $plugin = NULL);

  /**
   * @param string $route
   * @param string $key
   * @param string $parent_path
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
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
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeParentCallback($route, $key, $callback);

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
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
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeTitleCallback($route, $key, $callback);

  /**
   * @param string $route
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeSkipItem($route, $key);

}
