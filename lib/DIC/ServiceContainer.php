<?php

/**
 * Little brother of a dependency injection container (DIC)
 *
 * @property crumbs_BreadcrumbBuilder $breadcrumbBuilder
 * @property crumbs_TrailFinder $trailFinder
 * @property crumbs_ParentFinder $parentFinder
 * @property crumbs_PluginEngine $pluginEngine
 * @property crumbs_CallbackRestoration $callbackRestoration
 * @property crumbs_Container_CachedLazyPluginInfo $pluginInfo
 * @property crumbs_Container_LazyPageData $page
 * @property crumbs_Container_LazyDataByPath $trails
 * @property crumbs_Router $router
 */
class crumbs_DIC_ServiceContainer extends crumbs_DIC_AbstractServiceContainer {

  /**
   * A service that can build a breadcrumb from a trail.
   *
   * Available as crumbs('breadcrumbBuilder').
   *
   * @return crumbs_BreadcrumbBuilder
   */
  protected function breadcrumbBuilder() {
    return new crumbs_BreadcrumbBuilder($this->pluginEngine);
  }

  /**
   * A service that can build a trail for a given path.
   *
   * Available as crumbs('trailFinder').
   *
   * @return crumbs_TrailFinder
   */
  protected function trailFinder() {
    return new crumbs_TrailFinder($this->parentFinder, $this->router);
  }

  /**
   * A service that attempts to find a parent path for a given path.
   *
   * Available as crumbs('parentFinder').
   *
   * @return crumbs_ParentFinder
   */
  protected function parentFinder() {
    return new crumbs_ParentFinder($this->pluginEngine, $this->router);
  }

  /**
   * A service that knows all plugins and their configuration/weights,
   * and can run plugin operations on those plugins.
   *
   * Available as crumbs('pluginEngine').
   *
   * @return crumbs_PluginEngine
   */
  protected function pluginEngine() {
    return new crumbs_PluginEngine($this->pluginInfo, $this->router);
  }

  /**
   * @return crumbs_CallbackRestoration
   */
  protected function callbackRestoration() {
    return new crumbs_CallbackRestoration();
  }

  /**
   * A service that knows all plugins and their configuration/weights.
   *
   * Available as crumbs('pluginInfo').
   *
   * @return crumbs_Container_CachedLazyPluginInfo
   */
  protected function pluginInfo() {
    $source = new crumbs_PluginInfo();
    return new crumbs_Container_CachedLazyPluginInfo($source);
  }

  /**
   * Service that can provide information related to the current page.
   *
   * Available as crumbs('page').
   *
   * @return crumbs_Container_LazyPageData
   */
  protected function page() {
    $source = new crumbs_CurrentPageInfo($this->trails, $this->breadcrumbBuilder, $this->router);
    return new crumbs_Container_LazyPageData($source);
  }

  /**
   * Service that can provide/calculate trails for different paths.
   *
   * Available as crumbs('trails').
   *
   * @return crumbs_Container_LazyDataByPath
   */
  protected function trails() {
    return new crumbs_Container_LazyDataByPath($this->trailFinder);
  }

  /**
   * @return crumbs_Router
   */
  protected function router() {
    return new crumbs_Router();
  }

}
