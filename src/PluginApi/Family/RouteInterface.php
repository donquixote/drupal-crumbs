<?php

namespace Drupal\crumbs\PluginApi\Family;

interface RouteInterface extends BaseFamilyInterface {

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function translateTitle($key, $title);

  /**
   * @param $string
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function skipItem($string);

  /**
   * @param string $key
   * @param string $parentPath
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function fixedParentPath($key, $parentPath);

}
