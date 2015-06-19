<?php

namespace Drupal\crumbs\ParentCollector;

use Drupal\crumbs\ParentFinder\Approval\AccessChecker;
use Drupal\crumbs\ParentFinder\Approval\CollectorChecker;
use Drupal\crumbs\ParentFinder\ParentFinder;
use Drupal\crumbs\ParentFinder\ParentFinderInterface;
use Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollection;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs\Router\RouterInterface;

class ParentCollector {

  /**
   * @var \Drupal\crumbs\ParentFinder\ParentFinderInterface
   */
  private $parentFinder;

  /**
   * @param \Drupal\crumbs\PluginSystem\Collection\PluginCollection\PluginCollection $pluginCollection
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $statusMap
   * @param \Drupal\crumbs\Router\RouterInterface $router
   *
   * @return static
   */
  static function create(
    PluginCollection $pluginCollection,
    PluginStatusWeightMap $statusMap,
    RouterInterface $router
  ) {
    $parentFinder = ParentFinder::create($pluginCollection, $statusMap, $router);
    return new static($parentFinder);
  }

  /**
   * @param \Drupal\crumbs\ParentFinder\ParentFinderInterface $parentFinder
   */
  function __construct(ParentFinderInterface $parentFinder) {
    $this->parentFinder = $parentFinder;
  }

  /**
   * @param array $routerItem
   * @param \Drupal\crumbs\Router\RouterInterface $router
   *
   * @return array|NULL
   */
  function findParentRouterItem(array $routerItem, RouterInterface $router) {
    $checker = new AccessChecker($router);
    return $this->parentFinder->findParentRouterItem($routerItem, $checker);
  }

  /**
   * @param array $routerItem
   *
   * @return array[]
   */
  function findAllParentRouterItems(array $routerItem) {
    $collector = new CollectorChecker();
    $this->parentFinder->findParentRouterItem($routerItem, $collector);
    return $collector->getCollectedPaths();
  }
}
