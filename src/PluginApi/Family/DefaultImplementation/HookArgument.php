<?php

namespace Drupal\crumbs\PluginApi\Family\DefaultImplementation;

use Drupal\crumbs\PluginApi\Aggregate\EntityRoute;
use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;
use Drupal\crumbs\PluginApi\HookArgument\Helper;
use Drupal\crumbs\PluginApi\HookArgument\LegacyArgumentInterface;
use Drupal\crumbs\PluginApi\Offset\IllegalTreeOffset;
use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\Plugin\LegacyWrapper\MonoParentPluginLegacyWrapper;
use Drupal\crumbs\PluginSystem\Plugin\LegacyWrapper\MonoTitlePluginLegacyWrapper;
use Drupal\crumbs\PluginSystem\Plugin\LegacyWrapper\MultiParentPluginLegacyWrapper;
use Drupal\crumbs\PluginSystem\Plugin\LegacyWrapper\MultiTitlePluginLegacyWrapper;
use Drupal\crumbs\Util;

/**
 * Argument to be passed into hook_crumbs_plugins()
 *
 * @see hook_crumbs_plugins()
 */
class HookArgument extends LoreFamily implements ArgumentInterface, LegacyArgumentInterface {

  /**
   * @var string
   */
  private $module;

  /**
   * @var Helper
   */
  private $helper;

  /**
   * Known routes for entity types.
   *
   * @var \Drupal\crumbs\PluginApi\Aggregate\EntityRouteInterface[]
   *   Format: $[$route] = new EntityRoute();
   */
  protected $entityRoutes = array();

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findParentTreeNode
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findTitleTreeNode
   * @param string $module
   */
  function __construct(TreeNode $findParentTreeNode, TreeNode $findTitleTreeNode, $module) {
    parent::__construct($findParentTreeNode, $findTitleTreeNode);
    $this->module = $module;
    $this->helper = new Helper($module);
  }

  /**
   * @return array[]
   *   Format: $[$route] = array($entity_type, $bundle_key, $entity_type_label);
   */
  function getEntityRoutes() {
    return $this->entityRoutes;
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
    $this->entityRoutes[$route] = new EntityRoute($entity_type, $bundle_key, $bundle_name);
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   * @deprecated Use dedicated methods for title and parent plugins.
   */
  function monoPlugin($key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->monoPluginFromKey($key);
    }
    if ($plugin instanceof \crumbs_MonoPlugin_FindParentInterface) {
      return $this->getFindParentTreeNode()
        ->child($key, TRUE)
        ->setMonoPlugin($plugin)
        ->offset();
    }
    elseif ($plugin instanceof \crumbs_MonoPlugin_FindTitleInterface) {
      return $this->getFindTitleTreeNode()
        ->child($key, TRUE)
        ->setMonoPlugin($plugin)
        ->offset();
    }
    else {
      $reflectionObject = new \ReflectionObject($plugin);
      foreach ($reflectionObject->getMethods() as $method) {
        if ('findParent' === $method->name) {
          $wrapper = new MonoParentPluginLegacyWrapper($plugin, $method->name);
          parent::monoPlugin($key, $wrapper);
        }
        elseif ('findTitle' === $method->name) {
          $wrapper = new MonoTitlePluginLegacyWrapper($plugin, $method->name);
          parent::monoPlugin($key, $wrapper);
        }
        elseif (0 === strpos($method->name, 'findParent__')) {
          $wrapper = new MonoParentPluginLegacyWrapper($plugin, $method->name);
          $route = Util::routeFromMethodSuffix(substr($method->name, 12));
          parent::route($route)->monoPlugin($key, $wrapper);
        }
        elseif (0 === strpos($method->name, 'findTitle__')) {
          $wrapper = new MonoTitlePluginLegacyWrapper($plugin, $method->name);
          $route = Util::routeFromMethodSuffix(substr($method->name, 11));
          parent::route($route)->monoPlugin($key, $wrapper);
        }
      }
      // Return a placeholder tree offset.
      return new IllegalTreeOffset('Cannot use tree offset from legacy plugin.');
    }
  }

  /**
   * Register a "Mono" plugin that is restricted to a specific route.
   *
   * @param string $route
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->multiPluginFromKey($key);
    }
    if ($plugin instanceof \crumbs_MultiPlugin_FindParentInterface) {
      return $this->getFindParentTreeNode()
        ->child($key, FALSE)
        ->setMultiPlugin($plugin)
        ->offset();
    }
    elseif ($plugin instanceof \crumbs_MultiPlugin_FindTitleInterface) {
      return $this->getFindTitleTreeNode()
        ->child($key, FALSE)
        ->setMultiPlugin($plugin)
        ->offset();
    }
    else {
      $reflectionObject = new \ReflectionObject($plugin);
      foreach ($reflectionObject->getMethods() as $method) {
        if ('findParent' === $method->name) {
          $wrapper = new MultiParentPluginLegacyWrapper($plugin, $method->name);
          parent::multiPlugin($key, $wrapper);
        }
        elseif ('findTitle' === $method->name) {
          $wrapper = new MultiTitlePluginLegacyWrapper($plugin, $method->name);
          parent::multiPlugin($key, $wrapper);
        }
        elseif (0 === strpos($method->name, 'findParent__')) {
          $wrapper = new MultiParentPluginLegacyWrapper($plugin, $method->name);
          $route = Util::routeFromMethodSuffix(substr($method->name, 12));
          parent::route($route)->multiPlugin($key, $wrapper);
        }
        elseif (0 === strpos($method->name, 'findTitle__')) {
          $wrapper = new MultiTitlePluginLegacyWrapper($plugin, $method->name);
          $route = Util::routeFromMethodSuffix(substr($method->name, 11));
          parent::route($route)->multiPlugin($key, $wrapper);
        }
      }
      // Return a placeholder tree offset.
      return new IllegalTreeOffset('Cannot use tree offset from legacy plugin.');
    }
  }

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeParentPath($route, $key, $parent_path) {
    return $this->route($route)->fixedParentPath($key, $parent_path);
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeParentCallback($route, $key, $callback) {
    return $this->route($route)->parentCallback($key, $callback);
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeTranslateTitle($route, $key, $title) {
    return $this->route($route)->translateTitle($key, $title);
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
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeTitleCallback($route, $key, $callback) {
    return $this->route($route)->titleCallback($key, $callback);
  }

  /**
   * @param string $route
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function routeSkipItem($route, $key) {
    return $this->route($route)->skipItem($key);
  }

  /**
   * Set specific rules as disabled by default.
   *
   * @param array|string $keys
   *   Array of keys, relative to the module name, OR
   *   a single string key, relative to the module name.
   *
   * @return $this
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
      $this->getFindTitleTreeNode()->disabledByDefault();
      $this->getFindParentTreeNode()->disabledByDefault();
    }
    return $this;
  }
}
