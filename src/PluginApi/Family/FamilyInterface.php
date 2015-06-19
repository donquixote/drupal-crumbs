<?php

namespace Drupal\crumbs\PluginApi\Family;

interface FamilyInterface extends BaseFamilyInterface, EntityFamilyInterface {

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginApi\Family\RouteInterface
   */
  function route($route);

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\Family\FamilyLoreInterface
   */
  function pluginFamily($key);

}
