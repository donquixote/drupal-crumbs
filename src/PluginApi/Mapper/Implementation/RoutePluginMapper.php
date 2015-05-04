<?php

namespace Drupal\crumbs\PluginApi\Mapper\Implementation;

use Drupal\crumbs\PluginApi\Mapper\RoutePluginMapperInterface;

class RoutePluginMapper extends BasePluginMapper implements RoutePluginMapperInterface {

  /**
   * @param string $key
   * @param string $title
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function translateTitle($key, $title) {
    $this->monoPlugin($key, new \crumbs_MonoPlugin_TranslateTitle($title));
  }

  /**
   * @param $key
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  function skipItem($key) {
    $this->monoPlugin($key, new \crumbs_MonoPlugin_SkipItem());
  }

  /**
   * @param string $key
   * @param string $parentPath
   *
   * @return \Drupal\crumbs\PluginApi\PluginOffset\TreeOffsetMetaInterface
   */
  public function fixedParentPath($key, $parentPath) {
    $this->monoPlugin($key, new \crumbs_MonoPlugin_FixedParentPath($parentPath));
  }
}
