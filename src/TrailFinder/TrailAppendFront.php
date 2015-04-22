<?php

namespace Drupal\crumbs\TrailFinder;

use Drupal\crumbs\Router\RouterInterface;

class TrailAppendFront extends TrailFinderDecoratorBase {

  /**
   * @var \Drupal\crumbs\Router\RouterInterface
   */
  protected $router;

  /**
   * @param \Drupal\crumbs\TrailFinder\TrailFinderInterface $decorated
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(TrailFinderInterface $decorated, RouterInterface $router) {
    parent::__construct($decorated);
    $this->router = $router;
  }

  /**
   * @param string $path
   *
   * @return array[]
   */
  function buildTrail($path) {
    $front_normal_path = $this->router->getFrontNormalPath();
    $front_menu_item = $this->router->getRouterItem($front_normal_path);
    $reverse_trail = $this->decorated->buildTrail($path);
    $reverse_trail[$front_normal_path] = $front_menu_item;
    return $reverse_trail;
  }
}
