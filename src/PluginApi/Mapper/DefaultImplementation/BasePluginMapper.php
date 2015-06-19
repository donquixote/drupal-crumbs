<?php

namespace Drupal\crumbs\PluginApi\Mapper\DefaultImplementation;

use Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface;
use Drupal\crumbs\PluginApi\Mapper\BasePluginMapperInterface;
use Drupal\crumbs\PluginSystem\Plugin\ParentPluginInterface;
use Drupal\crumbs\PluginSystem\Plugin\TitlePluginInterface;

class BasePluginMapper implements BasePluginMapperInterface {

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected $parentPluginCollector;

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected $titlePluginCollector;

  /**
   * @var bool
   */
  protected $hasUncachablePlugins;

  /**
   * @var string
   */
  protected $prefix;

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $parentPluginCollector
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $titlePluginCollector
   * @param bool $hasUncachablePlugins
   *   By-reference flag.
   * @param string $prefix
   */
  function __construct(
    PluginCollectorInterface $parentPluginCollector,
    PluginCollectorInterface $titlePluginCollector,
    &$hasUncachablePlugins,
    $prefix
  ) {
    $this->parentPluginCollector = $parentPluginCollector;
    $this->titlePluginCollector = $titlePluginCollector;
    $this->hasUncachablePlugins =& $hasUncachablePlugins;
    $this->prefix = $prefix;
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @return \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected function pluginGetCollectionContainer(\crumbs_PluginInterface $plugin) {
    if ($plugin instanceof ParentPluginInterface) {
      return $this->parentPluginCollector;
    }
    elseif ($plugin instanceof TitlePluginInterface) {
      return $this->titlePluginCollector;
    }
    else {
      throw new \InvalidArgumentException("Invalid plugin type.");
    }
  }

  /**
   * Register a "Multi" plugin.
   * That is, a plugin that defines more than one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   *   A ".*" will be appended to form a wildcard key.
   * @param \crumbs_MultiPlugin $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key, \crumbs_MultiPlugin $plugin) {
    return $this->pluginGetCollectionContainer($plugin)->multiPlugin($key, $plugin);
  }

  /**
   * Register a "Mono" plugin.
   * That is, a plugin that defines exactly one rule.
   *
   * @param string $key
   *   Plugin key, relative to module name.
   * @param \crumbs_MonoPlugin $plugin
   *   Plugin object.
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function monoPlugin($key, \crumbs_MonoPlugin $plugin) {
    return $this->pluginGetCollectionContainer($plugin)->monoPlugin($key, $plugin);
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function parentCallback($key, $callback) {
    $key = $this->prefix . $key;
    $this->hasUncachablePlugins = TRUE;
    // Ignore this plugin, since it is uncachable.
    return $this->parentPluginCollector->pluginOffset($key);
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function titleCallback($key, $callback) {
    $key = $this->prefix . $key;
    $this->hasUncachablePlugins = TRUE;
    // Ignore this plugin, since it is uncachable.
    return $this->titlePluginCollector->pluginOffset($key);
  }
}
