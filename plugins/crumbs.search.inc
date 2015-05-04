<?php
use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface $api
 */
function search_crumbs_plugins(ArgumentInterface $api) {
  $family = $api->modulePluginFamily()
    ->describeFindParent(t('Search pages'));
  $family->route('search/node/%')->fixedParentPath('search.node', 'search');
  $family->route('search/user/%')->fixedParentPath('search.user', 'search');
}
