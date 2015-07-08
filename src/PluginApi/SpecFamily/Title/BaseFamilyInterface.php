<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Title;

/**
 * Base interface for:
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\FamilyInterface
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\RouteInterface
 *
 * Implemented by
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\DefaultImplementation\BasePluginFamily
 *
 * Generates / generated from
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Parent\BaseFamilyInterface
 */
interface BaseFamilyInterface {

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin|\crumbs_MultiPlugin_FindTitleInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function multiPlugin($key, \crumbs_MultiPlugin_FindTitleInterface $plugin);

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   * @param \crumbs_MonoPlugin|\crumbs_MonoPlugin_FindTitleInterface $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function monoPlugin($key, \crumbs_MonoPlugin_FindTitleInterface $plugin);

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function titleCallback($key, $callback);

}
