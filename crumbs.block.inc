<?php

/**
 * Implements hook_block_info()
 */
function crumbs_block_info() {

  return array(
    'breadcrumb' => array(
      'info' => t('Breadcrumb (Crumbs)'),
      'cache' => DRUPAL_NO_CACHE,
    ),
  );
}
