<?php

use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilder;
use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbDebugHistory;
use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbVisibilityFilter;
use Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatter;
use Drupal\crumbs\ParentFinder\ParentAccessFilter;
use Drupal\crumbs\ParentFinder\ParentBuffer;
use Drupal\crumbs\ParentFinder\ParentFallback;
use Drupal\crumbs\ParentFinder\ParentFront;
use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\TitlePluginType;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs\TitleFinder\FallbackTitleFinder;
use Drupal\crumbs\TrailFinder\ReverseTrailBuilder;
use Drupal\crumbs\TrailFinder\TrailAccessFilter;
use Drupal\crumbs\TrailFinder\TrailAppendFront;
use Drupal\crumbs\TrailFinder\TrailBuffer;
use Drupal\crumbs\TrailFinder\TrailUnreverse;

/**
 * Little brother of a dependency injection container (DIC)
 *
 * @property \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface $breadcrumbBuilder
 * @property \Drupal\crumbs\TrailFinder\TrailFinderInterface $trailFinder
 * @property \Drupal\crumbs\ParentFinder\ParentFinderInterface $rawParentFinder
 * @property \Drupal\crumbs\ParentFinder\ParentFinderInterface $parentFinder
 * @property \Drupal\crumbs\TitleFinder\TitleFinderInterface $rawTitleFinder
 * @property \Drupal\crumbs\TitleFinder\TitleFinderInterface $titleFinder
 * @property \Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatterInterface $breadcrumbFormatter
 * @property crumbs_CallbackRestoration $callbackRestoration
 * @property crumbs_PluginSystem_PluginInfo $pluginInfo
 * @property crumbs_CurrentPageInfo $page
 * @property \Drupal\crumbs\Router\RouterInterface $router
 * @property \Drupal\crumbs\PluginSystem\Discovery\PluginDiscoveryBuffer $pluginDiscoveryBuffer
 * @property PluginStatusWeightMap $parentStatusWeightMap
 * @property \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $parentPluginCollection
 * @property PluginStatusWeightMap $titleStatusWeightMap
 */
class crumbs_DIC_ServiceContainer extends crumbs_DIC_AbstractServiceContainer {

  /**
   * A service that can build a breadcrumb from a trail.
   *
   * @return \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface
   *
   * @see crumbs_DIC_ServiceContainer::$breadcrumbBuilder
   */
  protected function breadcrumbBuilder() {
    $breadcrumbBuilder = new BreadcrumbBuilder($this->titleFinder);
    $breadcrumbBuilder = new BreadcrumbVisibilityFilter(
      $breadcrumbBuilder,
      variable_get('crumbs_minimum_trail_items', 2),
      variable_get('crumbs_show_front_page', TRUE),
      variable_get('crumbs_show_current_page', FALSE) & ~CRUMBS_TRAILING_SEPARATOR);
    if (user_access('administer crumbs')) {
      $breadcrumbBuilder = new BreadcrumbDebugHistory($breadcrumbBuilder);
    }
    return $breadcrumbBuilder;
  }

  /**
   * @return \Drupal\crumbs\TitleFinder\TitleFinderInterface
   *
   * @see crumbs_DIC_ServiceContainer::$titleFinder
   */
  protected function titleFinder() {
    $titleFinder = $this->rawTitleFinder;
    $titleFinder = new FallbackTitleFinder($titleFinder);
    return $titleFinder;
  }

  /**
   * @return \Drupal\crumbs\TitleFinder\TitleFinderInterface
   *
   * @see crumbs_DIC_ServiceContainer::$rawTitleFinder
   */
  protected function rawTitleFinder() {
    return \Drupal\crumbs\PluginSystem\Engine\FactoryUtil::createTitleFinder(
      $this->pluginDiscoveryBuffer->getTitlePluginCollection(),
      $this->titleStatusWeightMap);
  }

  /**
   * A service that can build a trail for a given path.
   *
   * @return \Drupal\crumbs\TrailFinder\TrailFinderInterface
   *
   * @see crumbs_DIC_ServiceContainer::$trailFinder
   */
  protected function trailFinder() {
    $trailFinder = new ReverseTrailBuilder($this->parentFinder, $this->router);
    $trailFinder = new TrailAccessFilter($trailFinder);
    $trailFinder = new TrailAppendFront($trailFinder, $this->router);
    $trailFinder = new TrailUnreverse($trailFinder);
    $trailFinder = new TrailBuffer($trailFinder);
    return $trailFinder;
  }

  /**
   * A service that attempts to find a parent path for a given path.
   *
   * @return \Drupal\crumbs\ParentFinder\ParentFinderInterface
   *
   * @see crumbs_DIC_ServiceContainer::$parentFinder
   */
  protected function parentFinder() {
    $parentFinder = $this->rawParentFinder;
    $parentFinder = new ParentAccessFilter($parentFinder);
    $parentFinder = new ParentFallback($parentFinder, $this->router);
    $parentFinder = new ParentFront($parentFinder, $this->router->getFrontNormalPath());
    $parentFinder = new ParentBuffer($parentFinder);
    return $parentFinder;
  }

  /**
   * @return \Drupal\crumbs\ParentFinder\ParentFinder
   *
   * @see crumbs_DIC_ServiceContainer::$parentPluginEngine
   */
  protected function rawParentFinder() {
    return \Drupal\crumbs\PluginSystem\Engine\FactoryUtil::createParentFinder(
      $this->pluginDiscoveryBuffer->getParentPluginCollection(),
      $this->parentStatusWeightMap,
      $this->router);
  }

  /**
   * @return \Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatterInterface
   *
   * @see crumbs_DIC_ServiceContainer::$breadcrumbFormatter
   */
  protected function breadcrumbFormatter() {
    return new BreadcrumbFormatter(
      variable_get('crumbs_show_current_page', FALSE) & ~CRUMBS_TRAILING_SEPARATOR,
      filter_xss_admin(variable_get('crumbs_separator', ' &raquo; ')),
      variable_get('crumbs_separator_span', FALSE),
      variable_get('crumbs_show_current_page', FALSE) & CRUMBS_TRAILING_SEPARATOR);
  }

  /**
   * @return crumbs_CallbackRestoration
   *
   * @see crumbs_DIC_ServiceContainer::$callbackRestoration
   */
  protected function callbackRestoration() {
    return new crumbs_CallbackRestoration();
  }

  /**
   * A service that knows all plugins and their configuration/weights.
   *
   * @return crumbs_PluginSystem_PluginInfo
   *
   * @see crumbs_DIC_ServiceContainer::$pluginInfo
   */
  protected function pluginInfo() {
    return new crumbs_PluginSystem_PluginInfo();
  }

  /**
   * Service that can provide information related to the current page.
   *
   * @return crumbs_CurrentPageInfo
   *
   * @see crumbs_DIC_ServiceContainer::$page
   */
  protected function page() {
    return new crumbs_CurrentPageInfo(
      $this->trailFinder,
      $this->breadcrumbBuilder,
      $this->breadcrumbFormatter,
      $this->router);
  }

  /**
   * Wrapper for routing-related Drupal core functions.
   *
   * @return crumbs_Router
   *
   * @see crumbs_DIC_ServiceContainer::$router
   */
  protected function router() {
    return new crumbs_Router();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Discovery\PluginDiscoveryBuffer
   *
   * @see crumbs_DIC_ServiceContainer::$pluginDiscoveryBuffer
   */
  protected function pluginDiscoveryBuffer() {
    return \Drupal\crumbs\PluginSystem\Discovery\PluginDiscoveryBuffer::create();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection
   *
   * @see crumbs_DIC_ServiceContainer::$parentPluginCollection
   */
  protected function parentPluginCollection() {
    return $this->pluginDiscoveryBuffer->getParentPluginCollection();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap
   *
   * @see crumbs_DIC_ServiceContainer::$parentStatusWeightMap
   */
  protected function parentStatusWeightMap() {
    return PluginStatusWeightMap::loadAndCreate(
      $this->pluginDiscoveryBuffer->getParentPluginCollection()->getDefaultStatuses(),
      new ParentPluginType());
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap
   *
   * @see crumbs_DIC_ServiceContainer::$titleStatusWeightMap
   */
  protected function titleStatusWeightMap() {
    return PluginStatusWeightMap::loadAndCreate(
      $this->pluginDiscoveryBuffer->getTitlePluginCollection()->getDefaultStatuses(),
      new TitlePluginType());
  }

}
