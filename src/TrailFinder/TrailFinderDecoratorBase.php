<?php

namespace Drupal\crumbs\TrailFinder;

abstract class TrailFinderDecoratorBase implements TrailFinderInterface {

  /**
   * @var \Drupal\crumbs\TrailFinder\TrailFinderInterface
   */
  protected $decorated;

  /**
   * @param \Drupal\crumbs\TrailFinder\TrailFinderInterface $decorated
   */
  function __construct(TrailFinderInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * @return \Drupal\crumbs\TrailFinder\TrailFinderInterface
   */
  function getDecorated() {
    return $this->decorated;
  }
}
