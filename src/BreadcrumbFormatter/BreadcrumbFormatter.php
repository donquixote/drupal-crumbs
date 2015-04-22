<?php

namespace Drupal\crumbs\BreadcrumbFormatter;

class BreadcrumbFormatter implements BreadcrumbFormatterInterface {

  /**
   * @var bool
   */
  private $showCurrentPage;

  /**
   * @var string
   */
  private $separator;

  /**
   * @var bool
   */
  private $separatorSpan;

  /**
   * @var bool
   */
  private $trailingSeparator;

  /**
   * @param bool $showCurrentPage
   * @param string $separator
   * @param bool $separatorSpan
   * @param bool $trailingSeparator
   */
  function __construct($showCurrentPage, $separator, $separatorSpan, $trailingSeparator) {
    $this->showCurrentPage = $showCurrentPage;
    $this->separator = $separator;
    $this->separatorSpan = $separatorSpan;
    $this->trailingSeparator = $trailingSeparator;
  }

  /**
   * @param array $breadcrumb_items
   * @param array $trail
   *
   * @return string
   * @throws \Exception
   */
  function formatBreadcrumb(array $breadcrumb_items, array $trail) {
    if (empty($breadcrumb_items)) {
      return '';
    }
    $links = array();
    if ($this->showCurrentPage) {
      $last = array_pop($breadcrumb_items);
      foreach ($breadcrumb_items as $i => $item) {
        $links[$i] = theme('crumbs_breadcrumb_link', $item);
      }
      $links[] = theme('crumbs_breadcrumb_current_page', array(
        'item' => $last,
        'show_current_page' => $this->showCurrentPage,
      ));
    }
    else {
      foreach ($breadcrumb_items as $i => $item) {
        $links[$i] = theme('crumbs_breadcrumb_link', $item);
      }
    }
    return theme('breadcrumb', array(
      'breadcrumb' => $links,
      'crumbs_breadcrumb_items' => $breadcrumb_items,
      'crumbs_trail' => $trail,
      'crumbs_separator' => $this->separator,
      'crumbs_separator_span' => $this->separatorSpan,
      'crumbs_trailing_separator' => $this->trailingSeparator,
    ));
  }
}
