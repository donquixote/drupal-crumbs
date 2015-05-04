<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface;
use Drupal\crumbs\PluginApi\HookArgument\PluginCollectionArg;

class HookCrumbsPlugins implements HookInterface {

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $parentCollectionContainer
   * @param \Drupal\crumbs\PluginApi\Collector\PrimaryPluginCollectorInterface $titleCollectionContainer
   */
  function invokeAll(
    PrimaryPluginCollectorInterface $parentCollectionContainer,
    PrimaryPluginCollectorInterface $titleCollectionContainer
  ) {
    $this->includePluginFiles();

    foreach (module_implements('crumbs_plugins') as $module) {
      $api = new PluginCollectionArg($parentCollectionContainer, $titleCollectionContainer, $module);
      $f = $module . '_crumbs_plugins';
      $f($api);
    }
  }

  /**
   * Includes the module-specific plugin files in (crumbs dir)/plugins/.
   */
  protected function includePluginFiles() {

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
  }
}
