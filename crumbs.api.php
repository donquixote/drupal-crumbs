<?php
use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;

/**
 * A hook to register crumbs plugins.
 *
 * @param \Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface $api
 *   An object with methods to register plugins.
 *   See the class definition of crumbs_InjectedAPI_hookCrumbsPlugins, which
 *   methods are available.
 */
function hook_crumbs_plugins(ArgumentInterface $api) {
  // @todo Some examples.
}
