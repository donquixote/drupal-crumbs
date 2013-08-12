<?php

class crumbs_EntityPlugin_Field_Link extends crumbs_EntityPlugin_Field_Abstract {

  /**
   * @inheritdoc
   */
  function fieldFindCandidate(array $items) {
    foreach ($items as $item) {
      Drupal\krumong\dpm($entity);
      Drupal\krumong\dpm($item);
    }
  }
}