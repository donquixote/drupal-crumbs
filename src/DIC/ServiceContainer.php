<?php

namespace Drupal\crumbs\DIC;

use Drupal\crumbs\PageData;
use Drupal\crumbs\Router\Router;
use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilder;
use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbDebugHistory;
use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbVisibilityFilter;
use Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatter;
use Drupal\crumbs\ParentCollector\ParentCollector;
use Drupal\crumbs\ParentFinder\ParentBuffer;
use Drupal\crumbs\ParentFinder\ParentFallback;
use Drupal\crumbs\ParentFinder\ParentFinder;
use Drupal\crumbs\ParentFinder\ParentFront;
use Drupal\crumbs\PluginSystem\Discovery\PluginDiscoveryBuffer;
use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\TitlePluginType;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs\TitleFinder\FallbackTitleFinder;
use Drupal\crumbs\TitleFinder\TitleFinder;
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
 * @property \Drupal\crumbs\ParentFinder\ParentFinderInterface $parentFinder
 * @property \Drupal\crumbs\ParentCollector\ParentCollector $parentCollector
 * @property \Drupal\crumbs\TitleFinder\TitleFinderInterface $titleFinder
 * @property \Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatterInterface $breadcrumbFormatter
 * @property PageData $page
 * @property \Drupal\crumbs\Router\RouterInterface $router
 * @property PluginDiscoveryBuffer $pluginDiscoveryBuffer
 * @property PluginStatusWeightMap $parentStatusWeightMap
 * @property \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection $parentPluginCollection
 * @property PluginStatusWeightMap $titleStatusWeightMap
 */
class ServiceContainer extends ServiceContainerBase {

  /**
   * A service that can build a breadcrumb from a trail.
   *
   * @return \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface
   *
   * @see ServiceContainer::$breadcrumbBuilder
   */
  protected function breadcrumbBuilder() {
    $breadcrumbBuilder = new BreadcrumbBuilder($this->titleFinder);
    $breadcrumbBuilder = new BreadcrumbVisibilityFilter(
      $breadcrumbBuilder,
      variable_get('crumbs_minimum_trail_items', 2),
      variable_get('crumbs_show_front_page', TRUE),
      variable_get('crumbs_show_current_page', FALSE) & ~CRUMBS_TRAILING_SEPARATOR
    );
    if (user_access('administer crumbs')) {
      $breadcrumbBuilder = new BreadcrumbDebugHistory($breadcrumbBuilder);
    }
    return $breadcrumbBuilder;
  }

  /**
   * @return \Drupal\crumbs\TitleFinder\TitleFinderInterface
   *
   * @see ServiceContainer::$titleFinder
   */
  protected function titleFinder() {
    $titleFinder = TitleFinder::create(
      $this->pluginDiscoveryBuffer->getTitlePluginCollection(),
      $this->titleStatusWeightMap);
    $titleFinder = new FallbackTitleFinder($titleFinder);
    return $titleFinder;
  }

  /**
   * A service that can build a trail for a given path.
   *
   * @return \Drupal\crumbs\TrailFinder\TrailFinderInterface
   *
   * @see ServiceContainer::$trailFinder
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
   * @see ServiceContainer::$parentFinder
   */
  protected function parentFinder() {
    $parentFinder = ParentFinder::create(
      $this->pluginDiscoveryBuffer->getParentPluginCollection(),
      $this->parentStatusWeightMap,
      $this->router);
    $parentFinder = new ParentFallback($parentFinder, $this->router);
    $parentFinder = ParentFront::createFromRouter($parentFinder, $this->router);
    $parentFinder = new ParentBuffer($parentFinder);
    return $parentFinder;
  }

  /**
   * A service that attempts to find a parent path for a given path.
   *
   * @return \Drupal\crumbs\ParentFinder\ParentFinderInterface
   *
   * @see ServiceContainer::$parentCollector
   */
  protected function parentCollector() {
    return new ParentCollector($this->parentFinder);
  }

  /**
   * @return \Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatterInterface
   *
   * @see ServiceContainer::$breadcrumbFormatter
   */
  protected function breadcrumbFormatter() {
    return new BreadcrumbFormatter(
      variable_get('crumbs_show_current_page', FALSE) & ~CRUMBS_TRAILING_SEPARATOR,
      filter_xss_admin(variable_get('crumbs_separator', ' &raquo; ')),
      variable_get('crumbs_separator_span', FALSE),
      variable_get('crumbs_show_current_page', FALSE) & CRUMBS_TRAILING_SEPARATOR
    );
  }

  /**
   * Service that can provide information related to the current page.
   *
   * @return PageData
   *
   * @see ServiceContainer::$page
   */
  protected function page() {
    return new PageData(
      $this->trailFinder,
      $this->breadcrumbBuilder,
      $this->breadcrumbFormatter,
      $this->router
    );
  }

  /**
   * Wrapper for routing-related Drupal core functions.
   *
   * @return Router
   *
   * @see ServiceContainer::$router
   */
  protected function router() {
    return new Router();
  }

  /**
   * @return PluginDiscoveryBuffer
   *
   * @see ServiceContainer::$pluginDiscoveryBuffer
   */
  protected function pluginDiscoveryBuffer() {
    return PluginDiscoveryBuffer::create();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection
   *
   * @see ServiceContainer::$parentPluginCollection
   */
  protected function parentPluginCollection() {
    return $this->pluginDiscoveryBuffer->getParentPluginCollection();
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap
   *
   * @see ServiceContainer::$parentStatusWeightMap
   */
  protected function parentStatusWeightMap() {
    return PluginStatusWeightMap::loadAndCreate(
      $this->pluginDiscoveryBuffer->getParentPluginCollection()
        ->getDefaultStatuses(),
      new ParentPluginType()
    );
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap
   *
   * @see ServiceContainer::$titleStatusWeightMap
   */
  protected function titleStatusWeightMap() {
    return PluginStatusWeightMap::loadAndCreate(
      $this->pluginDiscoveryBuffer->getTitlePluginCollection()
        ->getDefaultStatuses(),
      new TitlePluginType()
    );
  }

}
