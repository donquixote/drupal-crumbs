<?php

/**
 * Implements hook_crumbs_plugins().
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function search_crumbs_plugins($api) {
  $api->describeFindParent('search.*', t('Search pages'));
  $api->routeParentPath('search/node/%', 'search.node', 'search');
  $api->routeParentPath('search/user/%', 'search.user', 'search');
}
