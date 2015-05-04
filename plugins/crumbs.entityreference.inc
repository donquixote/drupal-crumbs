<?php
use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface $api
 */
function entityreference_crumbs_plugins(ArgumentInterface $api) {
  $family = $api->modulePluginFamily()
    ->describeFindParent(t('Entity reference fields'))
    ->disabledByDefault();
  foreach (field_info_fields() as $field_name => $field_info) {
    if (1
      && 'entityreference' === $field_info['type']
      && '1' === '' . $field_info['cardinality']
    ) {
      $plugin = new crumbs_EntityPlugin_Field_EntityReference($field_name, $field_info['bundles']);
      $family->entityParentPlugin($field_name, $plugin, array_keys($field_info['bundles']));
    }
  }
}
