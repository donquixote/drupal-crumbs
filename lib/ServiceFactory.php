<?php


class crumbs_ServiceFactory {

  /**
   * A service that can build a breadcrumb from a trail.
   *
   * Available as crumbs('breadcrumbBuilder').
   */
  function breadcrumbBuilder($cache) {
    return new crumbs_BreadcrumbBuilder($cache->pluginEngine);
  }

  /**
   * A service that can build a trail for a given path.
   *
   * Available as crumbs('trailFinder').
   */
  function trailFinder($cache) {
    return new crumbs_TrailFinder($cache->parentFinder);
  }

  /**
   * A service that attempts to find a parent path for a given path.
   *
   * Available as crumbs('parentFinder').
   */
  function parentFinder($cache) {
    return new crumbs_ParentFinder($cache->pluginEngine);
  }

  /**
   * A service that knows all plugins and their configuration/weights,
   * and can run plugin operations on those plugins.
   *
   * Available as crumbs('pluginEngine').
   */
  function pluginEngine($cache) {
    return new crumbs_PluginEngine($cache->pluginInfo);
  }

  /**
   * A service that knows all plugins and their configuration/weights.
   *
   * Available as crumbs('pluginInfo').
   */
  function pluginInfo($cache) {
    $source = new crumbs_PluginInfo();
    return new crumbs_Container_CachedLazyData($source);
  }

  /**
   * Service that can provide information related to the current page.
   *
   * Available as crumbs('page').
   */
  function page($cache) {
    $source = new crumbs_CurrentPageInfo($cache->trails, $cache->breadcrumbBuilder);
    return new crumbs_Container_LazyData($source);
  }

  /**
   * Service that can provide/calculate trails for different paths.
   *
   * Available as crumbs('trails').
   */
  function trails($cache) {
    return new crumbs_Container_LazyDataByPath($cache->trailFinder);
  }

  function router($cache) {
    return new crumbs_Router();
  }
}
