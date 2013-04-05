<?php


/**
 * Implements hook_crumbs_plugins()
 */
function taxonomy_crumbs_plugins($api) {

  $api->multiPlugin('termParent');

  foreach (field_info_fields() as $key => $info) {
    if ($info['type'] == 'taxonomy_term_reference') {
      $plugin = new taxonomy_CrumbsEntityParentPlugin_termReference($key, $info['bundles']);
      $api->entityParentPlugin("termReference.$key", $plugin, array_keys($info['bundles']));
    }
  }

  $api->disabledByDefault('termReference.*');
}


// -----------------------------------------------------------------------------


class taxonomy_CrumbsEntityParentPlugin_termReference implements crumbs_EntityParentPlugin {

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
          $api->descWithLabel($instance['label'], t('Field'));
        }
      }
      $api->addDescription('(' . $this->fieldKey . ')');
    }
  }

  function entityFindParent($entity, $entity_type, $distinction_key) {

    $terms = array();
    $items = field_get_items($entity_type, $entity, $this->fieldKey);
    if ($items) {
      foreach ($items as $item) {
        $terms[$item['tid']] = TRUE;
      }
    }

    if (count($terms) > 1) {
      $walk = $terms;
      $visited = array();
      while (!empty($walk)) {
        $visited += $walk;
        foreach ($walk as $tid => $true) {
          $parents = taxonomy_get_parents($tid);
          unset($walk[$tid]);
          foreach ($parents as $tid => $parent) {
            unset($terms[$tid]);
            if (!isset($visited[$tid])) {
              $walk[$tid] = $parent;
            }
          }
        }
      }
    }

    // Return the path of the first found term, if any.
    foreach ($terms as $tid => $term_info) {
      $term = taxonomy_term_load($tid);
      if (!empty($term)) {
        $uri = entity_uri('taxonomy_term', $term);
        if (!empty($uri)) {
          return $uri['path'];
        }
      }
    }
  }
}


// -----------------------------------------------------------------------------


class taxonomy_CrumbsMultiPlugin_termParent implements crumbs_MultiPlugin {

  function describe($api) {
    foreach (taxonomy_get_vocabularies() as $voc_id => $voc) {
      $api->ruleWithLabel($voc->machine_name, $voc->name, t('Vocabulary'));
    }
    // Now set a generic title for the entire plugin.
    $api->descWithLabel(t('The parent term'), t('Parent'));
  }

  /**
   * Terms get their parent terms as breadcrumb parent.
   * The method name matches the router path "taxonomy/term/%".
   */
  function findParent__taxonomy_term_x($path, $item) {
    $term = $item['map'][2];
    // Load the term if it hasn't been loaded due to a missing wildcard loader.
    $term = is_numeric($term) ? taxonomy_term_load($term) : $term;

    $parents = taxonomy_get_parents($term->tid);
    $result = array();
    foreach ($parents as $parent_tid => $parent_term) {
      if ($parent_term->vocabulary_machine_name == $term->vocabulary_machine_name) {
        $uri = entity_uri('taxonomy_term', $parent_term);
        if (!empty($uri)) {
          return array($term->vocabulary_machine_name => $uri['path']);
        }
      }
    }
  }
}
