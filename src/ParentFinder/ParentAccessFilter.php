<?php

namespace Drupal\crumbs\ParentFinder;

/**
 * Use the frontpage as the parent for items with no access.
 */
class ParentAccessFilter extends ParentFinderDecoratorBase {

  /**
   * @param string $path
   * @param array $item
   *
   * @return string|NULL
   */
  function findParent($path, array $item) {
    return empty($item['access'])
      ? NULL
      : $this->decorated->findParent($path, $item);
  }
}
