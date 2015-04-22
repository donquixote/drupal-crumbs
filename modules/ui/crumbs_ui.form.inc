<?php
use Drupal\crumbs_ui\Form\FormBuilderInterface;
use Drupal\crumbs_ui\Form\FormSubmitInterface;

/**
 * Generic form builder.
 *
 * @param array $form
 * @param array $form_state
 * @param \Drupal\crumbs_ui\Form\FormBuilderInterface $formBuilder
 *
 * @return array|NULL
 */
function _crumbs_ui_form(array $form, array $form_state, FormBuilderInterface $formBuilder) {
  try {
    $form = $formBuilder->buildForm($form, $form_state);
    if ($formBuilder instanceof FormSubmitInterface) {
      $form['#submit'][] = '_crumbs_ui_form_submit';
    }
    return $form;
  }
  catch (\Exception $e) {
    drupal_set_message($e->getMessage());
    return NULL;
  }
}

/**
 * Generic submit handler.
 *
 * @param $form
 * @param $form_state
 */
function _crumbs_ui_form_submit($form, &$form_state) {
  if (!isset($form_state['build_info']['args'][0])) {
    throw new \RuntimeException("No form handler object in form state.");
  }
  $formHandler = $form_state['build_info']['args'][0];
  if (!$formHandler instanceof FormBuilderInterface) {
    throw new \RuntimeException("Invalid form handler.");
  }
  if ($formHandler instanceof FormSubmitInterface) {
    $formHandler->submit($form, $form_state);
  }
}
