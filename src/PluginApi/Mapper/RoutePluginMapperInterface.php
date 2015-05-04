<?php

namespace Drupal\crumbs\PluginApi\Mapper;

interface RoutePluginMapperInterface extends BasePluginMapperInterface {

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function translateTitle($key, $title);

  /**
   * @param $string
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function skipItem($string);

  /**
   * @param string $key
   * @param string $parentPath
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  public function fixedParentPath($key, $parentPath);

}
