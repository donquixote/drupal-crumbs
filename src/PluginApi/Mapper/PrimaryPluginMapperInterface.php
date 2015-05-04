<?php

namespace Drupal\crumbs\PluginApi\Mapper;

interface PrimaryPluginMapperInterface extends BasePluginMapperInterface, EntityPluginMapperInterface {

  /**
   * @param string $route
   *
   * @return \Drupal\crumbs\PluginApi\Mapper\RoutePluginMapperInterface
   */
  function route($route);

  /**
   * @param string $key
   *
   * @return \Drupal\crumbs\PluginApi\Mapper\PluginFamilyInterface
   */
  function pluginFamily($key);

}
