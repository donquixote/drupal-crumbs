<?php
use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface $api
 */
function search_crumbs_plugins(ArgumentInterface $api) {

  $api->route('search/node/%')
    ->fixedParentPath('node', 'search')
    ->translateDescription('Node search page (search/node/%)');

  $api->route('search/user/%')
    ->fixedParentPath('user', 'search')
    ->translateDescription('Use search page (search/user/%)');
}
