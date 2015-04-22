<?php

namespace Drupal\crumbs\BreadcrumbBuilder;

abstract class BreadcrumbBuilderDecoratorBase implements BreadcrumbBuilderInterface {

  /**
   * @var \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface
   */
  protected $decorated;

  function __construct(BreadcrumbBuilderInterface $decorated) {
    $this->decorated = $decorated;
  }

}
