<?php


/**
 * Implements hook_crumbs_plugins().
 */
function entityreference_crumbs_plugins($api) {
  foreach (field_info_fields() as $key => $info) {
    if ($info['type'] === 'entityreference') {
      $plugin = new entityreference_CrumbsEntityParentPlugin($key, $info['bundles']);
      $api->entityParentPlugin($key, $plugin, array_keys($info['bundles']));
    }
  }
  $api->disabledByDefault('*');
}


class entityreference_CrumbsEntityParentPlugin implements crumbs_EntityParentPlugin {

  protected $fieldKey;
  protected $bundlesByType;

  function __construct($field_key, $bundles_by_type) {
    $this->fieldKey = $field_key;
    $this->bundlesByType = $bundles_by_type;
  }

  function describe($api, $entity_type, $keys) {
    if (!empty($this->bundlesByType[$entity_type])) {
      if ('user' === $entity_type) {
        $instance = field_info_instance('user', $this->fieldKey, 'user');
        foreach ($keys as $key => $title) {
          $api->addRule($key, $title);
        }
        $api->descWithLabel($instance['label'], t('Field'));
      }
      foreach ($this->bundlesByType[$entity_type] as $bundle_name) {
        if (isset($keys[$bundle_name])) {
          $instance = field_info_instance($entity_type, $this->fieldKey, $bundle_name);
          $api->addRule($bundle_name, $keys[$bundle_name]);
          $api->descWithLabel($instance['label'], t('Field'), $bundle_name);
        }
      }
      // $api->addDescription('(' . $this->fieldKey . ')');
    }
  }

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
