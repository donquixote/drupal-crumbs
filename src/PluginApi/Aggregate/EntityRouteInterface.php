<?php

namespace Drupal\crumbs\PluginApi\Aggregate;

use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;

interface EntityRouteInterface {

  /**
   * @return string
   */
  public function getEntityType();

  /**
   * @param \crumbs_EntityPlugin $entityPlugin
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   *
   * @return \crumbs_MultiPlugin
   */
  public function createPlugin(\crumbs_EntityPlugin $entityPlugin, PluginTypeInterface $pluginType);

}
