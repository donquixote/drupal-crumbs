<?php

namespace Drupal\crumbs\PluginApi\Aggregate;

use Drupal\crumbs\PluginSystem\Tree\TreeNode;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;

class EntityPluginAggregate {

  /**
   * @var \crumbs_EntityPlugin
   */
  private $entityPlugin;

  /**
   * @var string[]|null
   */
  private $types;

  /**
   * @param \crumbs_EntityPlugin $entityPlugin
   * @param string[] $types
   */
  function __construct(\crumbs_EntityPlugin $entityPlugin, array $types = NULL) {
    $this->entityPlugin = $entityPlugin;
    $this->types = $types;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $tree
   * @param EntityRouteInterface[] $entityRoutes
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   */
  function finalize(TreeNode $tree, array $entityRoutes, PluginTypeInterface $pluginType) {
    foreach ($entityRoutes as $route => $entityRoute) {
      if (!$entityRoute instanceof EntityRouteInterface) {
        continue;
      }
      $entityType = $entityRoute->getEntityType();
      if (isset($this->types) && !in_array($entityType, $this->types)) {
        continue;
      }
      $plugin = $entityRoute->createPlugin($this->entityPlugin, $pluginType);
      $tree->child($entityType)->setRouteMultiPlugin($route, $plugin);
    }
  }
}
