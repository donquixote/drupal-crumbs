<?php

namespace Drupal\crumbs\PluginSystem\Collection\PluginCollection;

interface TreeCollectionInterface extends DescriptionCollectionInterface {

  /**
   * @param string $key
   * @param bool $status
   */
  function setDefaultStatus($key, $status);

}
