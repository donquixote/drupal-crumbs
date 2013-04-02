<?php

class crumbs_CrumbsMultiPlugin_NodeParent extends crumbs_CrumbsMultiPlugin_EntityParentAbstract {

  function describe($api) {
    $info = entity_get_info('node');
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
  function findParent__node_x($path, $item) {
    $node = $item['map'][1];
    // Load the node if it hasn't been loaded due to a missing wildcard loader.
    $node = is_numeric($node) ? node_load($node) : $node;
    if (empty($node) || !is_object($node)) {
      return;
    }

    $parent = $this->plugin->entityFindParent($node, 'node', $node->type);
    if (!empty($parent)) {
      return array($node->type => $parent);
    }
  }
}
