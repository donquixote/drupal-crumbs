<?php


/**
 * Implementation of hook_crumbs_plugins()
 */
function crumbs_crumbs_plugins($api) {
  $api->monoPlugin('home_title');
}


class crumbs_CrumbsMonoPlugin_home_title implements crumbs_MonoPlugin {

  protected $frontNormalPath;

  function __construct() {
    $this->frontNormalPath = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
  }

  function describe($api) {
    return t('Set "Home" as the title for the root item.');
  }

  function findTitle($path, array $item) {
    if ($path === $this->frontNormalPath) {
      return t('Home');
    }
  }
}
