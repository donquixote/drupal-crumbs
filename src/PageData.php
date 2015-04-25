<?php

namespace Drupal\crumbs;

use Drupal\crumbs\Container\DataContainerBase;
use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface;

use Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatterInterface;
use Drupal\crumbs\Router\RouterInterface;
use Drupal\crumbs\TrailFinder\TrailFinderInterface;

/**
 * Creates various data related to the current page.
 *
 * The data is provided to the rest of the world via crumbs_Container_LazyData.
 * Each method in here corresponds to one key on the data cache.
 *
 * The $page argument on each method is the data cache itself.
 * The argument can be mocked with a simple stdClass, to test the behavior of
 * each method. (if we had the time to write unit tests)
 *
 * @property bool $breadcrumbSuppressed
 * @property array $breadcrumbData
 * @property array $trail
 * @property array $breadcrumbItems
 * @property string $breadcrumbHtml
 * @property string $path
 * @property array $routerItem
 *
 * @see crumbs_Container_AbstractLazyData::__get()
 * @see crumbs_Container_AbstractLazyData::__set()
 */
class PageData extends DataContainerBase {

  /**
   * @var \Drupal\crumbs\TrailFinder\TrailFinderInterface
   */
  protected $trailFinder;

  /**
   * @var \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface
   */
  protected $breadcrumbBuilder;

  /**
   * @var \Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatterInterface
   */
  protected $breadcrumbFormatter;

  /**
   * @var \Drupal\crumbs\Router\RouterInterface
   */
  protected $router;

  /**
   * @param \Drupal\crumbs\TrailFinder\TrailFinderInterface $trailFinder
   * @param \Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderInterface $breadcrumbBuilder
   * @param \Drupal\crumbs\BreadcrumbFormatter\BreadcrumbFormatterInterface $breadcrumbFormatter
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(
    TrailFinderInterface $trailFinder,
    BreadcrumbBuilderInterface $breadcrumbBuilder,
    BreadcrumbFormatterInterface $breadcrumbFormatter,
    RouterInterface $router
  ) {
    $this->trailFinder = $trailFinder;
    $this->breadcrumbBuilder = $breadcrumbBuilder;
    $this->breadcrumbFormatter = $breadcrumbFormatter;
    $this->router = $router;
  }

  /**
   * Check if the breadcrumb is to be suppressed altogether.
   *
   * @return bool
   *
   * @see crumbs_CurrentPageInfo::$breadcrumbSuppressed
   */
  protected function breadcrumbSuppressed() {
    // @todo Find the corresponding issue on drupal.org.
    // This has to be drupal_set_breadcrumb(), not drupal_get_breadcrumb(), to
    // avoid menu_get_active_breadcrumb() from being called.
    $existing_breadcrumb = drupal_set_breadcrumb();
    // If the existing breadcrumb is empty, that means a module has
    // intentionally removed it. Honor that, and stop here.
    return isset($existing_breadcrumb) && empty($existing_breadcrumb);
  }

  /**
   * Assemble all breadcrumb data.
   *
   * @return array
   *
   * @see crumbs_CurrentPageInfo::$breadcrumbData
   */
  protected function breadcrumbData() {
    if (empty($this->breadcrumbItems)) {
      return FALSE;
    }
    return array(
      'trail' => $this->trail,
      'items' => $this->breadcrumbItems,
      'html' => $this->breadcrumbHtml,
    );
  }

  /**
   * Build the Crumbs trail.
   *
   * @return array
   *
   * @see crumbs_CurrentPageInfo::$trail
   */
  protected function trail() {
    return $this->trailFinder->buildTrail($this->path);
  }

  /**
   * Build altered breadcrumb items.
   *
   * Each breadcrumb item is a router item taken from the trail, with
   * two additional/updated keys:
   * - title: The title of the breadcrumb item as received from a plugin.
   * - localized_options: An array of options passed to l() if needed.
   *
   * @return array
   *
   * @see crumbs_CurrentPageInfo::$breadcrumbItems
   */
  protected function breadcrumbItems() {
    if ($this->breadcrumbSuppressed) {
      return array();
    }

    return $this->breadcrumbBuilder->buildBreadcrumb($this->trail);
  }

  /**
   * Build the breadcrumb HTML.
   *
   * @return string
   *
   * @see crumbs_CurrentPageInfo::$breadcrumbHtml
   */
  protected function breadcrumbHtml() {
    return $this->breadcrumbFormatter->formatBreadcrumb(
      $this->breadcrumbItems,
      $this->trail
    );
  }

  /**
   * Determine current path.
   *
   * @return string
   *
   * @see crumbs_CurrentPageInfo::$path
   */
  protected function path() {
    return $_GET['q'];
  }

  /**
   * Router item for the current path.
   *
   * Only used on debug pages.
   *
   * @return array
   *
   * @see crumbs_CurrentPageInfo::$routerItem
   */
  protected function routerItem() {
    return $this->router->getRouterItem($this->path);
  }

}
