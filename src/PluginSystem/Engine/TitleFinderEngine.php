<?php

namespace Drupal\crumbs\PluginSystem\Engine;

use Drupal\crumbs\PluginSystem\Plugin\FindTitleMultiPluginOffset;

class TitleFinderEngine {

  /**
   * @var \crumbs_MonoPlugin_FindTitleInterface[]
   *   Format: $[$pluginKey] = $plugin
   */
  private $pluginsSorted;

  /**
   * @param \crumbs_MonoPlugin_FindTitleInterface[] $pluginsSorted
   */
  function __construct(array $pluginsSorted) {
    $this->pluginsSorted = $pluginsSorted;
  }

  /**
   * @param string $path
   * @param array $item
   * @param array $breadcrumb
   *
   * @return NULL|string
   *   The breadcrumb link title, or NULL.
   */
  function findTitle($path, array $item, array $breadcrumb) {
    foreach ($this->pluginsSorted as $key => $plugin) {
      $candidate = $plugin->findTitle($path, $item, $breadcrumb);
      if (isset($candidate)) {
        return $candidate;
      }
    }
    return NULL;
  }

  /**
   * @param string $path
   * @param array $item
   *
   * @return string[]
   *   The breadcrumb link title candidates.
   */
  function findAllTitles($path, array $item) {
    $all = array();
    foreach ($this->pluginsSorted as $key => $plugin) {
      if ($plugin instanceof FindTitleMultiPluginOffset) {
        $all += $plugin->findAllTitles($path, $item);
      }
      elseif ($plugin instanceof \crumbs_MonoPlugin_FindTitleInterface) {
        $candidate = $plugin->findTitle($path, $item);
        if (isset($candidate)) {
          $all[$key] = $candidate;
        }
      }
      else {
        throw new \RuntimeException('Invalid plugin type.');
      }
    }
    return $all;
  }

}
