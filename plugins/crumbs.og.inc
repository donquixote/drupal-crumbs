<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;
use Drupal\crumbs\Util;

/**
 * Implements hook_crumbs_plugins().
 *
 * This is the version for the og-7.x-1.x branch.
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function og_crumbs_plugins(ArgumentInterface $api) {

  // Organic groups is a special case,
  // because 7.x-2.x behaves different from 7.x-1.x.
  if (!function_exists('og_get_group')) {
    // The site uses the og-7.x-1.x branch.

    $plugin = new og_v1_CrumbsMultiPlugin_group_post;
    $api->routeMultiPlugin('node/%', 'og1.group_post', $plugin)
      ->describe(t('Group node') . ' &raquo; ' . t('Group post'));

    $api->routeTranslateTitle('group-list', 'og1.groups_overview_title', 'Groups');

    $plugin = new og_v1_CrumbsMultiPlugin_groups_overview('group-list');
    $api->routeMultiPlugin('node/%', 'og1.groups_overview', $plugin)
      ->describe('group-list &raquo; node/%');

    $plugin = new og_v1_CrumbsMultiPlugin_my_groups_overview('user-groups');
    $api->routeMultiPlugin('node/%', 'og1.my_groups_overview', $plugin)
      ->describe('user-groups &raquo; node/%, for user\'s own groups');

    $api->describeFindParent('og1.*', 'Organic groups 7.x-1.x');
    $api->describeFindTitle('og1.*', 'Organic groups 7.x-1.x');
  }
  else {
    // The site uses the og-7.x-2.x branch.
    $plugin = new og_v2_CrumbsMultiPlugin_group_post;
    $api->routeMultiPlugin('node/%', 'og2.group_post', $plugin)
      ->describe(t('Group node') . ' &raquo; ' . t('Group post'));

    $api->describeFindParent('og2.*', 'Organic groups 7.x-2.x');

    // There are no default paths for group overview pages in og-7.x-2.x anymore.
    // We leave it to custom module developers to instantiate or override those
    // classes.
  }

}

/**
 * Use the group node as a parent for group posts.
 * The priorities can be configured per group content type.
 */
class og_v1_CrumbsMultiPlugin_group_post implements crumbs_MultiPlugin_FindParentInterface {

  /**
   * {@inheritdoc}
   */
  function describe($api) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_content_type('node', $type->type)) {
        $api->addRule($type->type);
      }
    }
  }

  function findParent($path, $item) {
    if (FALSE === $node = Util::itemExtractEntity($item, 'node', 1)) {
      return NULL;
    }

    $items = field_get_items('node', $node, 'group_audience');
    if (is_array($items)) {
      foreach ($items as $item) {
        $row = db_query("SELECT * FROM {og} WHERE gid = :gid", array(':gid' => $item['gid']))->fetchObject();
        if ($row && $row->entity_type === 'node') {
          $parent_path = $this->getParentPath($row->etid, $node);
          return array($node->type => $parent_path);
        }
      }
    }

    return NULL;
  }

  /**
   * This method can be overridden by custom plugins that inherit from this one,
   * e.g. to set a different parent for group events than for group discussions.
   *
   * @param int $group_nid
   * @param object $group_post
   *
   * @return string
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
 * Use the group node as a parent for group posts.
 * The priorities can be configured per group content type.
 */
class og_v2_CrumbsMultiPlugin_group_post implements crumbs_MultiPlugin_FindParentInterface {

  /**
   * {@inheritdoc}
   */
  function describe($api) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_content_type('node', $type->type)) {
        $api->ruleWithLabel($type->type, $type->name, t('Group content type'));
      }
    }
  }

  /**
   * Attempts to find a breadcrumb parent path for node/%.
   * If that node is in a group, it will return the group page as a parent.
   *
   * @param string $path
   *   The path that we want to find a parent for, e.g. "node/123".
   * @param array $item
   *   Loaded router item, as returned from crumbs_get_router_item()
   *
   * @return array|null
   *   Parent path candidates
   */
  function findParent($path, $item) {
    if (FALSE === $node = Util::itemExtractEntity($item, 'node', 1)) {
      return NULL;
    }

    // field_get_items() performs a lot faster than og_get_entity_groups().
    // See http://drupal.org/node/1819300#comment-6633494
    // TODO:
    //   We cannot rely on the field name to always be og_group_ref.
    //   Instead, we could provide a separate plugin for each such field.
    //   This way, fields in disabled plugins get never triggered.
    $items = field_get_items('node', $node, 'og_group_ref');
    if (is_array($items)) {
      foreach ($items as $item) {
        $parent_path = $this->getParentPath($item['target_id'], $node);
        return array($node->type => $parent_path);
      }
    }

    return NULL;
  }

  /**
   * This method can be overridden by custom plugins that inherit from this one,
   * e.g. to set a different parent for group events than for group discussions.
   *
   * @param int $group_nid
   *   Node id of the group that was found to be the parent.
   * @param stdClass $group_post
   *   The node that is in the group, and that we are trying to find a parent
   *   path for.
   *
   * @return string
   *   A parent path. The native implementation returns just node/$nid.
   *   Custom module might subclass this class and override this method, to let
   *   it return e.g. node/$nid/events, or node/$nid/forum, depending on the
   *   $group_post argument.
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
class og_v1_CrumbsMultiPlugin_groups_overview implements crumbs_MultiPlugin_FindParentInterface {

  protected $groupsOverviewPath;

  /**
   * @param string $groups_overview_path
   *   The path to be used as parent for all group nodes.
   */
  function __construct($groups_overview_path) {
    $this->groupsOverviewPath = $groups_overview_path;
  }

  /**
   * {@inheritdoc}
   */
  function describe($api) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_type('node', $type->type)) {
        $api->addRule($type->type);
      }
    }
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return array|null
   */
  function findParent($path, $item) {
    if (FALSE === $node = Util::itemExtractEntity($item, 'node', 1)) {
      return NULL;
    }

    $items = field_get_items('node', $node, 'group_group');
    if ($items) {
      return array($node->type => 'group-list');
    }

    return NULL;
  }

}

/**
 * Make $my_groups_path the parent path for group nodes where the current user
 * is a member.
 * The priorities can be configured per group node type.
 */
class og_v1_CrumbsMultiPlugin_my_groups_overview implements crumbs_MultiPlugin_FindParentInterface {

  /**
   * @var string
   */
  protected $myGroupsPath;

  /**
   * @param string $my_groups_path
   *   The path to be used as parent for all group nodes where the current user
   *   is a member.
   */
  function __construct($my_groups_path) {
    $this->myGroupsPath = $my_groups_path;
  }

  /**
   * {@inheritdoc}
   */
  function describe($api) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_type('node', $type->type)) {
        $api->addRule($type->type);
      }
    }
  }

  /**
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Loaded router item, as returned from crumbs_get_router_item()
   *
   * @return array
   *   Parent path candidates
   */
  function findParent($path, $item) {
    if (FALSE === $node = Util::itemExtractEntity($item, 'node', 1)) {
      return NULL;
    }

    if (og_is_group_type('node', $node->type)) {
      /** @noinspection PhpUndefinedFunctionInspection */
      $group = og_get_group('node', $node->nid);
      if (!empty($group)) {
        /** @noinspection PhpParamsInspection */
        if (og_is_member($group->gid)) {
          return array($node->type => 'user-groups');
        }
      }
    }

    return NULL;
  }

}
