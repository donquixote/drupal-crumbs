<?php

class crumbs_CrumbsMultiPlugin_EntityParent extends crumbs_CrumbsMultiPlugin_EntityParentAbstract {

  protected $entityType;
  protected $route;
  protected $bundleKey;

  /**
   * @param string $entity_type
   *   The entity type.
   * @param string $route
   *   The route, e.g. node/%.
   * @param string $bundle_key
   *   The key on the $entiy object to determine the bundle.
   * @param object $plugin
   *   The object that can actually determine a parent path for the entity.
   */
  function __construct($entity_type, $route, $bundle_key, $plugin) {
    $this->entityType = $entity_type;
    $this->route = $route;
    $this->bundleKey = $bundle_key;
    parent::__construct($plugin);
  }

  function describe($api) {
    $info = entity_get_info($this->entityType);
    foreach ($info['bundles'] as $bundle_key => $bundle) {
      $api->addRule($bundle_key);
    }
  }

  /**
   * Find candidates for the parent path.
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
    if ($item['route'] !== $this->route) {
      return;
    }
    $entity = end($item['map']);
    // Load the entity if it hasn't been loaded due to a missing wildcard loader.
    $entity = is_numeric($entity) ? entity_load($this->entity_type, $entity) : $entity;
    if (empty($entity) || !is_object($entity)) {
      return;
    }

    if (!empty($this->bundleKey)) {
      $distinction_key = $entity->{$this->bundleKey};
    }
    else {
      $distinction_key = $this->entityType;
    }

    $parent = $this->plugin->entityFindParent($entity, $this->entityType, $distinction_key);
    if (!empty($parent)) {
      return array($distinction_key => $parent);
    }
  }
}
