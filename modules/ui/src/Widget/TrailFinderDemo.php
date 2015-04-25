<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\TrailFinder\TrailFinderDecoratorBase;

class TrailFinderDemo implements WidgetInterface {

  /**
   * @var string
   */
  private $path;

  /**
   * @param string $path
   */
  function __construct($path) {
    $this->path = $path;
  }

  /**
   * @return array
   *   A render array.
   */
  function build() {
    $rows = array();
    $trailFinder = crumbs()->trailFinder;
    while (TRUE) {
      $class = get_class($trailFinder);
      $trail = $trailFinder->buildTrail($this->path);
      $rows[] = array(
        '<code>' . $class . '</code>',
        '<code>' . implode(' &raquo; ', array_keys($trail)) . '</code>',
      );
      if (!$trailFinder instanceof TrailFinderDecoratorBase) {
        break;
      }
      $trailFinder = $trailFinder->getDecorated();
    }
    return array(
      '#theme' => 'table',
      '#rows' => array_reverse($rows),
      '#header' => array(
        t('Trail finder class'),
        t('Trail paths'),
      ),
    );
  }
}
