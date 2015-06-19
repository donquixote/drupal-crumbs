<?php

namespace Drupal\crumbs\PluginApi\Mapper\CallbackRecovery;

use Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface;
use Drupal\crumbs\PluginApi\Mapper\BasePluginMapperInterface;
use Drupal\crumbs\PluginApi\PluginOffset\DummyOffset;

class BaseCallbackMapper implements BasePluginMapperInterface {

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected $parentPluginCollector;

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected $titlePluginCollector;

  /**
   * @var string
   */
  protected $prefix;

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $parentPluginCollector
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $titlePluginCollector
   * @param string $prefix
   */
  function __construct(
    PluginCollectorInterface $parentPluginCollector,
    PluginCollectorInterface $titlePluginCollector,
    $prefix
  ) {
    $this->parentPluginCollector = $parentPluginCollector;
    $this->titlePluginCollector = $titlePluginCollector;
    $this->prefix = $prefix;
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
    // Ignore this plugin, knowing that it is already cached.
    return new DummyOffset();
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
    // Ignore this plugin, knowing that it is already cached.
    return new DummyOffset();
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function parentCallback($key, $callback) {
    $key = $this->prefix . $key;
    $plugin = new \crumbs_MonoPlugin_ParentPathCallback($callback);
    $this->parentPluginCollector->monoPlugin($key, $plugin);
    return new DummyOffset();
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function titleCallback($key, $callback) {
    $key = $this->prefix . $key;
    $plugin = new \crumbs_MonoPlugin_TitleCallback($callback);
    $this->titlePluginCollector ->monoPlugin($key, $plugin);
    return new DummyOffset();
  }

}
