<?php
use Drupal\crumbs_ui\Controller\ControllerInterface;

/**
 * Page callback for all crumbs_ui pages that are not forms.
 *
 * @param \Drupal\crumbs_ui\Controller\ControllerInterface $controller
 *
 * @return string|array
 */
function _crumbs_ui_page(ControllerInterface $controller) {
  return $controller->handle();
}
