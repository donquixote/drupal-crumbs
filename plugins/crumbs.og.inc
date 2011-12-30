<?php


function og_crumbs_plugins() {
  return array(
    'group_post' => new _og_CrumbsPlugin__group_post(),
    'groups_overview' => new _og_CrumbsPlugin__groups_overview(),
    'my_groups_overview' => new _og_CrumbsPlugin__my_groups_overview(),
    'groups_overview_title' => new _og_CrumbsPlugin__groups_overview_title(),
  );
}


class _og_CrumbsPlugin__group_post {

  function define($h) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_content_type('node', $type->type)) {
        $h->addRule($type->type);
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


class _og_CrumbsPlugin__groups_overview {

  function define($h) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_type('node', $type->type)) {
        $h->addRule($type->type);
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


class _og_CrumbsPlugin__groups_overview_title {

  function defineOne($h) {
    return $h->setTitle(t('Set "Group" as the title for item for "group-list".'));
  }

  function findTitle($path, $item) {
    if ($item['route'] === 'group-list') {
      return t('Groups');
    }
  }
}


class _og_CrumbsPlugin__my_groups_overview {

  function define($h) {
    $types = node_type_get_types();
    foreach ($types as $type) {
      if (og_is_group_type('node', $type->type)) {
        $h->addRule($type->type);
      }
    }
  }

  function findParent__node_x($path, $item) {
    $node = $item['map'][1];
    if (isset($node->og_selective)) {
      if (og_is_group_member($node->nid, FALSE)) {
        return array($node->type => 'og/my');
      }
    }
  }
}


