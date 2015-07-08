<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Parent;

/**
 * Base interface for:
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Parent\FamilyInterface
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Parent\RouteInterface
 *
 * Implemented by
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Parent\DefaultImplementation\BasePluginFamily
 *
 * Generates / generated from
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\BaseFamilyInterface
 */
interface BaseFamilyInterface {

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin|\crumbs_MultiPlugin_FindParentInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function multiPlugin($key, \crumbs_MultiPlugin_FindParentInterface $plugin);

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   * @param \crumbs_MonoPlugin|\crumbs_MonoPlugin_FindParentInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function monoPlugin($key, \crumbs_MonoPlugin_FindParentInterface $plugin);

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function parentCallback($key, $callback);

}
