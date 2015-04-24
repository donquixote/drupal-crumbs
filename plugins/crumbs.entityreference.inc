<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function entityreference_crumbs_plugins(ArgumentInterface $api) {
  foreach (field_info_fields() as $field_name => $field_info) {
    if (1
      && 'entityreference' === $field_info['type']
      && '1' === '' . $field_info['cardinality']
    ) {
      $plugin = new crumbs_EntityPlugin_Field_EntityReference($field_name, $field_info['bundles']);
      $api->entityParentPlugin('field.' . $field_name, $plugin, array_keys($field_info['bundles']));
    }
  }
  $api->describeFindParent('field.*', t('Entity reference fields'));
  $api->disabledByDefault('field.*');
}
