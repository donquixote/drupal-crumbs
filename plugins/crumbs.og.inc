<?php


function og_crumbs_plugins($api) {
  $api->multiPlugin('group_post');
  $api->multiPlugin('groups_overview');
  $api->monoPlugin('groups_overview_title');
  $api->multiPlugin('my_groups_overview');
}


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
    if (isset($node->group_audience['und'])) {
      foreach ($node->group_audience['und'] as $group_info) {
        // Just pick the first one..
        $gid = $group_info['gid'];
        $row = db_query("SELECT * FROM {og} WHERE gid = :gid", array(':gid' => $gid))->fetchObject();
        if ($row && $row->entity_type === 'node') {
          $etid = $row->etid;
          return array($node->type => "node/$etid");
        }
      }
    }
  }
}


class og_CrumbsMultiPlugin_groups_overview implements crumbs_MultiPlugin {

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
    if (isset($node->group_group['und'][0]['value'])) {
      return array($node->type => 'group-list');
    }
  }
}


class og_CrumbsMonoPlugin_groups_overview_title implements crumbs_MonoPlugin {

  function describe($api) {
    return t('Set "Group" as the title for item for "group-list".');
  }

  function findTitle($path, $item) {
    if ($item['route'] === 'group-list') {
      return t('Groups');
    }
  }
}


class og_CrumbsMultiPlugin_my_groups_overview implements crumbs_MultiPlugin {

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
    if (og_is_group_type('node', $node->type)) {
      $group = og_get_group('node', $node->nid);
      if (!empty($group)) {
        if (og_is_member($group->gid)) {
          return array($node->type => 'user-groups');
        }
      }
    }
  }
}


