<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\ParentFinder\Approval\DebugCollectorChecker;
use Drupal\crumbs\ParentFinder\ParentFinderDecoratorBase;

use Drupal\crumbs_ui\PluginKey\RawHierarchyInterface;

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

    $table = new PluginResultTable($collected, $descriptions, crumbs()->router);

    return $table->build();
  }

  private function buildTableRows(
    RawHierarchyInterface $hierarchy,
    array $collected,
    array $descriptions,
    $key
  ) {
    $rows = array();
    foreach ($hierarchy->keyGetChildren($key) as $childKey) {
      if ($hierarchy->keyIsWildcard($childKey)) {
        $rows[$childKey] = $this->buildParentRow($childKey);
        $rows += $this->buildTableRows($hierarchy, $collected, $descriptions, $childKey);
      }
      else {
        $rows[$childKey] = $this->buildLeafRow($childKey);
      }
    }
    return $rows;
  }
}
