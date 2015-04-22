<?php
use Drupal\crumbs\Plugin\TextFieldTitlePlugin;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function text_crumbs_plugins($api) {
  foreach (field_info_fields() as $field_name => $field_info) {
    if (0
      || 'text' !== $field_info['type']
      || '1' !== '' . $field_info['cardinality']
    ) {
      continue;
    }

    $instances = array();
    foreach ($field_info['bundles'] as $entity_type => $bundles) {
      foreach ($bundles as $bundle_name) {
        $instance_info = field_info_instance($entity_type, $field_name, $bundle_name);
        if (empty($instance_info['settings']['text_processing'])) {
          $instances[$entity_type][] = $bundle_name;
        }
      }
    }

    if (!empty($instances)) {
      $plugin = new crumbs_EntityPlugin_Field_Text($field_name, $instances);
      $api->entityTitlePlugin('field.' . $field_name, $plugin, array_keys($instances));
      $api->describeFindTitle('field.' . $field_name . '.*', t('Field') . ': ' . check_plain($field_name));
    }
  }

  # $api->fieldTypeTitlePlugin('field.*', 'text', new TextFieldTitlePlugin());

  $api->disabledByDefault('field.*');
  $api->describeFindTitle('field.*', t('Text fields'));
}
