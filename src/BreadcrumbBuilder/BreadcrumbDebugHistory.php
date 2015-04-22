<?php

namespace Drupal\crumbs\BreadcrumbBuilder;

class BreadcrumbDebugHistory extends BreadcrumbBuilderDecoratorBase {

  /**
   * @param array[] $trail
   *
   * @return array[]
   */
  function buildBreadcrumb(array $trail) {
    $paths = array_keys($trail);
    $path = end($paths);
    // Remember which pages we are visiting,
    // for the autocomplete on admin/structure/crumbs/debug.
    unset($_SESSION['crumbs.admin.debug.history'][$path]);
    $_SESSION['crumbs.admin.debug.history'][$path] = TRUE;
    // Never remember more than 15 links.
    while (15 < count($_SESSION['crumbs.admin.debug.history'])) {
      array_shift($_SESSION['crumbs.admin.debug.history']);
    }

    return $this->decorated->buildBreadcrumb($trail);
  }
}
