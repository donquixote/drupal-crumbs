<?php


function comment_crumbs_plugins() {
  return array(
    'reply' => new _comment_CrumbsPlugin__reply(),
  );
}


/**
 * Note: A findTitle() method is not necessary,
 * the title defined in comment_menu() is sufficient.
 */
class _comment_CrumbsPlugin__reply {

  function findParent__comment_reply_x($path, $item) {
    $nid = $item['fragments'][2];
    return 'node/'. $nid;
  }
}



