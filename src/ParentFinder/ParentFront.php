<?php

namespace Drupal\crumbs\ParentFinder;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
use Drupal\crumbs\Router\RouterInterface;

class ParentFront extends ParentFinderDecoratorBase {

  /**
   * System path of the frontpage.
   *
   * @var string
   */
  protected $frontPath;

  /**
   * @var array
   *   The frontpage router item.
   */
  private $frontRouterItem;

  /**
   * @param \Drupal\crumbs\ParentFinder\ParentFinderInterface $decorated
   * @param \Drupal\crumbs\Router\RouterInterface $router
   *
   * @return static
   */
  static function createFromRouter(ParentFinderInterface $decorated, RouterInterface $router) {
    $frontNormalPath = $router->getFrontNormalPath();
    $frontRouterItem = $router->getRouterItem($frontNormalPath);
    return new static($decorated, $frontRouterItem);
  }

  /**
   * @param \Drupal\crumbs\ParentFinder\ParentFinderInterface $decorated
   * @param array $frontRouterItem
   *   System path of the frontpage.
   */
  function __construct(ParentFinderInterface $decorated, array $frontRouterItem) {
    parent::__construct($decorated);
    $this->frontPath = $frontRouterItem['link_path'];
    $this->frontRouterItem = $frontRouterItem;
  }

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return bool
   *   TRUE, if it was found.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {

    if ($routerItem['link_path'] === $this->frontPath) {
      // The frontpage has no parent.
      return FALSE;
    }

    if ($this->decorated->findParentRouterItem($routerItem, $checker)) {
      return TRUE;
    }

    if ($checker->checkParentPath($this->frontPath, '.ParentFront')) {
      return TRUE;
    }

    return FALSE;
  }
}
