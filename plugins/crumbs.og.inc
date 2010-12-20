<?php


function og_crumbs_plugins() {
  return array(
    'group_post' => new _og_CrumbsPlugin__group_post(),
    'groups_overview' => new _og_CrumbsPlugin__groups_overview(),
    'groups_overview' => new _og_CrumbsPlugin__my_groups_overview(),
  );
}


class _og_CrumbsPlugin__group_post {

  function define($h) {
    $types = og_get_types('group_post');
    foreach ($types as $type) {
      $h->addRule($type);
    }
  }

  function findParent__node__($path, $item) {
    $node = $item['map'][1];
    if (is_array($node->og_groups)) {
      foreach ($node->og_groups as $group_nid) {
        return array($node->type => 'node/'. $group_nid);
      }
    }
  }
}


class _og_CrumbsPlugin__groups_overview {

  function define($h) {
    $types = og_get_types('group');
    foreach ($types as $type) {
      $h->addRule($type);
    }
  }

  function findParent__node__($path, $item) {
    $node = $item['map'][1];
    if (isset($node->og_selective)) {
      return array($node->type => 'og');
    }
  }
}


class _og_CrumbsPlugin__my_groups_overview {

  function define($h) {
    $types = og_get_types('group');
    foreach ($types as $type) {
      $h->addRule($type);
    }
  }

  function findParent__node__($path, $item) {
    $node = $item['map'][1];
    if (isset($node->og_selective)) {
      if (isset($GLOBALS['user']->og_groups[$node->nid])) {
        return array($node->type => 'og/my');
      }
    }
  }
}


