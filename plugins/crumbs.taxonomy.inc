<?php
use Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface;
use Drupal\crumbs\Util;

/**
 * Implements hook_crumbs_plugins()
 *
 * @param \Drupal\crumbs\PluginApi\HookArgument\ArgumentInterface $api
 */
function taxonomy_crumbs_plugins(ArgumentInterface $api) {

  $plugin = new taxonomy_CrumbsMultiPlugin_termParent;
  $api->route('taxonomy/term/%')->multiPlugin('termParent', $plugin);

  $family = $api->modulePluginFamily()
    ->describeFindParent(t('Term reference fields'))
    ->disabledByDefault();

  foreach (field_info_fields() as $field_name => $field_info) {
    if ($field_info['type'] === 'taxonomy_term_reference') {
      $plugin = new crumbs_EntityPlugin_Field_TermReference($field_name, $field_info['bundles']);
      $family->entityParentPlugin($field_name, $plugin, array_keys($field_info['bundles']))
        ->describe($field_name);
    }
  }
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
    if (FALSE === $term = Util::itemExtractEntity($item, 'taxonomy_term', 2)) {
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
