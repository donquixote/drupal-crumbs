<?php

namespace Drupal\crumbs\PluginSystem\Plugin\LegacyWrapper;

class MultiTitlePluginLegacyWrapper implements \crumbs_MultiPlugin_FindTitleInterface {

  /**
   * @var \crumbs_MultiPlugin
   */
  private $wrappedPlugin;

  /**
   * @var string
   */
  private $method;

  /**
   * @param \crumbs_MultiPlugin $wrappedPlugin
   * @param string $method
   */
  function __construct(\crumbs_MultiPlugin $wrappedPlugin, $method) {
    $this->wrappedPlugin = $wrappedPlugin;
    $this->method = $method;
  }

  /**
   * @param \Drupal\crumbs\PluginApi\DescribeArgument\DescribeMultiPluginArg $api
   *   Injected API object, with methods that allow the plugin to further
   *   describe itself.
   *   The plugin is supposed to tell Crumbs about all possible rule keys, and
   *   can give a label and a description for each.
   *
   * @return string[]|void
   *   As an alternative to the API object's methods, the plugin can simply
   *   return a key-value array, where the keys are the available rules, and the
   *   values are their respective labels.
   */
  function describe($api) {
    return $this->wrappedPlugin->describe($api);
  }

  /**
   * Find candidates for the title path.
   *
   * @param string $path
   *   The path that we want to find a title for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return string[]
   *   Title path candidates
   */
  function findTitle($path, $item) {
    return $this->wrappedPlugin->{$this->method}($path, $item);
  }
}
