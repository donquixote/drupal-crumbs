<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Parent;

/**
 * @see Route
 */
interface RouteInterface extends BaseFamilyInterface {

  /**
   * @param string $key
   * @param string $parentPath
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function fixedParentPath($key, $parentPath);

}
