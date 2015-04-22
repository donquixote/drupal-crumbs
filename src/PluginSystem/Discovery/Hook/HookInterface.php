<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

interface HookInterface {

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
   */
  function invokeAll(ArgumentInterface $api);
}
