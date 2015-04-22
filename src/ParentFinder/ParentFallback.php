<?php

namespace Drupal\crumbs\ParentFinder;

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
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   */
  function findParent($path, array $item) {
    $parentPath = $this->decorated->findParent($path, $item);
    return isset($parentPath)
      ? $parentPath
      : $this->router->reducePath($path);
  }
}
