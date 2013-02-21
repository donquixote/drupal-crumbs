<?php


function theme_crumbs_weights_textual(&$vars) {

  $element = $vars['element'];

  $sort_enabled = array();
  foreach (element_children($element) as $key) {
    $child = $element[$key];
    $weight = $child['weight']['#value'];
    $section_key = $child['#section_key'];
    if ($section_key === 'enabled') {
      $sort_enabled[$key] = $weight;
    }
    if ($child['weight']['#type'] === 'hidden') {
      // That's a section label, not a regular row.
      $description = @$child['#description'];
      $title = $child['#title'];
      unset($child['#description']);
      unset($child['#title']);
      $table_sections[$section_key][] = array(
        'data' => array(
          array(
            'data' => '<h3>' . $title . '</h3>' . drupal_render($child),
            'colspan' => 3,
          ),
        ),
      );
    }
    else {
      $child['weight']['#attributes']['class'][] = 'crumbs-weight-element';
      $description = @$child['#description'];
      $title = $child['#title'];
      unset($child['#description']);
      unset($child['#title']);
      $table_sections[$section_key][] = array(
        'data' => array(
          $title,
          drupal_render($child),
          $description,
        ),
        'class' => array('draggable'),
      );
    }
  }

  array_multisort($sort_enabled, $table_sections['enabled']);

  $table_rows = array();
  foreach ($table_sections as $section_key => $section_rows) {
    $table_rows = array_merge($table_rows, $section_rows);
  }

  drupal_add_tabledrag('crumbs_weights_tabledrag', 'order', 'sibling', 'crumbs-weight-element');

  return theme('table', array(
    'rows' => $table_rows,
    'attributes' => array('id' => 'crumbs_weights_tabledrag'),
  ));
}
