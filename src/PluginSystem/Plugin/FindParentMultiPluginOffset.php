<?php

namespace Drupal\crumbs\PluginSystem\Plugin;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
use Drupal\crumbs\ParentFinder\ParentFinderInterface;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;

class FindParentMultiPluginOffset implements ParentFinderInterface {

  /**
   * @var \crumbs_MultiPlugin_FindParentInterface
   */
  private $multiPlugin;

  /**
   * @var string
   */
  private $pluginKey;

  /**
   * @var \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap
   */
  private $localStatusMap;

  /**
   * @var int
   */
  private $weight;

  /**
   * @param \crumbs_MultiPlugin_FindParentInterface $multiPlugin
   * @param string $pluginKey
   * @param \Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap $localStatusMap
   * @param int $weight
   */
  function __construct(
    \crumbs_MultiPlugin_FindParentInterface $multiPlugin,
    $pluginKey,
    PluginStatusWeightMap $localStatusMap,
    $weight
  ) {
    $this->multiPlugin = $multiPlugin;
    $this->pluginKey = $pluginKey;
    $this->localStatusMap = $localStatusMap;
    $this->weight = $weight;
  }

  /**
   * @param array $routerItem
   *   The router item to find a parent for..
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   *
   * @return bool
   *   The parent router item, or NULL.
   */
  function findParentRouterItem(array $routerItem, CheckerInterface $checker) {
    $path = $routerItem['link_path'];
    $candidates = $this->multiPlugin->findParent($path, $routerItem);
    if (!is_array($candidates)) {
      // Some plugins return NULL instead of an empty array, if they find nothing.
      return FALSE;
    }
    foreach ($candidates as $key => $parentPathCandidate) {
      if (!isset($parentPathCandidate)) {
        continue;
      }
      if ($this->weight !== $this->localStatusMap->keyGetWeightOrFalse($key)) {
        continue;
      }
      $absoluteKey = $this->pluginKey . '.' . $key;
      if ($checker->checkParentPath($parentPathCandidate, $absoluteKey)) {
        return TRUE;
      }
    }
    return FALSE;
  }
}
