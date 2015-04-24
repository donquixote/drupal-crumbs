<?php

namespace Drupal\crumbs_ui\Controller;

interface ControllerInterface {

  /**
   * The main controller method.
   *
   * @return string|array
   */
  function handle();
}
