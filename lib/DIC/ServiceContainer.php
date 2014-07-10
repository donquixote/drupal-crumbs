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
 * @property crumbs_CurrentPageInfo $page
 * @property crumbs_Container_LazyDataByPath $trails
 * @property crumbs_Router $router
 */
class crumbs_DIC_ServiceContainer extends crumbs_DIC_AbstractServiceContainer {

  /**
   * A service that can build a breadcrumb from a trail.
   *
   * @return crumbs_BreadcrumbBuilder
   *
   * @see crumbs_DIC_ServiceContainer::breadcrumbBuilder
   */
  protected function breadcrumbBuilder() {
    return new crumbs_BreadcrumbBuilder($this->pluginEngine);
  }

  /**
   * A service that can build a trail for a given path.
   *
   * @return crumbs_TrailFinder
   *
   * @see crumbs_DIC_ServiceContainer::trailFinder
   */
  protected function trailFinder() {
    return new crumbs_TrailFinder($this->parentFinder, $this->router);
  }

  /**
   * A service that attempts to find a parent path for a given path.
   *
   * @return crumbs_ParentFinder
   *
   * @see crumbs_DIC_ServiceContainer::parentFinder
   */
  protected function parentFinder() {
    return new crumbs_ParentFinder($this->pluginEngine, $this->router);
  }

  /**
   * A service that knows all plugins and their configuration/weights,
   * and can run plugin operations on those plugins.
   *
   * @return crumbs_PluginEngine
   *
   * @see crumbs_DIC_ServiceContainer::pluginEngine
   */
  protected function pluginEngine() {
    return new crumbs_PluginEngine($this->pluginInfo, $this->router);
  }

  /**
   * @return crumbs_CallbackRestoration
   *
   * @see crumbs_DIC_ServiceContainer::callbackRestoration
   */
  protected function callbackRestoration() {
    return new crumbs_CallbackRestoration();
  }

  /**
   * A service that knows all plugins and their configuration/weights.
   *
   * @return crumbs_Container_CachedLazyPluginInfo
   *
   * @see crumbs_DIC_ServiceContainer::pluginInfo
   */
  protected function pluginInfo() {
    $source = new crumbs_PluginInfo();
    return new crumbs_Container_CachedLazyPluginInfo($source);
  }

  /**
   * Service that can provide information related to the current page.
   *
   * @return crumbs_CurrentPageInfo
   *
   * @see crumbs_DIC_ServiceContainer::page
   */
  protected function page() {
    return new crumbs_CurrentPageInfo($this->trails, $this->breadcrumbBuilder, $this->router);
  }

  /**
   * Service that can provide/calculate trails for different paths.
   *
   * @return crumbs_Container_LazyDataByPath
   *
   * @see crumbs_DIC_ServiceContainer::trails
   */
  protected function trails() {
    return new crumbs_Container_LazyDataByPath($this->trailFinder);
  }

  /**
   * Wrapper for routing-related Drupal core functions.
   *
   * @return crumbs_Router
   *
   * @see crumbs_DIC_ServiceContainer::router
   */
  protected function router() {
    return new crumbs_Router();
  }

}
