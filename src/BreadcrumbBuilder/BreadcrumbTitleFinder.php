<?php

namespace Drupal\crumbs\BreadcrumbBuilder;

use Drupal\crumbs\TitleFinder\TitleFinderInterface;

class BreadcrumbTitleFinder extends BreadcrumbBuilderDecoratorBase {

  /**
   * @var \Drupal\crumbs\TitleFinder\TitleFinder
   */
  protected $titleFinder;

  /**
   * @param \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface $decorated
   * @param \Drupal\crumbs\TitleFinder\TitleFinderInterface $titleFinder
   */
  function __construct(BreadcrumbBuilderInterface $decorated, TitleFinderInterface $titleFinder) {
    parent::__construct($decorated);
    $this->titleFinder = $titleFinder;
  }

  /**
   * @param array[] $trail
   *
   * @return array[]
   */
  function buildBreadcrumb(array $trail) {
    $breadcrumb = $this->decorated->buildBreadcrumb($trail);
    foreach ($breadcrumb as $path => &$item) {
      if ($item) {
        $title = $this->titleFinder->findTitle($path, $item, $breadcrumb);
        // The item will be skipped, if $title === FALSE.
        if (!isset($title) || $title === FALSE || $title === '') {
          unset($breadcrumb[$path]);
        }
        $item['title'] = $title;
      }
    }
    return $breadcrumb;
  }

}
