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

  /**
   * Gets the decorated parent finder.
   *
   * This is mostly used for debug and testing purposes.
   *
   * @return \Drupal\crumbs\ParentFinder\ParentFinderInterface
   */
  function getDecorated() {
    return $this->decorated;
  }
}
