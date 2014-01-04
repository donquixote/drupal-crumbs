<?php

/**
 * Page callback for 'crumbs/special-menu-item/%menu_link'
 *
 * @param array $menu_link
 */
function crumbs_special_menu_link_page(array $menu_link) {
  drupal_goto('<front>');
}

/**
 * Title callback for 'crumbs/special-menu-item/%menu_link'
 *
 * @param array $menu_link
 */
function crumbs_special_menu_link_title(array $menu_link) {
  return $menu_link['title'];
}