<?php

namespace Drupal\crumbs\PluginSystem\Discovery;

use Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface;
use Drupal\crumbs\PluginApi\Family\CallbackRecovery\CallbackHookArgument;
use Drupal\crumbs\PluginApi\Family\DefaultImplementation\HookArgument;
use Drupal\crumbs\PluginSystem\Discovery\Hook\HookCrumbsPlugins;
use Drupal\crumbs\PluginSystem\Discovery\Hook\HookInterface;

class PluginDiscovery {

  /**
   * @var bool
   */
  protected $pluginFilesIncluded = FALSE;

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $parentPluginCollector
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $titlePluginCollector
   * @param bool[] $uncacheableModules
   */
  function discoverPlugins(
    RoutelessPluginCollectorInterface $parentPluginCollector,
    RoutelessPluginCollectorInterface $titlePluginCollector,
    array &$uncacheableModules
  ) {
    $this->includePluginFiles();
    foreach (module_implements('crumbs_plugins') as $module) {
      $hasUncachablePlugins = FALSE;
      $api = new HookArgument(
        $parentPluginCollector,
        $titlePluginCollector,
        $hasUncachablePlugins,
        $module);
      $f = $module . '_crumbs_plugins';
      $f($api);
      if ($hasUncachablePlugins) {
        $uncacheableModules[$module] = TRUE;
      }
    }

    $parentPluginCollector->finalize();
    $titlePluginCollector->finalize();
  }

  /**
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $parentPluginCollector
   * @param \Drupal\crumbs\PluginApi\Collector\RoutelessPluginCollectorInterface $titlePluginCollector
   * @param bool[] $uncacheableModules
   */
  function discoverUncacheablePlugins(
    RoutelessPluginCollectorInterface $parentPluginCollector,
    RoutelessPluginCollectorInterface $titlePluginCollector,
    array $uncacheableModules
  ) {
    $this->includePluginFiles();
    foreach ($uncacheableModules as $module => $cTrue) {
      $api = new CallbackHookArgument(
        $parentPluginCollector,
        $titlePluginCollector,
        $module);
      $f = $module . '_crumbs_plugins';
      $f($api);
    }

    $parentPluginCollector->finalize();
    $titlePluginCollector->finalize();
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
