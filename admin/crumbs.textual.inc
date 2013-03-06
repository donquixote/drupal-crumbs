<?php


function theme_crumbs_weights_textual(&$vars) {

  $element = $vars['element'];
  return drupal_render($element['text']);
}
