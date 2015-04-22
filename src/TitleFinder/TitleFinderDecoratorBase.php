<?php

namespace Drupal\crumbs\TitleFinder;

abstract class TitleFinderDecoratorBase implements TitleFinderInterface {

  /**
   * @var \Drupal\crumbs\TitleFinder\TitleFinderInterface
   */
  protected $decorated;

  /**
   * @param \Drupal\crumbs\TitleFinder\TitleFinderInterface $decorated
   */
  function __construct(TitleFinderInterface $decorated) {
    $this->decorated = $decorated;
  }
}
