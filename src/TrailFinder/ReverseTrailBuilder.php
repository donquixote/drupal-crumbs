<?php

namespace Drupal\crumbs\TrailFinder;

use Drupal\crumbs\ParentFinder\ParentFinderInterface;
use Drupal\crumbs\Router\RouterInterface;

class ReverseTrailBuilder implements TrailFinderInterface {

  /**
   * @var \Drupal\crumbs\ParentFinder\ParentFinderInterface
   */
  protected $parentFinder;

  /**
   * @var \Drupal\crumbs\Router\RouterInterface;
   */
  protected $router;

  /**
   * @param \Drupal\crumbs\ParentFinder\ParentFinderInterface $parent_finder
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(ParentFinderInterface $parent_finder, RouterInterface $router) {
    $this->parentFinder = $parent_finder;
    $this->router = $router;
  }

  /**
   * Build the raw trail.
   *
   * @param string $path
   *
   * @return array[]
   *   The trail items in reverse order.
   *   Format: $[$normalpath] = $router_item
   */
  function buildTrail($path) {
    $path = $this->router->getNormalPath($path);
    $trail_reverse = array();
    while (isset($path)) {
      if (isset($trail_reverse[$path])) {
        // We found a loop! To prevent infinite recursion, we remove the loopy
        // paths from the trail and return the part that is not loopy.
        while (isset($trail_reverse[$path])) {
          array_pop($trail_reverse);
        }
        return $trail_reverse;
      }
      $item = $this->router->getRouterItem($path);
      if (!is_array($item)) {
        return $trail_reverse;
      }
      // If this menu item is a default local task and links to its parent,
      // skip it and start the search from the parent instead.
      if ($item['type'] & MENU_LINKS_TO_PARENT) {
        $path = $item['tab_parent_href'];
        $item = $this->router->getRouterItem($item['tab_parent_href']);
        if (!is_array($item)) {
          return $trail_reverse;
        }
      }

      $trail_reverse[$path] = $item;

      // For a path to be included in the trail, it must resolve to a valid
      // router item, and the access check must pass.
      if ($item['access']) {
      }
      $parent_path = $this->parentFinder->findParent($path, $item);
      if ($parent_path === $path) {
        // This is again a loop, but with just one step.
        // Not as evil as the other kind of loop.
        return $trail_reverse;
      }
      $path = $parent_path;
    }

    return $trail_reverse;
  }

}
