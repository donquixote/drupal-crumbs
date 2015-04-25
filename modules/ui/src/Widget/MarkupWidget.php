<?php

namespace Drupal\crumbs_ui\Widget;

class MarkupWidget implements WidgetInterface {

  /**
   * @var string
   */
  private $markup;

  /**
   * @param string $markup
   */
  function __construct($markup) {
    $this->markup = $markup;
  }

  /**
   * @return array
   *   A render array.
   */
  function build() {
    return array('#markup' => $this->markup);
  }
}
