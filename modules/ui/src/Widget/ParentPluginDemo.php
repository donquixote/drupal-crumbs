<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\ParentFinder\Approval\DebugCollectorChecker;
use Drupal\crumbs\ParentFinder\ParentFinderDecoratorBase;

class ParentPluginDemo implements WidgetInterface {

  /**
   * @var array
   */
  private $routerItem;

  /**
   * @param array $routerItem
   */
  function __construct(array $routerItem) {
    $this->routerItem = $routerItem;
  }

  /**
   * @return array
   *   A render array.
   */
  function build() {

    // Get the plugin parent finder, remove all other decorator layers.
    $parentFinder = crumbs()->parentFinder;
    while ($parentFinder instanceof ParentFinderDecoratorBase) {
      $parentFinder = $parentFinder->getDecorated();
    }

    $collector = new DebugCollectorChecker();
    $parentFinder->findParentRouterItem($this->routerItem, $collector);

    $collected = $collector->getCollected();

    $labeledPluginCollection = crumbs()->labeledParentPluginCollection;
    $descriptions = $labeledPluginCollection->getDescriptions();

    $router = crumbs()->router;

    $rows = array();
    foreach ($collected as list($parentPath, $key)) {
      $row = array($key, $parentPath);
      $parentRouterItem = $router->getRouterItem($parentPath);
      if (!isset($parentRouterItem)) {
        $row[] = t('Denied, no router item.');
      }
      elseif (empty($parentRouterItem['access'])) {
        $row[] = t('Denied, no access.');
      }
      else {
        $row[] = t('Accepted.');
      }
      $rows[] = $row;
    }

    return array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => array(
        t('Plugin key'),
        t('Parent path candidate'),
        t('Accepted?'),
      ),
    );
  }
}
