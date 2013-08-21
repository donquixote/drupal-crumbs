<?php

class crumbs_EntityPlugin_Field_EntityReference extends crumbs_EntityPlugin_Field_Abstract {

  /**
   * @inheritdoc
   */
  function fieldFindCandidate(array $items) {
    $field = $this->getFieldInfo();
    if ($target_type = $field['settings']['target_type']) {
      foreach ($items as $item) {
        if (1
          && !empty($item['target_id'])
          && ($target_entity = reset(entity_load($target_type, array($item['target_id']))))
          && $uri = entity_uri($target_type, $target_entity)
        ) {
          return $uri['path'];
        }
      }
    }
  }
}