<?php

namespace Drupal\crumbs\PluginApi\HookArgument;

use Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface;
use Drupal\crumbs\PluginSystem\Callback\CallbackWrapper;
use Drupal\crumbs\PluginApi\Mapper\Implementation\PrimaryPluginMapper;

class PluginCollectionArg extends PrimaryPluginMapper implements ArgumentInterface {

  /**
   * @var string|NULL
   */
  private $module;

  /**
   * @var Helper
   */
  private $helper;

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\PluginCollectorInterface $titleCollectionContainer
   * @param string $module
   */
  function __construct(
    PluginCollectorInterface $parentCollectionContainer,
    PluginCollectorInterface $titleCollectionContainer,
    $module
  ) {
    parent::__construct(
      $parentCollectionContainer,
      $titleCollectionContainer,
      new CallbackWrapper($module),
      $module . '-');
    $this->helper = new Helper($module);
  }

  /**
   * @return \Drupal\crumbs\PluginApi\Mapper\PluginFamilyInterface
   */
  function modulePluginFamily() {
    if (!isset($this->module)) {
      throw new \RuntimeException('Module not initialized.');
    }
    return $this->pluginFamily($this->module);
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
    $this->parentCollectionContainer->entityRoute($entity_type, $route, $bundle_key, $bundle_name);
    $this->titleCollectionContainer->entityRoute($entity_type, $route, $bundle_key, $bundle_name);
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
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   * @deprecated Use dedicated methods for title and parent plugins.
   */
  function monoPlugin($key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->monoPluginFromKey($key);
    }
    return parent::monoPlugin($key, $plugin);
  }

  /**
   * Register a "Mono" plugin that is restricted to a specific route.
   *
   * @param string $route
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeMonoPlugin($route, $key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->monoPluginFromKey($key);
    }
    return $this->route($route)->monoPlugin($key, $plugin);
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
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->multiPluginFromKey($key);
    }
    return parent::multiPlugin($key, $plugin);
  }

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeMultiPlugin($route, $key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->multiPluginFromKey($key);
    }
    return $this->route($route)->multiPlugin($key, $plugin);
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $parent_path
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeParentPath($route, $key, $parent_path) {
    $plugin = new \crumbs_MonoPlugin_FixedParentPath($parent_path);
    return $this->routeMonoPlugin($route, $key, $plugin)
      ->describe('<code>' . check_plain($parent_path) . '</code>' . ' &raquo; '
        . '<code>' . check_plain($route) . '</code>');
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
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeParentCallback($route, $key, $callback) {
    return $this->route($route)->parentCallback($key, $callback);
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeTranslateTitle($route, $key, $title) {
    $plugin = new \crumbs_MonoPlugin_TranslateTitle($title);
    return $this->routeMonoPlugin($route, $key, $plugin)
      ->translateDescription("Title t('@title') for route '@route'", array(
        '@title' => $title,
        '@route' => $route,
      ));
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
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeTitleCallback($route, $key, $callback) {
    return $this->routeMonoPlugin(
      $route,
      $key,
      new \crumbs_MonoPlugin_TitleCallback(
        $callback,
        $this->helper->getModule(),
        $key));
  }

  /**
   * @param string $route
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function routeSkipItem($route, $key) {
    return $this->routeMonoPlugin($route, $key, new \crumbs_MonoPlugin_SkipItem())
      ->translateDescription('Skip links with route !route', array(
        '!route' => '<code>' . check_plain($route) . '</code>',
      ));
  }

  /**
   * Set specific rules as disabled by default.
   *
   * @param array|string $keys
   *   Array of keys, relative to the module name, OR
   *   a single string key, relative to the module name.
   */
  function disabledByDefault($keys = NULL) {
    if (is_array($keys)) {
      foreach ($keys as $key) {
        $this->pluginFamily($key)->disabledByDefault();
      }
    }
    elseif (isset($keys)) {
      $this->pluginFamily($keys)->disabledByDefault();
    }
    else {
      $this->modulePluginFamily()->disabledByDefault();
    }
  }
}
