<?php

namespace Drupal\crumbs\PluginApi\SpecFamily\Title;

/**
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Title\DefaultImplementation\Route
 * @see \Drupal\crumbs\PluginApi\SpecFamily\Parent\RouteInterface
 */
interface RouteInterface extends BaseFamilyInterface {

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* PARENT ONLY * /
  function fixedTitle($key, $title); /* */

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* TITLE ONLY */
  function translateTitle($key, $title); /* */

  /**
   * @param $string
   *
   * @return \Drupal\crumbs\PluginApi\Offset\TreeOffsetMetaInterface
   */ /* TITLE ONLY */
  function skipItem($string); /* */

}
