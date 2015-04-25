<?php

namespace Drupal\crumbs_ui\Widget;

class RouterItemDemo implements WidgetInterface {

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
    $build = array();
    $routerItem = $this->routerItem;
    if (function_exists('krumo_ob')) {
      $build['#markup'] = krumo_ob($routerItem);
    }
    else {
      $build['#markup'] = t('Devel is currently not installed.');
    }
    return $build;
  }
}
