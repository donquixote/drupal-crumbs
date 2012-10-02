<?php


function crumbs_build_breadcrumb(array $trail) {
  $pluginEngine = _crumbs_load_plugin_engine();
  $breadcrumbBuilder = new crumbs_BreadcrumbBuilder($pluginEngine);
  return $breadcrumbBuilder->buildBreadcrumb($trail);
}
