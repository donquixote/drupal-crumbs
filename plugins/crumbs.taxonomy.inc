<?php


/**
 * Implements hook_crumbs_plugins()
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function taxonomy_crumbs_plugins($api) {

  $api->routeMultiPlugin('taxonomy/term/%', 'termParent');

  $api->describeFindParent('termReference.*', t('Term reference fields'));

  foreach (field_info_fields() as $field_name => $field_info) {
    if ($field_info['type'] === 'taxonomy_term_reference') {
      $plugin = new crumbs_EntityPlugin_Field_TermReference($field_name, $field_info['bundles']);
      $api->describeFindParent('termReference.' . $field_name . '.*', $field_name);
      $api->entityParentPlugin('termReference.' . $field_name, $plugin, array_keys($field_info['bundles']));
    }
  }
  $api->disabledByDefault('termReference.*');
}


class taxonomy_CrumbsMultiPlugin_termParent implements crumbs_MultiPlugin_FindParentInterface {

  /**
   * {@inheritdoc}
   */
  function describe($api) {
    // Now set a generic title for the entire plugin.
    $api->addDescription(t('Taxonomy term hierarchy'));
    foreach (taxonomy_get_vocabularies() as $voc) {
      $api->ruleWithLabel($voc->machine_name, $voc->name, t('Vocabulary'));
    }
  }

  /**
   * Terms get their parent terms as breadcrumb parent.
   *
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return array
   *   Parent path candidates
   */
  function findParent($path, $item) {
    if (FALSE === $term = crumbs_Util::itemExtractEntity($item, 'taxonomy_term', 2)) {
      return NULL;
    }

    $parents = taxonomy_get_parents($term->tid);
    foreach ($parents as $parent_tid => $parent_term) {
      if ($parent_term->vocabulary_machine_name == $term->vocabulary_machine_name) {
        $uri = entity_uri('taxonomy_term', $parent_term);
        if (!empty($uri)) {
          return array($term->vocabulary_machine_name => $uri['path']);
        }
      }
    }

    return NULL;
  }
}
