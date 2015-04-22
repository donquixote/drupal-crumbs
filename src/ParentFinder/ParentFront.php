<?php

namespace Drupal\crumbs\ParentFinder;

class ParentFront extends ParentFinderDecoratorBase {

  /**
   * System path of the frontpage.
   *
   * @var string
   */
  protected $frontPath;

  /**
   * @param \Drupal\crumbs\ParentFinder\ParentFinderInterface $decorated
   * @param string $frontPath
   *   System path of the frontpage.
   */
  function __construct(ParentFinderInterface $decorated, $frontPath) {
    $this->frontPath = $frontPath;
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   */
  function findParent($path, array $item) {
    if ($path === $this->frontPath) {
      return NULL;
    }
    $parentPath = $this->decorated->findParent($path, $item);
    return isset($parentPath)
      ? $parentPath
      : $this->frontPath;
  }
}
