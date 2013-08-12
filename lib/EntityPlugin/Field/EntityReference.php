<?php

class crumbs_EntityPlugin_Field_EntityReference extends crumbs_EntityPlugin_Field_Abstract {

  /**
   * @inheritdoc
   */
  function fieldFindCandidate(array $items) {
    $field = $this->getFieldInfo();
    foreach ($items as $item) {
      if (1
        && !empty($item['target_id'])
        && $uri = entity_uri($field['settings']['target_type'], $item['target_id'])
      ) {
        return $uri['path'];
      }
    }
  }
}