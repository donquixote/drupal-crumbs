<?php


/**
 * Implements hook_crumbs_plugins().
 *
 * This is the version for the og-7.x-2.x branch.
 */
function og_crumbs_plugins($api) {
  $api->multiPlugin('group_post');

  // There are no default paths for group overview pages in og-7.x-2.x anymore.
  // We leave it to custom module developers to instantiate or override those
  // classes.
}


/**
 * Use the group node as a parent for group posts.
 * The priorities can be configured per group content type.
 */
class og_CrumbsMultiPlugin_group_post implements crumbs_MultiPlugin {

  function describe($api) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_content_type('node', $type->type)) {
        $api->addRule($type->type);
      }
    }
  }

  function findParent__node_x($path, $item) {
    $node = $item['map'][1];
    // Load the node if it hasn't been loaded due to a missing wildcard loader.
    $node = is_numeric($node) ? node_load($node) : $node;

    // field_get_items() performs a lot faster than og_get_entity_groups().
    // TODO:
    //   We cannot rely on the field name to always be og_group_ref.
    //   Instead, we could provide a separate plugin for each such field.
    //   This way, fields in disabled plugins get never triggered.
    $items = field_get_items('node', $node, 'og_group_ref');
    if (!empty($items)) {
      foreach ($items as $item) {
        $parent_path = $this->getParentPath($item['target_id'], $node);
        return array($node->type => $parent_path);
      }
    }
  }

  /**
   * This method can be overridden by custom plugins that inherit from this one,
   * e.g. to set a different parent for group events than for group discussions.
   */
  protected function getParentPath($group_nid, $group_post) {
    return 'node/' . $group_nid;
    /*
     * Example:
     * switch ($group_post->type) {
     *   case 'event':
     *     return 'node/' . $group_nid . '/events';
     *   case 'discussion':
     *     return 'node/' . $group_nid . '/forum';
     *   default:
     *     return 'node/' . $group_nid;
     * }
     */
  }
}


/**
 * Make $groups_overview_path the parent path for group nodes.
 * The priorities can be configured per group node type.
 */
class og_CrumbsMultiPlugin_groups_overview implements crumbs_MultiPlugin {

  protected $groupsOverviewPath;

  /**
   * @param string $groups_overview_path
   *   The path to be used as parent for all group nodes.
   */
  function __construct($groups_overview_path) {
    $this->groupsOverviewPath = $groups_overview_path;
  }

  function describe($api) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_type('node', $type->type)) {
        $api->addRule($type->type);
      }
    }
  }

  function findParent__node_x($path, $item) {
    $node = $item['map'][1];
    // Load the node if it hasn't been loaded due to a missing wildcard loader.
    $node = is_numeric($node) ? node_load($node) : $node;
return;
    $items = field_get_items('node', $node, 'group_group');
    if (!empty($items)) {
      return array($node->type => $this->groupsOverviewPath);
    }
  }
}


/**
 * Make $my_groups_path the parent path for group nodes where the current user
 * is a member.
 * The priorities can be configured per group node type.
 */
class og_CrumbsMultiPlugin_my_groups_overview implements crumbs_MultiPlugin {

  protected $myGroupsPath;

  /**
   * @param string $my_groups_path
   *   The path to be used as parent for all group nodes where the current user
   *   is a member.
   */
  function __construct($my_groups_path) {
    $this->myGroupsPath = $my_groups_path;
  }

  function describe($api) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_type('node', $type->type)) {
        $api->addRule($type->type);
      }
    }
  }

  function findParent__node_x($path, $item) {
    $node = $item['map'][1];
    // Load the node if it hasn't been loaded due to a missing wildcard loader.
    $node = is_numeric($node) ? node_load($node) : $node;

    $items = field_get_items('node', $node, 'group_group');
    if (!empty($items)) {
      if (og_is_member('node', $node->nid)) {
        return array($node->type => $this->myGroupsPath);
      }
    }
  }
}
