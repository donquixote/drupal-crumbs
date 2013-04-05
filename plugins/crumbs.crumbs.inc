<?php


/**
 * Implements hook_crumbs_plugins().
 */
function crumbs_crumbs_plugins($api) {
  $api->entityRoute('node', 'node/%', 'NodeParent', 'type');
  $api->entityRoute('user', 'user/%', 'UserParent', NULL);
  if (module_exists('taxonomy')) {
    $api->entityRoute('taxonomy_term', 'taxonomy/term/%', 'TaxonomyTermParent', 'vocabulary_machine_name');
  }

  $api->monoPlugin('home_title');
  $suffix = module_exists('token') ? 'TokenEnabled' : NULL;
  $api->entityParentPlugin('entityParent', $suffix);
}


class crumbs_CrumbsMonoPlugin_home_title implements crumbs_MonoPlugin {

  protected $frontNormalPath;

  function __construct() {
    $this->frontNormalPath = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
  }

  function describe($api) {
    return t('Set "Home" as the title for the root item.');
  }

  function findTitle($path, array $item) {
    if ($path === $this->frontNormalPath) {
      return t('Home');
    }
  }
}


class crumbs_CrumbsEntityParentPlugin implements crumbs_EntityParentPlugin {

  function describe($api, $entity_type, $keys) {
    $patterns = variable_get('crumbs_' . $entity_type . '_parent_patterns', array());
    foreach ($keys as $key => $title) {
      if (empty($patterns[$key])) {
        unset($keys[$key]);
      }
      else {
        $api->addRule($key, $title);
        $api->descWithLabel('"' . check_plain($patterns[$key]) . '"', t('Parent'), $key);
      }
    }
  }

  function entityFindParent($entity, $entity_type, $distinction_key) {

    // This is cached..
    $patterns = variable_get('crumbs_' . $entity_type . '_parent_patterns', array());

    if (!empty($patterns[$distinction_key])) {
      $parent = $patterns[$distinction_key];
      // Only accept candidates where all tokens are fully resolved.
      // This means we can't have literal '[' in the path - so be it.
      if (FALSE === strpos($parent, '[')) {
        return $parent;
      }
    }
  }
}


class crumbs_CrumbsEntityParentPlugin_TokenEnabled extends crumbs_CrumbsEntityParentPlugin {

  function entityFindParent($entity, $entity_type, $distinction_key) {

    // This is cached..
    $patterns = variable_get('crumbs_' . $entity_type . '_parent_patterns', array());

    if (!empty($patterns[$distinction_key])) {

      // Use token to resolve the pattern.
      $info = entity_get_info($entity_type);
      $parent = token_replace($patterns[$distinction_key],
        array(
          $info['token type'] => $entity,
        ),
        array(
          'language' => $GLOBALS['language'],
          'callback' => 'crumbs_clean_token_values',
        )
      );

      if (!empty($parent)) {
        // Only accept candidates where all tokens are fully resolved.
        // This means we can't have literal '[' in the path - so be it.
        if (FALSE === strpos($parent, '[')) {
          return $parent;
        }
      }
    }
  }
}
