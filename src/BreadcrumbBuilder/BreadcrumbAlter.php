<?php

namespace Drupal\crumbs\BreadcrumbBuilder;

class BreadcrumbAlter extends BreadcrumbBuilderDecoratorBase {

  /**
   * @param array[] $trail
   *
   * @return array[]
   */
  function buildBreadcrumb(array $trail) {
    $breadcrumb_items = $this->decorated->buildBreadcrumb($trail);
    if (empty($breadcrumb_items)) {
      return array();
    }
    $router_item = end($trail);
    // Allow modules to alter the breadcrumb, if possible, as that is much
    // faster than rebuilding an entirely new active trail.
    drupal_alter('menu_breadcrumb', $breadcrumb_items, $router_item);
    return $breadcrumb_items;
  }
}
