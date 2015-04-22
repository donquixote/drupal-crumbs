<?php

namespace Drupal\crumbs_ui\Form;

interface FormBuilderInterface {

  /**
   * @param array $form
   * @param array $form_state
   *
   * @return array
   *   The form array.
   */
  public function buildForm($form, $form_state);
}
