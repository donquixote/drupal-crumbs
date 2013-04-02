<?php


/**
 * Implements hook_crumbs_plugins().
 */
function entityreference_crumbs_plugins($api) {
  foreach (field_info_fields() as $key => $info) {
    if ($info['type'] === 'entityreference') {
      $plugin = new entityreference_CrumbsEntityParentPlugin($key, $info['bundles'], array_keys($info['bundles']));
      $api->entityParentPlugin($key, $plugin);
    }
  }
  $api->disabledByDefault('*');
}


class entityreference_CrumbsEntityParentPlugin {

  protected $fieldKey;
  protected $bundlesByType;

  function __construct($field_key, $bundles_by_type) {
    $this->fieldKey = $field_key;
    $this->bundlesByType = $bundles_by_type;
  }

  function describe($api, $entity_type, $entity_info) {
    if (!empty($this->bundlesByType[$entity_type])) {
      foreach ($this->bundlesByType[$entity_type] as $bundle_name) {
        $api->addRule($bundle_name, $bundle_name);
      }
    }
  }

  function entityFindParent($entity, $entity_type, $distinction_key) {
    $items = field_get_items($entity_tpy, $entity, $this->fieldKey);
    if ($items) {
      $field = field_info_field($this->fieldKey);
      $item = $items[0];
      $target_id = $item['target_id'];
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
