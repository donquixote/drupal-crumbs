<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function search_crumbs_plugins(ArgumentInterface $api) {
  $api->describeFindParent('search.*', t('Search pages'));
  $api->routeParentPath('search/node/%', 'search.node', 'search');
  $api->routeParentPath('search/user/%', 'search.user', 'search');
}
