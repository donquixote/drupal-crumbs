<?php

namespace Drupal\crumbs\PluginSystem\Discovery\Hook;

use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;

class HookCrumbsPlugins implements HookInterface {

  /**
   * @param \Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface $api
   */
  function invokeAll(ArgumentInterface $api) {
    $this->includePluginFiles();

    foreach (module_implements('crumbs_plugins') as $module) {
      $api->setModule($module);
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

    // Organic groups is a special case,
    // because 7.x-2.x behaves different from 7.x-1.x.
    if (1
      && isset($files['og'])
      && !function_exists('og_get_group')
    ) {
      // We are using the og-7.x-1.x branch.
      $files['og'] = $dir . '/crumbs.og.2.inc';
    }

    // Since the directory order may be anything, sort alphabetically.
    ksort($files);
    foreach ($files as $file) {
      require_once $file;
    }
  }
}
