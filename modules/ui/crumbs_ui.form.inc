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
    return $formBuilder->buildForm($form, $form_state);
  }
  catch (\Exception $e) {
    drupal_set_message($e->getMessage());
    return NULL;
  }
}

/**
 * Implements hook_form_FORM_ID_alter()
 *
 * This should happen AFTER other form_alter() implementations.
 *
 * @param array $form
 * @param array $form_state
 */
function crumbs_ui_form__crumbs_ui_form_alter(array &$form, array $form_state) {

  // Allow OOP submit handlers.
  $form['#crumbs_ui-form-submit'] = !empty($form['#submit'])
    ? $form['#submit']
    : array();
  $form['#submit'] = array('_crumbs_ui_form_submit');
}

/**
 * Generic submit handler.
 *
 * @param array $form
 * @param array $form_state
 */
function _crumbs_ui_form_submit(array $form, array &$form_state) {
  foreach ($form['#crumbs_ui-form-submit'] as $submitHandler) {
    if (is_object($submitHandler)) {
      if (!$submitHandler instanceof FormSubmitInterface) {
        throw new \RuntimeException('Invalid submit handler object.');
      }
      $submitHandler->submit($form, $form_state);
    }
    elseif (is_string($submitHandler)) {
      if (!function_exists($submitHandler)) {
        throw new \RuntimeException('Undefined submit handler function.');
      }
      $submitHandler($form, $form_state);
    }
    else {
      throw new \RuntimeException('Invalid submit handler.');
    }
  }
}

/**
 * @param array $form_state
 *
 * @return FormBuilderInterface
 */
function _crumbs_ui_require_form_builder(array $form_state) {
  if (!isset($form_state['build_info']['args'][0])) {
    throw new \RuntimeException("No form handler object in form state.");
  }
  $formHandler = $form_state['build_info']['args'][0];
  if (!$formHandler instanceof FormBuilderInterface) {
    throw new \RuntimeException("Invalid form handler.");
  }
  return $formHandler;
}
