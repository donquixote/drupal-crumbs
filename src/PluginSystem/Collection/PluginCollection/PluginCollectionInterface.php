<?php

namespace Drupal\crumbs\PluginSystem\Collection\PluginCollection;

interface PluginCollectionInterface {

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin $plugin
   */
  function addMonoPlugin($key, \crumbs_MonoPlugin $plugin);

  /**
   * @param string $key
   *   The plugin key, without the '.*'.
   * @param \crumbs_MultiPlugin $plugin
   */
  function addMultiPlugin($key, \crumbs_MultiPlugin $plugin);

}
