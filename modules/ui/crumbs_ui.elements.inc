<?php
use Drupal\crumbs_ui\FormElement\ElementInterface;
use Drupal\crumbs_ui\FormElement\Theme\ElementThemeInterface;

/**
 * Implements hook_element_info()
 *
 * @see crumbs_ui_element_info()
 */
function crumbs_ui_element_info() {
  $types['crumbs_ui_element'] = array(
    '#input' => TRUE,
    /** @see _crumbs_ui_element_value_callback() */
    '#value_callback' => '_crumbs_ui_element_value_callback',
    /** @see _crumbs_ui_element_process() */
    '#process' => array('_crumbs_ui_element_process'),
    /** @see _crumbs_ui_element_after_build() */
    # '#after_build' => array('_crumbs_ui_element_after_build'),
    /** @see _crumbs_ui_element_validate() */
    # '#element_validate' => array('_crumbs_ui_element_validate'),
    '#tree' => TRUE,
    '#theme' => 'crumbs_ui_element',
  );
  return $types;
}

/**
 * Value callback for tabledrag element ($element['#value_callback']).
 *
 * @param array $element
 * @param array|bool|NULL $input
 * @param array $form_state
 *
 * @throws Exception
 * @return array|bool
 */
function _crumbs_ui_element_value_callback(&$element, $input = FALSE, $form_state = array()) {
  return _crumbs_ui_element_object($element)->value_callback($element, $input, $form_state);
}

/**
 * Processor callback for tabledrag element ($element['#process']).
 * See form_process_checkboxes() for an example how these processors work.
 *
 * @param array $element
 * @param array $form_state
 *
 * @throws Exception
 * @return array
 */
function _crumbs_ui_element_process($element, $form_state) {
  $object = _crumbs_ui_element_object($element);
  if ($object instanceof \Drupal\crumbs_ui\FormElement\PreRenderInterface) {
    /** @see _crumbs_ui_element_pre_render() */
    $element['#pre_render'][] = '_crumbs_ui_element_pre_render';
  }
  return _crumbs_ui_element_object($element)->process($element, $form_state);
}

/**
 * Get the object representing the form element type.
 *
 * @param array $element
 *
 * @return ElementInterface
 *
 * @throws RuntimeException
 */
function _crumbs_ui_element_object(&$element) {
  if (!isset($element['#crumbs_ui_element_object'])) {
    throw new \RuntimeException("Element object missing in element.");
  }
  $object = $element['#crumbs_ui_element_object'];
  if (!$object instanceof ElementInterface) {
    throw new \RuntimeException("Element object has wrong type.");
  }
  return $object;
}

/**
 * Callback for $element['#pre_render'].
 *
 * @param array $element
 *
 * @throws Exception
 * @return array
 */
function _crumbs_ui_element_pre_render($element) {
  $object = _crumbs_ui_element_object($element);
  if (!$object instanceof \Drupal\crumbs_ui\FormElement\PreRenderInterface) {
    throw new RuntimeException('Element object must implement PreRenderInterface.');
  }
  return $object->pre_render($element);
}

/**
 * @param array $vars
 *
 * @return string
 *   Rendered HTML.
 */
function theme_crumbs_ui_element(array &$vars) {
  $element = $vars['element'];
  if (!isset($element['#crumbs_ui_theme_object'])) {
    throw new \RuntimeException("Element object missing in element.");
  }
  $object = $element['#crumbs_ui_theme_object'];
  if (!$object instanceof ElementThemeInterface) {
    throw new \RuntimeException("Theme object has wrong type.");
  }
  return $object->theme($element);
}

/**
 * @param array $vars
 *
 * @return string
 *
 * @see theme_form_element()
 */
function theme_crumbs_ui_form_element(array &$vars) {
  $element = &$vars['element'];

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  $attributes = isset($element['#crumbs_ui_container_attributes'])
    ? $element['#crumbs_ui_container_attributes']
    : array();

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes['class'][] = 'form-item';
  if (!empty($element['#type'])) {
    $attributes['class'][] = 'form-type-' . strtr($element['#type'], '_', '-');
  }
  if (!empty($element['#name'])) {
    $attributes['class'][] = 'form-item-' . strtr($element['#name'], array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
    $attributes['class'][] = 'form-disabled';
  }
  $output = '<div' . drupal_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';

  switch ($element['#title_display']) {
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $vars);
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $vars) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  if (!empty($element['#description'])) {
    $output .= '<div class="description">' . $element['#description'] . "</div>\n";
  }

  $output .= "</div>\n";

  return $output;
}

/**
 * @param array $vars
 *
 * @return string
 *
 * @see theme_form_element()
 */
function theme_crumbs_ui_inline_element(array &$vars) {
  $element = &$vars['element'];

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';

  $html = $prefix . $element['#children'] . $suffix;

  switch ($element['#title_display']) {
    case 'before':
    case 'invisible':
      $html = theme('form_element_label', $vars) . $html;
      break;

    case 'after':
      $html .= theme('form_element_label', $vars);
      break;
  }

  if (!empty($element['#description'])) {
    $html .= '<div class="description">' . $element['#description'] . "</div>\n";
  }

  $attributes = isset($element['#crumbs_ui_container_attributes'])
    ? $element['#crumbs_ui_container_attributes']
    : array();

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes['class'][] = 'form-item';
  if (!empty($element['#type'])) {
    $attributes['class'][] = 'form-type-' . strtr($element['#type'], '_', '-');
  }
  if (!empty($element['#name'])) {
    $attributes['class'][] = 'form-item-' . strtr($element['#name'], array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
    $attributes['class'][] = 'form-disabled';
  }
  $html = '<span' . drupal_attributes($attributes) . '>' . $html . '</span>';

  return $html;

}
