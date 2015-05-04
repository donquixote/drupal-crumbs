<?php

namespace Drupal\crumbs\PluginApi\Mapper\Implementation;

use Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface;
use Drupal\crumbs\PluginApi\Mapper\BasePluginMapperInterface;
use Drupal\crumbs\PluginSystem\Callback\CallbackWrapperInterface;
use Drupal\crumbs\PluginSystem\Plugin\ParentPluginInterface;
use Drupal\crumbs\PluginSystem\Plugin\TitlePluginInterface;

class BasePluginMapper implements BasePluginMapperInterface {

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected $parentCollectionContainer;

  /**
   * @var \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected $titleCollectionContainer;

  /**
   * @var \Drupal\crumbs\PluginSystem\Callback\CallbackWrapperInterface
   */
  protected $callbackWrapper;

  /**
   * @var string
   */
  protected $prefix;

  /**
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
    $this->parentCollectionContainer = $parentCollectionContainer;
    $this->titleCollectionContainer = $titleCollectionContainer;
    $this->callbackWrapper = $callbackWrapper;
    $this->prefix = $prefix;
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @return \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface
   */
  protected function pluginGetCollectionContainer(\crumbs_PluginInterface $plugin) {
    if ($plugin instanceof ParentPluginInterface) {
      return $this->parentCollectionContainer;
    }
    elseif ($plugin instanceof TitlePluginInterface) {
      return $this->titleCollectionContainer;
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
    $plugin = $this->callbackWrapper->wrapParentCallback($callback, $key);
    return $this->monoPlugin($key, $plugin);
  }

  /**
   * @param string $key
   * @param callable $callback
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function titleCallback($key, $callback) {
    $key = $this->prefix . $key;
    $plugin = $this->callbackWrapper->wrapTitleCallback($callback, $key);
    return $this->monoPlugin($key, $plugin);
  }
}
