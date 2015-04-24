<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
use Drupal\crumbs\ParentFinder\ParentFinderInterface;
use Drupal\crumbs\Router\RouterInterface;

class ParentFinderEngine implements ParentFinderInterface {

  /**
   * @var \crumbs_MonoPlugin_FindParentInterface[]
   *   Format: $[$pluginKey] = $plugin
   */
  private $pluginsSorted;

  /**
   * @var RouterInterface
   */
  protected $router;

  /**
   * @param \crumbs_MonoPlugin_FindParentInterface[] $pluginsSorted
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(array $pluginsSorted, RouterInterface $router) {
    $this->pluginsSorted = $pluginsSorted;
    $this->router = $router;
  }

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return array|NULL
   *   The parent router item, or NULL.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {
    $path = $routerItem['link_path'];
    foreach ($this->pluginsSorted as $key => $pluginOrWrapper) {
      if ($pluginOrWrapper instanceof ParentFinderInterface) {
        if ($pluginOrWrapper->findParentRouterItem($routerItem, $checker)) {
          return TRUE;
        }
      }
      elseif ($pluginOrWrapper instanceof \crumbs_MonoPlugin_FindParentInterface) {
        $parentPathCandidate = $pluginOrWrapper->findParent($path, $routerItem);
        if (isset($parentPathCandidate)) {
          if ($checker->checkParentPath($parentPathCandidate, $key)) {
            return TRUE;
          }
        }
      }
      else {
        throw new \RuntimeException("Need either a mono plugin or a multi plugin offset.");
      }
    }

    // If nothing was found, return NULL.
    return NULL;
  }
}
