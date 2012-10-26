<?php


/**
 * Implements hook_crumbs_plugins().
 */
function forum_crumbs_plugins($api) {

  $api->monoPlugin('forumTerm');
  $api->monoPlugin('forumTermName');
  $api->monoPlugin('forumThread');
  $api->monoPlugin('forumThreadCreate');
}


// -----------------------------------------------------------------------------


class forum_CrumbsMonoPlugin_forumTerm implements crumbs_MonoPlugin {

  function describe($api) {
    return t('Let /forum/123 be the parent path of /forum/456, if 123 is the parent of 456.');
  }

  /**
   * Forums get their parent forums as breadcrumb parent.
   * The method name matches the router path "forum/%".
   * Forums are actually taxonomy terms, just the path is different.
   */
  function findParent__forum_x($path, $item) {
    $term = $item['map'][1];
    // Load the forum term,
    // if it hasn't been loaded due to a missing wildcard loader.
    // We could use forum_forum_load() instead, but this looks too scary
    // performance-wise, and we don't actually need it.
    // So we just use taxonomy_term_load() instead.
    $term = is_numeric($term) ? taxonomy_term_load($term) : $term;

    $parents = taxonomy_get_parents($term->tid);
    $result = array();
    foreach ($parents as $parent_tid => $parent_term) {
      if ($parent_term->vocabulary_machine_name == $term->vocabulary_machine_name) {
        // The parent forum
        return 'forum/' . $parent_tid;
      }
    }
    // Forum overview
    return 'forum';
  }
}


// -----------------------------------------------------------------------------


class forum_CrumbsMonoPlugin_forumTermName implements crumbs_MonoPlugin {

  function describe($api) {
    return t('Let /forum/123 be the parent path of /forum/456, if 123 is the parent of 456.');
  }

  /**
   * Forums get their parent forums as breadcrumb parent.
   * The method name matches the router path "forum/%".
   * Forums are actually taxonomy terms, just the path is different.
   */
  function findTitle__forum_x($path, $item) {
    $term = $item['map'][1];
    // Load the forum term,
    // if it hasn't been loaded due to a missing wildcard loader.
    // We could use forum_forum_load() instead, but this looks too scary
    // performance-wise, and we don't actually need it.
    // So we just use taxonomy_term_load() instead.
    $term = is_numeric($term) ? taxonomy_term_load($term) : $term;
    if (!empty($term) && !empty($term->name)) {
      return $term->name;
    }
  }
}


// -----------------------------------------------------------------------------


class forum_CrumbsMonoPlugin_forumThread implements crumbs_MonoPlugin {

  function describe($api) {
    return t('Let /forum/456 be the parent path of /node/789, if node 789 is a thread in this forum.');
  }

  /**
   * Forum nodes get their forum terms as breadcrumb parents.
   * The method name matches the router path "node/%".
   */
  function findParent__node_x($path, $item) {
    $node = $item['map'][1];
    // Load the node if it hasn't been loaded due to a missing wildcard loader.
    $node = is_numeric($node) ? node_load($node) : $node;
    if (!empty($node->forum_tid) && _forum_node_check_node_type($node)) {
      return 'forum/' . $node->forum_tid;
    }
  }
}


// -----------------------------------------------------------------------------


class forum_CrumbsMonoPlugin_forumThreadCreate implements crumbs_MonoPlugin {

  function describe($api) {
    return t('Let /forum/456 be the parent path of /node/789, if node 789 is a thread in this forum.');
  }

  /**
   * The method name matches the router path "node/add/forum".
   *
   * TODO: No hardcoded vocabulary names and node types, please!
   */
  function findParent__node_add_forum($path, $item) {
    if (is_numeric($item['original_map'][3])) {
      $term = taxonomy_term_load($item['original_map'][3]);
      if ($term && $term->vocabulary_machine_name === 'forums') {
        return 'forum/' . $term->tid;
      }
    }
  }
}
