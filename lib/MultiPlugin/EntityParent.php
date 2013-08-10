<?php

class crumbs_MultiPlugin_EntityParent extends crumbs_MultiPlugin_EntityParentAbstract {

  protected $entityType;
  protected $bundleKey;
  protected $bundleName;

  /**
   * @param crumbs_EntityParentPlugin $plugin
   *   The object that can actually determine a parent path for the entity.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle_key
   *   The key on the $entiy object to determine the bundle.
   * @param string $bundle_name
   *   The label for the bundle, e.g. "Node type" or "Vocabulary".
   *   This is an untranslated string.
   */
  function __construct($plugin, $entity_type, $bundle_key, $bundle_name) {
    $this->entityType = $entity_type;
    $this->bundleKey = $bundle_key;
    $this->bundleName = $bundle_name;
    parent::__construct($plugin);
  }

  /**
   * @inheritdoc
   */
  function describe($api) {
    return $this->describeGeneric($api, $this->entityType, t($this->bundleName));
  }

  /**
   * @inheritdoc
   */
  function findParent($path, $item) {

    $entity = end($item['map']);
    // Load the entity if it hasn't been loaded due to a missing wildcard loader.
    $entity = is_numeric($entity) ? entity_load($this->entityType, array($entity)) : $entity;
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
