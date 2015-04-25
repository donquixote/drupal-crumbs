<?php

namespace Drupal\crumbs\TrailFinder;

use Drupal\crumbs\ParentFinder\Approval\AccessChecker;
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

    $routerItem = $this->router->getRouterItem($path);
    if (!is_array($routerItem)) {
      return array();
    }

    $reverseTrail = array();
    $checker = new AccessChecker($this->router);

    while (TRUE) {

      $path = $routerItem['link_path'];

      if (isset($reverseTrail[$path])) {
        // We found a loop! To prevent infinite recursion, we remove the loopy
        // paths from the trail and return the part that is not loopy.
        while (isset($reverseTrail[$path])) {
          array_pop($reverseTrail);
        }
        \Drupal\krumong\dpm('BIG LOOP');
        break;
      }

      // If this menu item is a default local task and links to its parent,
      // skip it and start the search from the parent instead.
      if ($routerItem['type'] & MENU_LINKS_TO_PARENT) {
        $path = $routerItem['tab_parent_href'];
        $routerItem = $this->router->getRouterItem($routerItem['tab_parent_href']);
        if (!is_array($routerItem)) {
          \Drupal\krumong\dpm('NO ROUTER ITEM FOR MENU_LINKS_TO_PARENT');
          break;
        }
      }

      // Add the item to the trail.
      // Items with no access will be removed later.
      $reverseTrail[$path] = $routerItem;

      if (!$this->parentFinder->findParentRouterItem($routerItem, $checker)) {
        break;
      }
      $parentRouterItem = $checker->getParentRouterItem();
      if (!is_array($parentRouterItem)) {
        \Drupal\krumong\dpm("Parent router item is NULL.");
        break;
      }

      if ($parentRouterItem['link_path'] === $path) {
        // This is again a loop, but with just one step.
        // Not as evil as the other kind of loop.
        \Drupal\krumong\dpm('SHORT LOOP');
        break;
      }

      // Continue with the parent.
      $routerItem = $parentRouterItem;
    }

    return $reverseTrail;
  }

}
