<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;
use Drupal\crumbs\Util;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function blog_crumbs_plugins(ArgumentInterface $api) {

  $api->routeMonoPlugin('node/%', 'authorPage', new blog_CrumbsMonoPlugin_authorPage);
  $api->describeFindParent('authorPage', t("Author's blog page (blog/*) &raquo; Blog post (node/*)."));

  $api->routeMonoPlugin('blog/%', 'authorName', new blog_CrumbsMonoPlugin_authorName);
  $api->describeFindTitle('authorName', t('"!name\'s blog", where !name is the author\'s username.'));
}


class blog_CrumbsMonoPlugin_authorPage implements crumbs_MonoPlugin_FindParentInterface {

  /**
   * Still under constructon..
   *
   * @param string $path
   * @param array $item
   *
   * @return null|string
   */
  function findParent($path, $item) {
    $node = Util::itemExtractEntity($item, 'node', 1);

    if ($node === FALSE || $node->type !== 'blog') {
      return NULL;
    }

    return 'blog/' . $node->uid;
  }
}


class blog_CrumbsMonoPlugin_authorName implements crumbs_MonoPlugin_FindTitleInterface {

  /**
   * Still under constructon..
   *
   * @param string $path
   * @param array $item
   *
   * @return null|string
   */
  function findTitle($path, $item) {
    if (FALSE === $user = Util::itemExtractEntity($item, 'user', 1)) {
      return NULL;
    }

    return t("!name's blog", array('!name' => format_username($user)));
  }
}
