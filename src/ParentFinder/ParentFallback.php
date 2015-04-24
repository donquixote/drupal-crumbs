<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
use Drupal\crumbs\Router\RouterInterface;

class ParentFallback extends ParentFinderDecoratorBase {

  /**
   * @var \Drupal\crumbs\Router\RouterInterface
   */
  protected $router;

  /**
   * @param \Drupal\crumbs\ParentFinder\ParentFinderInterface $decorated
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(ParentFinderInterface $decorated, RouterInterface $router) {
    $this->router = $router;
    parent::__construct($decorated);
  }

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return array|NULL
   *   The parent router item, or NULL.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {

    $parentRouterItem = $this->decorated->findParentRouterItem($routerItem, $checker);
    if (isset($parentRouterItem)) {
      return $parentRouterItem;
    }

    // Chop off path fragments at the end, to find a valid parent.
    $fragments = $routerItem['fragments'];
    while (count($fragments) > 1) {
      array_pop($fragments);
      $parentRouterItem = $checker->checkParentPath(implode('/', $fragments), '.ParentFallback');
      if (isset($parentRouterItem)) {
        return $parentRouterItem;
      }
    }

    return NULL;
  }
}
