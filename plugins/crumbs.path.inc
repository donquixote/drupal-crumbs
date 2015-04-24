<?php
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

/**
 * Implements hook_crumbs_plugins().
 *
 * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
 */
function path_crumbs_plugins(ArgumentInterface $api) {
  // Just one plugin for the entire module.
  $api->monoPlugin('alias')
    ->translateDescription('Url alias')
    ->disabledByDefault();
}


/**
 * Implementation of class hook class_CrumbsParentFinder
 * on the behalf of path module.
 */
class path_CrumbsMonoPlugin_alias implements crumbs_MonoPlugin_FindParentInterface {

  /**
   * {@inheritdoc}
   */
  function findParent($path, $item) {
    if ($alias = $item['alias']) {
      return crumbs_reduce_path($alias, 1);
    }

    return NULL;
  }
}
