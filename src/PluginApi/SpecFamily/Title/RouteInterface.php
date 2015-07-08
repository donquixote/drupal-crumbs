<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Title;

/**
 * @see Route
 */
interface RouteInterface extends BaseFamilyInterface {

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */
  function fixedTitle($key, $title);

}
