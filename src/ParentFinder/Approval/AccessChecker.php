<?php

namespace Drupal\crumbs\ParentFinder\Approval;

use Drupal\crumbs\Router\RouterInterface;

class AccessChecker implements CheckerInterface {

  /**
   * @var \Drupal\crumbs\Router\RouterInterface
   */
  private $router;

  /**
   * The result.
   *
   * @var array|NULL
   */
  private $parentRouterItem;

  /**
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(RouterInterface $router) {
    $this->router = $router;
  }

  /**
   * @param array $routerItem
   *   A router item.
   * @param string $key
   *
   * @return bool
   *   TRUE, if the router item is accepted in the breadcrumb trail.
   */
  function checkRouterItem(array $routerItem, $key) {
    if (empty($routerItem['access'])) {
      return FALSE;
    }

    $this->parentRouterItem = $routerItem;
    return TRUE;
  }

  /**
   * @param string $path
   * @param string $key
   *
   * @return bool
   */
  function checkParentPath($path, $key) {
    if ($this->router->urlIsExternal($path)) {
      return FALSE;
    }

    $routerItem = $this->router->getRouterItem($path);
    if (!isset($routerItem)) {
      return FALSE;
    }

    if (empty($routerItem['access'])) {
      return FALSE;
    }

    $this->parentRouterItem = $routerItem;
    return TRUE;
  }

  /**
   * @return array|NULL
   */
  function getParentRouterItem() {
    return $this->parentRouterItem;
  }
}
