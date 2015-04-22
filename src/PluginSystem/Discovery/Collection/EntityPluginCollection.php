<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Collection;

use Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface;

class EntityPluginCollection {

  /**
   * @var array[]
   *   Known routes for entity types, e.g.:
   *   $this->entityRoutes['node/%'] = array('node', 'type', 'Node type');
   */
  protected $entityRoutes = array();

  /**
   * @var array[]
   *   Collection of entity plugins. Format:
   *   $this->entityPlugins[$key] = array($entity_plugin, $types);
   */
  protected $entityPlugins = array();

  /**
   * @var array[]
   *   Format: $[$key] = array($field_type_plugin, $field_type);
   */
  protected $fieldTypePlugins = array();

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $pluginCollection
   * @param bool $isFindParent
   *   TRUE, if this is an entity plugin collection for parent-finding.
   *   FALSE, if it is for title-finding.
   */
  function finalize(RawPluginCollection $pluginCollection, $isFindParent) {
    $build = array();
    foreach ($this->entityPlugins as $key => $y) {
      list($entity_plugin, $types) = $y;
      if (!isset($types)) {
        foreach ($this->entityRoutes as $route => $x) {
          list($entity_type) = $x;
          $build[$entity_type][$key . '.' . $entity_type] = $entity_plugin;
        }
      }
      elseif (is_array($types)) {
        foreach ($types as $entity_type) {
          $build[$entity_type][$key . '.' . $entity_type] = $entity_plugin;
        }
      }
      elseif (is_string($types)) {
        $entity_type = $types;
        $build[$entity_type][$key] = $entity_plugin;
      }
    }

    foreach ($this->entityRoutes as $route => $x) {
      list($entity_type, $bundle_key, $bundle_name) = $x;
      if (!empty($build[$entity_type])) {
        foreach ($build[$entity_type] as $key => $entity_plugin) {
          $plugin = $isFindParent
            ? new \crumbs_MultiPlugin_EntityParent($entity_plugin, $entity_type, $bundle_key, $bundle_name)
            : new \crumbs_MultiPlugin_EntityTitle($entity_plugin, $entity_type, $bundle_key, $bundle_name);
          $pluginCollection->addMultiPlugin($key, $plugin, $route);
          $pluginCollection->addDescription($key . '.*', '<code>' . check_plain($route) . '</code>');
        }
      }
    }
  }

  /**
   * @param string $key
   *   The plugin key under which this callback will be listed on the weights
   *   configuration form.
   * @param \crumbs_EntityPlugin $entity_plugin
   * @param string[]|string|null $types
   *   An array of entity types, or a single entity type, or NULL to allow all
   *   entity types.
   */
  function entityPlugin($key, \crumbs_EntityPlugin $entity_plugin, $types) {
    if ($entity_plugin instanceof \crumbs_EntityPlugin) {
      $this->entityPlugins[$key] = array($entity_plugin, $types);
    }
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
  public function entityRoute($entity_type, $route, $bundle_key, $bundle_name) {
    $this->entityRoutes[$route] = array($entity_type, $bundle_key, $bundle_name);
  }
}
