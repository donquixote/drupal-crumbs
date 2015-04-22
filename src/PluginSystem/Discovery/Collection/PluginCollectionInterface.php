<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Collection;

interface PluginCollectionInterface extends DescriptionCollectionInterface {

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   * @param string|NULL $route
   */
  function addMonoPlugin($key, \crumbs_MonoPlugin $plugin, $route = NULL);

  /**
   * @param string $key
   *   The plugin key, without the '.*'.
   * @param \crumbs_MultiPlugin $plugin
   * @param string|NULL $route
   */
  function addMultiPlugin($key, \crumbs_MultiPlugin $plugin, $route = NULL);

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status);

}
