<?php

/**
 * Implements hook_permission().
 */
function crumbs_permission() {
  return array(
    'administer crumbs' => array(
      'title' => t('Administer Crumbs'),
    ),
  );
}
