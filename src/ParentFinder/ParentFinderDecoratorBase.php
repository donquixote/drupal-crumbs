<?php

namespace Drupal\crumbs\ParentFinder;

abstract class ParentFinderDecoratorBase implements ParentFinderInterface {

  /**
   * @var \Drupal\crumbs\ParentFinder\ParentFinderInterface
   */
  protected $decorated;

  /**
   * @param \Drupal\crumbs\ParentFinder\ParentFinderInterface $decorated
   */
  function __construct(ParentFinderInterface $decorated) {
    $this->decorated = $decorated;
  }
}
