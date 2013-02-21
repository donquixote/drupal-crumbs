<?php

class crumbs_Admin_ElementObject_Abstract {

  function __construct($element) {
    // We do NOT store the element, because it might be replaced by other callbacks.
  }

  function value_callback(&$element, $input = FALSE, $form_state = array()) {

    // TODO: What is the correct "neutral" behavior of a validation callback?
    if ($input === FALSE) {
      return isset($element['#default_value']) ? $element['#default_value'] : array();
    }
    else {
      return $input;
    }
  }

  function process($element, $form_state) {
    return $element;
  }

  function after_build($element, $form_state) {
    return $element;
  }

  function validate(&$element, &$form_state) {
    // TODO: What is the correct "neutral" behavior of a validation callback?
    return TRUE;
  }

  function pre_render($element) {
    return $element;
  }
}
