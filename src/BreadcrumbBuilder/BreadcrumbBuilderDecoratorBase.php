<?php

namespace Drupal\crumbs\BreadcrumbBuilder;

abstract class BreadcrumbBuilderDecoratorBase implements BreadcrumbBuilderInterface {

  /**
   * @var \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface
   */
  protected $decorated;

  /**
   * @param \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface $decorated
   */
  function __construct(BreadcrumbBuilderInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * @return \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface
   */
  public function getDecorated() {
    return $this->decorated;
  }

}
