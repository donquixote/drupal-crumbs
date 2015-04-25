<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\ParentFinder\Approval\AccessChecker;
use Drupal\crumbs\ParentFinder\ParentFinderDecoratorBase;

class ParentFinderDemo implements WidgetInterface {

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

    $router = crumbs()->router;

    $rows = array();

    $parentFinder = crumbs()->parentFinder;
    while (TRUE) {
      $checker = new AccessChecker($router);
      $success = $parentFinder->findParentRouterItem($this->routerItem, $checker);
      $parentRouterItem = $checker->getParentRouterItem();

      if (isset($parentRouterItem)) {
        $rows[] = array(
          get_class($parentFinder),
          $parentRouterItem['link_path'],
          $success ? t('Success') : t('No success'),
        );
      }
      else {
        $rows[] = array(
          get_class($parentFinder),
          '-',
          $success ? t('Success') : t('No success'),
        );
      }

      if (!$parentFinder instanceof ParentFinderDecoratorBase) {
        break;
      }
      $parentFinder = $parentFinder->getDecorated();
    }

    $rows = array_reverse($rows);

    return array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => array(
        t('Parent finder class.'),
        t('Parent path.'),
        t('Success?'),
      ),
    );
  }
}
