<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function crumbs_crumbs_plugins(ArgumentInterface $api) {

  if (module_exists('node')) {
    $api->entityRoute('node', 'node/%', 'type', 'Node type');
  }
  if (module_exists('user')) {
    $api->entityRoute('user', 'user/%', NULL, 'User role');
  }
  if (module_exists('taxonomy')) {
    $api->entityRoute('taxonomy_term', 'taxonomy/term/%', 'vocabulary_machine_name', 'Vocabulary');
  }

  $home_title = variable_get('crumbs_home_link_title', 'Home');
  $api->monoPlugin('home_title')
    ->translateDescription('Set t("@title") as the title for the root item.', array(
      '@title' => $home_title,
    ));

  if (module_exists('token')) {
    $api->entityParentPlugin('entityParent', new crumbs_CrumbsEntityPlugin_TokenEnabled())
      ->translateDescription('Custom entity parent paths.');
  }
  else {
    $api->entityParentPlugin('entityParent', new crumbs_CrumbsEntityPlugin_TokenDisabled())
      ->translateDescription('Custom entity parent paths.');
  }
}


class crumbs_CrumbsMonoPlugin_home_title implements crumbs_MonoPlugin_FindTitleInterface {

  /**
   * @var string
   */
  private $pathToFront;

  function __construct() {
    $this->pathToFront = crumbs()->router->getFrontNormalPath();
  }

  /**
   * {@inheritdoc}
   */
  function describe($api) {
  }

  /**
   * {@inheritdoc}
   */
  function findTitle($path, $item) {
    if ($item['href'] === $this->pathToFront) {
      $home_title = variable_get('crumbs_home_link_title', 'Home');
      return t($home_title);
    }

    return NULL;
  }
}
