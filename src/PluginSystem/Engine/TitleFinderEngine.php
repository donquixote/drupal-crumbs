<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\TitleFinder\TitleFinderInterface;

class TitleFinderEngine implements TitleFinderInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\Wrapper\Title\TitlePluginWrapperInterface[][]
   *   Format: $[$bestWeight][] = $wrapper
   */
  private $pluginWrappersGrouped = array();

  /**
   * @param \Drupal\crumbs\PluginSystem\Wrapper\Title\TitlePluginWrapperInterface[] $pluginWrappers
   */
  function __construct(array $pluginWrappers) {
    foreach ($pluginWrappers as $wrapper) {
      $wrapperBestWeight = $wrapper->getBestWeight();
      if ($wrapperBestWeight !== FALSE) {
        $this->pluginWrappersGrouped[$wrapperBestWeight][] = $wrapper;
      }
    }
    ksort($this->pluginWrappersGrouped);
  }

  /**
   * @param string $path
   * @param array $routerItem
   * @param array[] $breadcrumb
   *
   * @return string|null
   */
  function findTitle($path, array $routerItem, array $breadcrumb = array()) {
    $bestCandidate = NULL;
    $bestWeight = NULL;
    foreach ($this->pluginWrappersGrouped as $wrapperBestWeight => $wrappers) {
      foreach ($wrappers as $wrapper) {
        if (isset($bestWeight) && $wrapperBestWeight >= $bestWeight) {
          return $bestCandidate;
        }
        $wrapper->findBestTitle($bestCandidate, $bestWeight, $path, $routerItem);
      }
    }

    return $bestCandidate;
  }

}
