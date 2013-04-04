<?php

/**
 * Creates various data related to the current page.
 *
 * The data is provided to the rest of the world via crumbs_Container_LazyData.
 * Each method in here corresponds to one key on the data cache.
 *
 * The $page argument on each method is the data cache itself.
 * The argument can be mocked with a simple stdClass, to test the behavior of
 * each method. (if we had the time to write unit tests)
 */
class crumbs_CurrentPageInfo {

  protected $trails;
  protected $breadcrumbBuilder;

  function __construct($trails, $breadcrumbBuilder) {
    $this->trails = $trails;
    $this->breadcrumbBuilder = $breadcrumbBuilder;
  }

  /**
   * Check if the breadcrumb is to be suppressed altogether.
   */
  function breadcrumbSuppressed($page) {
    return FALSE;
    $existing_breadcrumb = drupal_get_breadcrumb();
    // If the existing breadcrumb is empty, that means a module has
    // intentionally removed it. Honor that, and stop here.
    return empty($existing_breadcrumb);
  }

  /**
   * Assemble all breadcrumb data.
   */
  function breadcrumbData($page) {
    if (empty($page->breadcrumbItems)) {
      return FALSE;
    }
    return array(
      'trail' => $page->trail,
      'items' => $page->breadcrumbItems,
      'html' => $page->breadcrumbHtml,
    );
  }

  /**
   * Build the Crumbs trail.
   */
  function trail($page) {
    return $this->trails->getForPath($page->path);
  }

  /**
   * Build the raw breadcrumb based on the $page->trail.
   *
   * Each breadcrumb item is a router item taken from the trail, with
   * two additional/updated keys:
   * - title: The title of the breadcrumb item as received from a plugin.
   * - localized_options: An array of options passed to l() if needed.
   *
   * The altering will happen in a separate step, so 
   */
  function rawBreadcrumbItems($page) {
    if ($page->breadcrumbSuppressed) {
      return array();
    }
    $trail = $page->trail;
    if (count($trail) < $page->minTrailItems) {
      return array();
    }
    if (!$page->showFrontPage) {
      array_shift($trail);
    }
    if (!$page->showCurrentPage) {
      array_pop($trail);
    }
    if (!count($trail)) {
      return array();
    }
    $items = $this->breadcrumbBuilder->buildBreadcrumb($trail);
    if (count($items) < $page->minVisibleItems) {
      // Some items might get lost due to having an empty title.
      return array();
    }
    return $items;
  }

  /**
   * Determine if we want to show the breadcrumb item for the current page.
   */
  function showCurrentPage($page) {
    return variable_get('crumbs_show_current_page', FALSE);
  }

  /**
   * Determine if we want to show the breadcrumb item for the front page.
   */
  function showFrontPage($page) {
    return variable_get('crumbs_show_front_page', TRUE);
  }

  /**
   * If there are fewer trail items than this, we hide the breadcrumb.
   */
  function minTrailItems($page) {
    return variable_get('crumbs_minimum_trail_items', 2);
  }

  /**
   * If there are fewer visible items than this, we hide the breadcrumb.
   * Every "trail item" does become a "visible item", except when it is hidden:
   * - The frontpage item might be hidden based on a setting.
   * - The current page item might be hidden based on a setting.
   * - Any item where the title is FALSE will be hidden / skipped over.
   */
  function minVisibleItems($page) {
    $n = $page->minTrailItems;
    if (!$page->showCurrentPage) {
      --$n;
    }
    if (!$page->showFrontPage) {
      --$n;
    }
    return $n;
  }

  /**
   * Build altered breadcrumb items.
   */
  function breadcrumbItems($page) {
    $breadcrumb_items = $page->rawBreadcrumbItems;
    if (empty($breadcrumb_items)) {
      return array();
    }
    $router_item = crumbs_get_router_item($page->path);
    // Allow modules to alter the breadcrumb, if possible, as that is much
    // faster than rebuilding an entirely new active trail.
    drupal_alter('menu_breadcrumb', $breadcrumb_items, $router_item);
    return $breadcrumb_items;
  }

  /**
   * Build the breadcrumb HTML.
   */
  function breadcrumbHtml($page) {
    $breadcrumb_items = $page->breadcrumbItems;
    if (empty($breadcrumb_items)) {
      return '';
    }
    $links = array();
    $last_item = end($breadcrumb_items);
    foreach ($breadcrumb_items as $i => $item) {
      if ($page->showCurrentPage && $item == $last_item) {
        // The current page should be styled differently (no link).
        $links[$i] = theme('crumbs_breadcrumb_current_page', $item);
      }
      else {
        $links[$i] = theme('crumbs_breadcrumb_link', $item);
      }
    }
    return theme('breadcrumb', array(
      'breadcrumb' => $links,
      'crumbs_breadcrumb_items' => $breadcrumb_items,
      'crumbs_trail' => $page->trail,
    ));
  }

  /**
   * Determine current path.
   */
  function path($page) {
    return $_GET['q'];
  }
}
