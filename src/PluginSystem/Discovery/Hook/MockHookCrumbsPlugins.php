<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

class MockHookCrumbsPlugins implements HookInterface {

  /**
   * @var callable
   */
  private $callback;

  /**
   * @param callable $callback
   */
  function __construct($callback) {
    if (!is_callable($callback)) {
      throw new \InvalidArgumentException("Callback must be callable.");
    }
    $this->callback = $callback;
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
   */
  function invokeAll(ArgumentInterface $api) {
    $callback = $this->callback;
    $callback($api);
  }
}
