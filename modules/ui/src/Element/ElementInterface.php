<?php

namespace Drupal\crumbs_ui\Element;

/**
 * @see crumbs_ui_element_info()
 */
interface ElementInterface {

  /**
   * @param array $element
   * @param array|FALSE $input
   * @param array $form_state
   *
   * @return mixed
   *
   * @see _crumbs_ui_element_value_callback()
   */
  public function value_callback(array $element, $input, array $form_state);

  /**
   * @param array $element
   * @param array $form_state
   *
   * @return array
   *   The modified form element.
   *
   * @see _crumbs_ui_element_process()
   */
  public function process($element, $form_state);
}
