<?php


/**
 * Implements hook_crumbs_plugins().
 */
function crumbs_crumbs_plugins($api) {
  $api->monoPlugin('home_title');
  foreach (array(
    'node' => 'node/%',
    'taxonomy_term' => 'taxonomy/term/%',
    'user' => 'user/%',
  ) as $entity_type => $route) {
    $patterns = variable_get('crumbs_' . $entity_type . '_parent_patterns', array());
    $plugin = new crumbs_CrumbsMultiPlugin_entityParent($entity_type, $route, $patterns);
    $api->multiPlugin("entityParent.$entity_type", $plugin);
  }
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


class crumbs_CrumbsMultiPlugin_entityParent implements crumbs_MultiPlugin {

  protected $entityType;
  protected $route;
  protected $patterns;

  function __construct($entity_type, $route, $patterns) {
    $this->entityType = $entity_type;
    $this->info = entity_get_info($entity_type);
    $this->route = $route;
    $this->patterns = $patterns;
  }

  function describe($api) {
    foreach ($this->info['bundles'] as $bundle_key => $bundle) {
      if (!empty($this->patterns[$bundle_key])) {
        $api->addRule($bundle_key);
      }
    }
  }

  function findParent($path, $item) {
    if ($item['route'] !== $this->route) {
      return;
    }
    $entity = end($item['map']);
    // Load the entity if it hasn't been loaded due to a missing wildcard loader.
    $entity = is_numeric($entity) ? entity_load($this->entity_type, $entity) : $entity;
    if (empty($entity) || !is_object($entity)) {
      return;
    }

    $bundle_key = $entity->{$this->info['entity keys']['bundle']};
    $pattern = @$this->patterns[$bundle_key];
    if (!empty($pattern)) {
      if (module_exists('token')) {
        $parent = token_replace($pattern, array(
          $this->info['token type'] => $entity
        ), array(
          'language' => $GLOBALS['language'],
          'callback' => 'crumbs_clean_token_values',
        ));
      }
      else {
        $parent = $pattern;
      }
      if (!empty($parent)) {
        // Only accept candidates where all tokens are fully resolved.
        // This means we can't have literal '[' in the path - so be it.
        if (FALSE === strpos($parent, '[')) {
          return array($bundle_key => $parent);
        }
      }
    }
  }
}
