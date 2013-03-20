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
    $plugins = $cache->pluginLibrary->getAvailablePlugins();
    $disabled_keys = $cache->pluginLibrary->getDisabledByDefaultKeys();
    $weights = $cache->ruleWeightSettings->buildWeights($disabled_keys);
    return new crumbs_PluginEngine($plugins, $weights);
  }

  /**
   * A service that knows about all available plugins and their default
   * settings, but not about their runtime configuration / weights.
   *
   * Available as crumbs('pluginLibrary').
   */
  function pluginLibrary($cache) {
    return new crumbs_PluginLibrary();
  }

  /**
   * Service that knows about rule weight settings in the database,
   * but not about which plugins are actually available.
   *
   * Available as crumbs('ruleWeightSettings').
   */
  function ruleWeightSettings($cache) {
    return new crumbs_Conf_RuleWeightSettings();
  }

  /**
   * Service that can provide information related to the current page.
   *
   * Available as crumbs('page').
   */
  function page($cache) {
    $source = new crumbs_CurrentPageInfo($cache->trails, $cache->breadcrumbBuilder);
    return new crumbs_Util_DataCache($source);
  }

  /**
   * Service that can provide/calculate trails for different paths.
   *
   * Available as crumbs('trails').
   */
  function trails($cache) {
    return new crumbs_Util_PathCache($cache->trailFinder);
  }

  function router($cache) {
    return new crumbs_Router();
  }
}
