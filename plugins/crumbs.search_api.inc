<?php

/**
 * Implements hook_crumbs_plugins()
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function search_api_crumbs_plugins($api) {
  $api->routeTitleCallback('admin/config/search/search_api/index/%', 'index', function($path, $item) {

  });
  foreach (array(
    'dependencies',
    'edit',
    'export',
    'filters',
    'revert',
  ) as $key) {
    $api->routeParentCallback('admin/config/search/facetapi/%/%/%/' . $key, $key, function($path, $item) {
      list(,,,,$adapter, $realm, $facet) = $item['map'];
      if (!$adapter instanceof FacetapiAdapter || empty($realm['name'])) {
        return NULL;
      }
      return $adapter->getPath($realm['name']);
    });
  }
}
