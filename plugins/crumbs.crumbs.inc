<?php


/**
 * Implements hook_crumbs_plugins().
 *
 * @param crumbs_InjectedAPI_hookCrumbsPlugins $api
 */
function crumbs_crumbs_plugins($api) {
  $api->entityRoute('node', 'node/%', 'type', 'Node type');
  $api->entityRouteSpecial('user', 'user/%', 'crumbs_MultiPlugin_UserParent');
  if (module_exists('taxonomy')) {
    $api->entityRoute('taxonomy_term', 'taxonomy/term/%', 'vocabulary_machine_name', 'Vocabulary');
  }

  $api->monoPlugin('home_title');
  $suffix = module_exists('token') ? 'TokenEnabled' : 'TokenDisabled';
  $api->entityParentPlugin('entityParent', $suffix);
}


class crumbs_CrumbsMonoPlugin_home_title implements crumbs_MonoPlugin_FindTitleInterface {

  protected $frontNormalPath;
  protected $homeTitle;

  function __construct() {
    $this->frontNormalPath = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
    $this->homeTitle = variable_get('crumbs_home_link_title', 'Home');
  }

  /**
   * @inheritdoc
   */
  function describe($api) {
    return t('Set t("@title") as the title for the root item.', array(
      '@title' => $this->homeTitle,
    ));
  }

  /**
   * @inheritdoc
   */
  function findTitle($path, $item) {
    if ($path === $this->frontNormalPath) {
      return t($this->homeTitle);
    }
  }
}