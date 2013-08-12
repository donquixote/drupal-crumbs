<?php


/**
 * Implements hook_crumbs_plugins().
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function entityreference_prepopulate_crumbs_plugins($api) {
  $api->multiPlugin('node');
}

class entityreference_prepopulate_CrumbsMultiPlugin_node implements crumbs_MultiPlugin_FindParentInterface {

  /**
   * @inheritdoc
   */
  function describe($api) {
    foreach (field_info_fields() as $field_name => $field_info) {
      if (0
        || $field_info['type'] !== 'entityreference'
        || empty($field_info['bundles']['node'])
      ) {
        continue;
      }

      foreach ($field_info['bundles']['node'] as $type) {
        $instance = field_info_instance('node', $field_name, $type);
        if (0
          || empty($instance['default_value_function'])
          || 'entityreference_prepopulate_field_default_value' !== $instance['default_value_function']
        ) {
          continue;
        }

        $api->addRule("$field_name.$type");
      }
    }
  }

  /**
   * @inheritdoc
   */
  function findParent($path, $item) {
    if (0
      // Start with a cheap-to-compute condition,
      // so the regex can be skipped in the average case.
      || 'node/add/' !== substr($path, 0, 9)
      || !preg_match('#^node/add/([^/]+)$#', $path, $m)
      || $path !== $_GET['q']
      || 1 === count($_GET)
    ) {
      return;
    }

    $type = str_replace('-', '_', $m[1]);
    $info = field_info_fields();

    $candidates = array();
    foreach ($_GET as $k => $v) {
      if (0
        || 'q' === $k
        || !is_numeric($v) || empty($v) || !($v > 0)
        || !isset($info[$k]['settings']['target_type'])
      ) {
        continue;
      }

      $target_type = $info[$k]['settings']['target_type'];
      $instance = field_info_instance('node', $k, $type);
      if (0
        || empty($instance['default_value_function'])
        || 'entityreference_prepopulate_field_default_value' !== $instance['default_value_function']
      ) {
        continue;
      }

      $target_entities = entity_load($target_type, array($v));
      if (empty($target_entities[$v])) {
        continue;
      }

      $uri = entity_uri($target_type, $target_entities[$v]);
      if (empty($uri['path'])) {
        continue;
      }

      $candidates["$k.$type"] = $uri['path'];
    }
    return $candidates;
  }
}
