<?php


/**
 * Implements hook_crumbs_plugins().
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function blog_crumbs_plugins($api) {
  $api->monoPlugin('authorPage');
  $api->monoPlugin('authorName');
}


class blog_CrumbsMonoPlugin_authorPage implements crumbs_MonoPlugin {

  /**
   * @inheritdoc
   */
  function describe($api) {
    $api->titleWithLabel(t("The author's blog page"), t('Parent'));
  }

  /**
   * Still under constructon..
   */
  function findParent__node_x($path, $item) {
    $node = $item['map'][1];
    // Load the node if it hasn't been loaded due to a missing wildcard loader.
    $node = is_numeric($node) ? node_load($node) : $node;

    if ($node->type === 'blog') {
      return 'blog/' . $node->uid;
    }
  }
}


class blog_CrumbsMonoPlugin_authorName implements crumbs_MonoPlugin {

  /**
   * @inheritdoc
   */
  function describe($api) {
    $api->titleWithLabel(t('"!name\'s blog", where !name is the author\'s username.'), t('Title'));
  }

  /**
   * Still under constructon..
   */
  function findTitle__blog_x($path, $item) {
    $user = $item['map'][1];
    // Load the user if it hasn't been loaded due to a missing wildcard loader.
    $user = is_numeric($user) ? user_load($user) : $user;

    return t("!name's blog", array('!name' => format_username($user)));
  }
}
