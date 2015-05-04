<?php

namespace Drupal\crumbs\TitleFinder;

interface TitleFinderInterface {

  /**
   * @param string $path
   * @param array $item
   * @param array[] $breadcrumb
   *
   * @return NULL|string
   */
  function findTitle($path, array $item, array $breadcrumb = array());

}
