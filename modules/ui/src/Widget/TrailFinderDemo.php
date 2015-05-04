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
        $this->renderTrail($trail),
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

  /**
   * @param array[] $trail
   *
   * @return string
   */
  private function renderTrail(array $trail) {
    $rows = array();
    foreach ($trail as $path => $trailItem) {
      $rows[] = array(
        $path,
        isset($trailItem['title'])
          ? check_plain($trailItem['title'])
          : '',
      );
    }
    return theme('table', array(
      'rows' => $rows,
    ));
  }
}
