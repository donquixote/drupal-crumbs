<?php

class crumbs_EntityParentPlugin_Field_EntityReference extends crumbs_EntityParentPlugin_Field_Abstract {

  /**
   * @inheritdoc
   */
  function entityFindParent($entity, $entity_type, $distinction_key) {
    $items = field_get_items($entity_type, $entity, $this->fieldKey);
    if ($items) {
      $field = field_info_field($this->fieldKey);
      $item = $items[0];
      $target_id = $item['target_id'];
      // TODO: Use entity_uri() ?
      switch ($field['settings']['target_type']) {
        case 'node':
          return 'node/' . $target_id;
        case 'user':
          return 'user/' . $target_id;
        case 'taxonomy_term':
          return 'taxonomy/term/' . $target_id;
      }
    }
  }
}