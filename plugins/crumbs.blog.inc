<?php


function blog_crumbs_plugins() {
  return new _blog_CrumbsPlugin();
}


class _blog_CrumbsPlugin {

  function findParent__node__($path, $item) {
    $node = $item['map'][1];
    if ($node->type === 'blog') {
      return 'blog/'. $node->uid;
    }
  }

  function findTitle__blog__($path, $item) {
    $user = $item['map'][1];
    return t("!name's blog", array('!name' => $user->name));
  }
}


