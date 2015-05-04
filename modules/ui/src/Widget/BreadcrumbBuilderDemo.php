<?php

namespace Drupal\crumbs_ui\Widget;

use Drupal\crumbs\BreadcrumbBuilder\BreadcrumbBuilderDecoratorBase;

class BreadcrumbBuilderDemo implements WidgetInterface {

  /**
   * @var array[]
   */
  private $trail;

  /**
   * @param array[] $trail
   */
  function __construct(array $trail) {
    $this->trail = $trail;
  }

  /**
   * @return array
   *   A render array.
   */
  function build() {
    $rows = array();
    $breadcrumbBuilder = crumbs()->breadcrumbBuilder;
    while (TRUE) {
      $class = get_class($breadcrumbBuilder);
      $breadcrumb = $breadcrumbBuilder->buildBreadcrumb($this->trail);
      $rows[] = array(
        '<code>' . $class . '</code>',
        $this->renderBreadcrumb($breadcrumb),
      );
      if (!$breadcrumbBuilder instanceof BreadcrumbBuilderDecoratorBase) {
        break;
      }
      $breadcrumbBuilder = $breadcrumbBuilder->getDecorated();
    }

    return array(
      '#theme' => 'table',
      '#rows' => array_reverse($rows),
      '#header' => array(
        t('Breadcrumb builder class'),
        t('Breadcrumb items'),
      ),
    );
  }

  /**
   * @param array[] $breadcrumb
   *
   * @return string
   */
  private function renderBreadcrumb($breadcrumb) {
    $rows = array();
    foreach ($breadcrumb as $item) {
      $rows[] = array(
        $item['link_path'],
        isset($item['title']) ? $item['title'] : '?',
      );
    }
    return theme('table', array('rows' => $rows));
  }
}
