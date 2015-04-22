<?php

namespace Drupal\crumbs\BreadcrumbBuilder;

class BreadcrumbVisibilityFilter extends BreadcrumbBuilderDecoratorBase {

  /**
   * @var int
   */
  private $minTrailItems;

  /**
   * @var int
   */
  private $minVisibleItems;

  /**
   * @var bool
   */
  private $showFrontPage;

  /**
   * @var bool
   */
  private $showCurrentPage;

  /**
   * @param \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface $decorated
   * @param int $minTrailItems
   * @param bool $showFrontPage
   * @param bool $showCurrentPage
   */
  function __construct($decorated, $minTrailItems, $showFrontPage, $showCurrentPage) {
    parent::__construct($decorated);
    $this->minTrailItems = $minTrailItems;
    $this->minVisibleItems = $minTrailItems
      + ($showFrontPage ? 0 : -1)
      + ($showCurrentPage ? 0 : -1);
    $this->showFrontPage = $showFrontPage;
    $this->showCurrentPage = $showCurrentPage;
  }

  /**
   * @param array[] $trail
   *
   * @return array[]
   */
  function buildBreadcrumb(array $trail) {
    if (count($trail) < $this->minTrailItems) {
      return array();
    }
    if (!$this->showFrontPage) {
      array_shift($trail);
    }
    if (!$this->showCurrentPage) {
      array_pop($trail);
    }
    if (!count($trail)) {
      return array();
    }
    $items = $this->decorated->buildBreadcrumb($trail);
    if (count($items) < $this->minVisibleItems) {
      // Some items might get lost due to having an empty title.
      return array();
    }
    return $items;
  }
}
