<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\TitleFinder\TitleFinderDecoratorBase;

class TitleFinderDemo implements WidgetInterface {

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

    $rows = array();

    $path = $this->routerItem['link_path'];

    $titleFinder = crumbs()->titleFinder;
    while (TRUE) {
      $title = $titleFinder->findTitle($path, $this->routerItem);
      $rows[] = array(
        get_class($titleFinder),
        var_export($title, TRUE),
      );
      if (!$titleFinder instanceof TitleFinderDecoratorBase) {
        break;
      }
      $titleFinder = $titleFinder->getDecorated();
    }

    $rows = array_reverse($rows);

    return array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => array(
        t('Title finder class'),
        t('Title'),
      ),
    );
  }
}
