<?php

/**
 * Implements hook_crumbs_plugins().
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function search_crumbs_plugins($api) {
  $api->routeParentPath('search/node/%', 'search.node.result', 'search');
  $api->routeParentPath('search/user/%', 'search.user.result', 'search');
}
