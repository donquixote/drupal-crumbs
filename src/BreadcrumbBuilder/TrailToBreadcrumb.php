<?php

namespace Drupal\crumbs\BreadcrumbBuilder;



class TrailToBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * @param array[] $trail
   *
   * @return array[]
   */
  function buildBreadcrumb(array $trail) {
    $breadcrumb = array();
    foreach ($trail as $path => $item) {
      if ($item) {
        $breadcrumb[$path] = $item;
      }
    }
    return $breadcrumb;
  }
}
