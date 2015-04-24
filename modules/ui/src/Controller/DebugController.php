<?php

namespace Drupal\crumbs_ui\Controller;

use Drupal\crumbs_ui\Widget\PathSelectorWidget;

class DebugController implements ControllerInterface {

  /**
   * The main controller method.
   *
   * @return string|array
   */
  function handle() {

    // @todo Is this needed?
    drupal_set_title('Crumbs debug');

    $pathSelectorWidget = new PathSelectorWidget();
    return $pathSelectorWidget->build();
  }
}
