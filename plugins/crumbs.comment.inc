<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function comment_crumbs_plugins(ArgumentInterface $api) {

  $api->routeMonoPlugin('comment/reply/%', 'reply', new comment_CrumbsMonoPlugin_reply)
    ->describe("<code>node/123</code> &raquo; <code>comment/reply/123</code>");

  $api->routeMonoPlugin('comment/%', 'comment', new comment_CrumbsMonoPlugin_comment)
    ->describe("<code>node/%node</code> &raquo; <code>comment/reply/%comment</code>");

  $api->routeSkipItem('comment/%', 'skip');
}

class comment_CrumbsMonoPlugin_reply implements crumbs_MonoPlugin_FindParentInterface {

  /**
   * findParent callback for comment/reply/%.
   * Actually, system paths cam look more like comment/reply/%/%, but the router
   * path is comment/reply/%. Complain to the people who wrote comment module.
   *
   * @param string $path
   * @param array $item
   *
   * @return string
   */
  function findParent($path, $item) {
    $nid = $item['fragments'][2];
    return 'node/'. $nid;
  }
}

class comment_CrumbsMonoPlugin_comment implements crumbs_MonoPlugin_FindParentInterface {

  /**
   * Make node/% the parent for comment/%.
   * This also completes the breadcrumb for other comment/%/* paths.
   *
   * @param string $path
   * @param array $item
   *
   * @return string
   */
  function findParent($path, $item) {
    $comment = comment_load($item['original_map'][1]);
    if (!empty($comment->nid)) {
      return 'node/' . $comment->nid;
    }

    return NULL;
  }

}
