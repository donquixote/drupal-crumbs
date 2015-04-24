<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook\Arg;

use Drupal\crumbs\PluginSystem\Discovery\Collection\EmptyPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Collection\EntityPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Collection\PluginCollectionCollection;
use Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffset;
use Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface;
use Drupal\crumbs\PluginSystem\Plugin\ParentPluginInterface;
use Drupal\crumbs\PluginSystem\Plugin\TitlePluginInterface;

class PluginCollectionArg implements ArgumentInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Helper
   */
  private $helper;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection
   */
  private $titlePluginCollection;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection
   */
  private $parentPluginCollection;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Collection\EntityPluginCollection
   */
  private $entityParentPluginCollection;

  /**
   * @var \Drupal\crumbs\PluginSystem\Discovery\Collection\EntityPluginCollection
   */
  private $entityTitlePluginCollection;

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $parentPluginCollection
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $titlePluginCollection
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\EntityPluginCollection $entityParentPluginCollection
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\EntityPluginCollection $entityTitlePluginCollection
   */
  function __construct(
    RawPluginCollection $parentPluginCollection,
    RawPluginCollection $titlePluginCollection,
    EntityPluginCollection $entityParentPluginCollection,
    EntityPluginCollection $entityTitlePluginCollection
  ) {
    $this->parentPluginCollection = $parentPluginCollection;
    $this->titlePluginCollection = $titlePluginCollection;
    $this->entityParentPluginCollection = $entityParentPluginCollection;
    $this->entityTitlePluginCollection = $entityTitlePluginCollection;
    $this->helper = new Helper();
  }

  /**
   * @param string $module
   */
  function setModule($module) {
    $this->helper->setModule($module);
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
    $this->entityParentPluginCollection->entityRoute($entity_type, $route, $bundle_key, $bundle_name);
    $this->entityTitlePluginCollection->entityRoute($entity_type, $route, $bundle_key, $bundle_name);
  }

  /**
   * Register an entity parent plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function entityParentPlugin($key, $entity_plugin = NULL, $types = NULL) {
    $entity_plugin = $this->helper->entityPluginFromKey($entity_plugin);
    $key = $this->helper->buildAbsoluteKey($key);
    $this->entityParentPluginCollection->entityPlugin($key, $entity_plugin, $types);
    return new ArgumentOffset($this->parentPluginCollection, $key . '.*');
  }

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
  function entityParentCallback($key, $callback, $types = NULL) {
    $entity_plugin = new \crumbs_EntityPlugin_Callback($callback, $this->helper->getModule(), $key, 'entityParent');
    $key = $this->helper->buildAbsoluteKey($key);
    $this->entityParentPluginCollection->entityPlugin($key, $entity_plugin, $types);
    return new ArgumentOffset($this->parentPluginCollection, $key . '.*');
  }

  /**
   * Register an entity title plugin.
   *
   * @param string $key
   * @param \crumbs_EntityPlugin|NULL $entity_plugin
   * @param string[]|string|NULL $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function entityTitlePlugin($key, $entity_plugin = NULL, $types = NULL) {
    $entity_plugin = $this->helper->entityPluginFromKey($entity_plugin);
    $key = $this->helper->buildAbsoluteKey($key);
    $this->entityTitlePluginCollection->entityPlugin($key, $entity_plugin, $types);
    return new ArgumentOffset($this->titlePluginCollection, $key . '.*');
  }

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
  function entityTitleCallback($key, $callback, $types = NULL) {
    $entity_plugin = new \crumbs_EntityPlugin_Callback($callback, $this->helper->getModule(), $key, 'entityParent');
    $key = $this->helper->buildAbsoluteKey($key);
    $this->entityTitlePluginCollection->entityPlugin($key, $entity_plugin, $types);
    return new ArgumentOffset($this->titlePluginCollection, $key . '.*');
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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   *
   * @throws \Exception
   * @deprecated Use dedicated methods for title and parent plugins.
   */
  function monoPlugin($key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->monoPluginFromKey($key);
    }
    $key = $this->helper->buildAbsoluteKey($key);

    $pluginCollection = $this->pluginGetCollection($plugin);
    $pluginCollection->addMonoPlugin($key, $plugin);
    return new ArgumentOffset($pluginCollection, $key);
  }

  /**
   * Register a "Mono" plugin that is restricted to a specific route.
   *
   * @param string $route
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeMonoPlugin($route, $key = NULL, \crumbs_MonoPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->monoPluginFromKey($key);
    }
    $key = $this->helper->buildAbsoluteKey($key);

    $pluginCollection = $this->pluginGetCollection($plugin);
    $pluginCollection->addMonoPlugin($key, $plugin, $route);
    return new ArgumentOffset($pluginCollection, $key);
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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   *
   * @throws \Exception
   */
  function multiPlugin($key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->multiPluginFromKey($key);
    }
    $key = $this->helper->buildAbsoluteKey($key);

    $pluginCollection = $this->pluginGetCollection($plugin);
    $pluginCollection->addMultiPlugin($key, $plugin);
    return new ArgumentOffset($pluginCollection, $key . '.*');
  }

  /**
   * @param string $route
   * @param string|null $key
   * @param \crumbs_MultiPlugin|null $plugin
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeMultiPlugin($route, $key = NULL, \crumbs_MultiPlugin $plugin = NULL) {
    if (!isset($plugin)) {
      $plugin = $this->helper->multiPluginFromKey($key);
    }
    $key = $this->helper->buildAbsoluteKey($key);

    $pluginCollection = $this->pluginGetCollection($plugin);
    $pluginCollection->addMultiPlugin($key, $plugin, $route);
    return new ArgumentOffset($pluginCollection, $key . '.*');
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $parent_path
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
   */
  function routeParentCallback($route, $key, $callback) {
    return $this->routeMonoPlugin(
      $route,
      $key,
      new \crumbs_MonoPlugin_ParentPathCallback(
        $callback,
        $this->helper->getModule(),
        $key));
  }

  /**
   * @param string $route
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
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
   * @return \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\Offset\ArgumentOffsetInterface
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
        $this->_disabledByDefault($key);
      }
    }
    else {
      $this->_disabledByDefault($keys);
    }
  }

  /**
   * @param string|NULL $key
   */
  protected function _disabledByDefault($key) {
    $key = $this->helper->buildAbsoluteKey($key);
    // At this point we don't know if this is a parent or title plugin, or both.
    $this->parentPluginCollection->setDefaultStatus($key, FALSE);
    $this->titlePluginCollection->setDefaultStatus($key, FALSE);
  }

  /**
   * @param string $key
   * @param string $description
   *
   * @return mixed
   */
  function describeFindParent($key, $description) {
    $key = $this->helper->buildAbsoluteKey($key);
    $this->parentPluginCollection->addDescription($key, $description);
  }

  /**
   * @param string $key
   * @param string $description
   *
   * @return mixed
   */
  function describeFindTitle($key, $description) {
    $key = $this->helper->buildAbsoluteKey($key);
    $this->titlePluginCollection->addDescription($key, $description);
  }

  /**
   * @param string $key
   * @param string $field_type
   * @param \Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface $plugin
   */
  function fieldTypeTitlePlugin($key, $field_type, FieldTypePluginInterface $plugin) {

  }

  /**
   * @param string $key
   * @param string $field_type
   * @param \Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface $plugin
   */
  function fieldTypeParentPlugin($key, $field_type, FieldTypePluginInterface $plugin) {
    // TODO: Implement fieldTypeParentPlugin() method.
  }

  /**
   * @param \crumbs_PluginInterface $plugin
   *
   * @return \Drupal\crumbs\PluginSystem\Discovery\Collection\PluginCollectionInterface
   */
  private function pluginGetCollection(\crumbs_PluginInterface $plugin) {
    /** @var \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection[] $pluginCollections */
    $pluginCollections = array();
    if ($plugin instanceof ParentPluginInterface) {
      $pluginCollections[] = $this->parentPluginCollection;
    }
    if ($plugin instanceof TitlePluginInterface) {
      $pluginCollections[] = $this->titlePluginCollection;
    }
    switch (count($pluginCollections)) {
      case 0:
        dpm($plugin);
        return new EmptyPluginCollection();
      case 1:
        return $pluginCollections[0];
      default:
        return new PluginCollectionCollection($pluginCollections);
    }
  }
}
