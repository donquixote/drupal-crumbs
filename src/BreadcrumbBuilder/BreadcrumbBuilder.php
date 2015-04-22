<?php

namespace Drupal\crumbs\BreadcrumbBuilder;

use Drupal\crumbs\TitleFinder\TitleFinderInterface;

class BreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * @var \Drupal\crumbs\TitleFinder\TitleFinder
   */
  protected $titleFinder;

  /**
   * @param \Drupal\crumbs\TitleFinder\TitleFinderInterface $titleFinder
   */
  function __construct(TitleFinderInterface $titleFinder) {
    $this->titleFinder = $titleFinder;
  }

  /**
   * @param array[] $trail
   *
   * @return array[]
   */
  function buildBreadcrumb(array $trail) {
    $breadcrumb = array();
    foreach ($trail as $path => $item) {
      if ($item) {
        $title = $this->titleFinder->findTitle($path, $item, $breadcrumb);
        // The item will be skipped, if $title === FALSE.
        if (isset($title) && $title !== FALSE && $title !== '') {
          $item['title'] = $title;
          $breadcrumb[] = $item;
        }
      }
    }
    return $breadcrumb;
  }
}
