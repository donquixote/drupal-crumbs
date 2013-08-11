<?php

/**
 * Implements hook_crumbs_plugins().
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function entityreference_crumbs_plugins($api) {
  foreach (field_info_fields() as $key => $info) {
    if ($info['type'] === 'entityreference') {
      $plugin = new crumbs_EntityParentPlugin_Field_EntityReference($key, $info['bundles']);
      $api->entityParentPlugin($key, $plugin, array_keys($info['bundles']));
    }
  }
  $api->disabledByDefault('*');
}
