<?php

namespace Drupal\crumbs\PluginSystem\Discovery;

use Drupal\crumbs\PluginApi\Family\DefaultImplementation\HookArgument;
use Drupal\crumbs\PluginSystem\Tree\TreeNode;

class PluginDiscovery {

  /**
   * @var bool
   */
  protected $pluginFilesIncluded = FALSE;

  /**
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findParentTree
   *   Hierarchy of parent-finding plugins.
   * @param \Drupal\crumbs\PluginSystem\Tree\TreeNode $findTitleTree
   *   Hierarchy of title-finding plugins.
   */
  function discoverPlugins(TreeNode $findParentTree, TreeNode $findTitleTree) {
    $system_list = system_list('module_enabled');
    $this->includePluginFiles();
    $entityRoutes = array();
    foreach (module_implements('crumbs_plugins') as $module) {

      $module_name = isset($system_list[$module]->info['name'])
        ? $system_list[$module]->info['name']
        : $module;

      $api = new HookArgument(
        $moduleFindParentTree = $findParentTree->child($module)->describe($module_name),
        $moduleFindTitleTree = $findTitleTree->child($module)->describe($module_name),
        $module);

      $f = $module . '_crumbs_plugins';
      $f($api);

      if ($moduleFindParentTree->isEmpty()) {
        $findParentTree->unchild($module);
      }

      if ($moduleFindTitleTree->isEmpty()) {
        $findTitleTree->unchild($module);
      }

      $entityRoutes += $api->getEntityRoutes();
    }

    $findParentTree->unfoldEntityPlugins($entityRoutes);
    $findTitleTree->unfoldEntityPlugins($entityRoutes);
  }

  /**
   * Includes the module-specific plugin files in (crumbs dir)/plugins/.
   */
  protected function includePluginFiles() {

    if ($this->pluginFilesIncluded) {
      return;
    }

    $dir = drupal_get_path('module', 'crumbs') . '/plugins';

    $files = array();
    foreach (scandir($dir) as $candidate) {
      if (preg_match('/^crumbs\.(.+)\.inc$/', $candidate, $m)) {
        if (module_exists($m[1])) {
          $files[$m[1]] = $dir . '/' . $candidate;
        }
      }
    }

    // Since the directory order may be anything, sort alphabetically.
    // @todo Probably the sorting is not necessary at all.
    ksort($files);
    foreach ($files as $file) {
      require_once $file;
    }

    $this->pluginFilesIncluded = TRUE;
  }
}
