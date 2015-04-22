<?php

namespace Drupal\crumbs_ui\Form;

/**
 * Optional interface for form handler objects.
 */
interface FormSubmitInterface {

  function submit($form, &$form_state);

}
