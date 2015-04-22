<?php
namespace Drupal\crumbs\BreadcrumbBuilder;

interface BreadcrumbBuilderInterface {

  /**
   * @param array[] $trail
   *
   * @return array[]
   */
  function buildBreadcrumb(array $trail);
}
