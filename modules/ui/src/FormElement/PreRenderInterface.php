<?php

namespace Drupal\crumbs_ui\FormElement;

interface PreRenderInterface {

  /**
   * @param array $element
   *
   * @return array
   */
  public function pre_render($element);
}
