<?php
namespace Drupal\crumbs\BreadcrumbFormatter;

interface BreadcrumbFormatterInterface {

  /**
   * @param array $breadcrumb_items
   * @param array $trail
   *
   * @return string
   * @throws \Exception
   */
  function formatBreadcrumb(array $breadcrumb_items, array $trail);
}
