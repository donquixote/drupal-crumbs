<?php

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
    $existing_breadcrumb = drupal_get_breadcrumb();
    // If the existing breadcrumb is empty, that means a module has
    // intentionally removed it. Honor that, and stop here.
    return empty($existing_breadcrumb);
  }

  /**
   * Assemble all breadcrumb data.
   */
  function breadcrumbData($page) {
    if ($page->breadcrumbSuppressed) {
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
      return FALSE;
    }
    $trail = $page->trail;
    if ($page->showCurrentPage) {
      // Do not show the breadcrumb if it contains only 1 item. This prevents
      // the breadcrumb from showing up on the frontpage.
      if (count($trail) == 1) {
        return FALSE;
      }
    }
    else {
      // Remove the last item, before building the breadcrumb.
      array_pop($trail);
    }
    return $this->breadcrumbBuilder->buildBreadcrumb($trail);
  }

  /**
   * Determine if we want to show the breadcrumb item for the current page.
   */
  function showCurrentPage($page) {
    return variable_get('crumbs_show_current_page', FALSE);
  }

  /**
   * Build altered breadcrumb items.
   */
  function breadcrumbItems($page) {
    $router_item = crumbs_get_router_item($page->path);
    $breadcrumb_items = $page->rawBreadcrumbItems;
    // Allow modules to alter the breadcrumb, if possible, as that is much
    // faster than rebuilding an entirely new active trail.
    drupal_alter('menu_breadcrumb', $breadcrumb_items, $router_item);
    return $breadcrumb_items;
  }

  /**
   * Build the breadcrumb HTML.
   */
  function breadcrumbHtml($page) {
    if ($page->breadcrumbSuppressed) {
      return FALSE;
    }
    $breadcrumb_items = $page->breadcrumbItems;
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
