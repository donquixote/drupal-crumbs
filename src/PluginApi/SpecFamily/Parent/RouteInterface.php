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
   */ /* PARENT ONLY */
  function fixedParentPath($key, $parentPath); /* */

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* TITLE ONLY * /
  function translateTitle($key, $title); /* */

  /**
   * @param $string
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* TITLE ONLY * /
  function skipItem($string); /* */

}
