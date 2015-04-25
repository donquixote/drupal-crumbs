<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\DIC\ServiceContainer;
use Drupal\crumbs\PageData;

abstract class PathDemoBase implements WidgetInterface {

  /**
   * @var \Drupal\crumbs\PageData
   */
  protected $pageData;

  /**
   * @param $path
   * @param \Drupal\crumbs\DIC\ServiceContainer $services
   *
   * @return static
   */
  static function create($path, ServiceContainer $services) {
    $pageData = new PageData(
      $services->trailFinder,
      $services->breadcrumbBuilder,
      $services->breadcrumbFormatter,
      $services->router);
    $pageData->path = $path;
    return new static($pageData);
  }

  /**
   * @param \Drupal\crumbs\PageData $pageData
   */
  function __construct(PageData $pageData) {
    $this->pageData = $pageData;
  }
}
