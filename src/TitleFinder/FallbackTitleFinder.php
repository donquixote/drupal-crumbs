<?php

namespace Drupal\crumbs\TitleFinder;

class FallbackTitleFinder extends TitleFinderDecoratorBase {

  /**
   * @param string $path
   * @param array $item
   * @param array[] $breadcrumb
   *
   * @return NULL|string
   */
  function findTitle($path, array $item, array $breadcrumb = array()) {
    $title = $this->decorated->findTitle($path, $item, $breadcrumb);
    if (!isset($title)) {
      return $item['title'];
    }
    return $title;
  }
}
