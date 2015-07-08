<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Title;

class TitleMonoPluginWrapper implements TitlePluginWrapperInterface {

  /**
   * @var string
   */
  private $key;

  /**
   * @var int
   */
  private $weight;

  /**
   * @var \crumbs_MonoPlugin_FindTitleInterface
   */
  private $monoPlugin;

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin_FindTitleInterface $plugin
   * @param int $weight
   */
  function __construct($key, \crumbs_MonoPlugin_FindTitleInterface $plugin, $weight) {
    $this->key = $key;
    $this->monoPlugin = $plugin;
    $this->weight = $weight;
  }

  /**
   * @param string|null $bestCandidate
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestTitle(&$bestCandidate, &$bestWeight, $path, array $routerItem) {
    if (!isset($bestWeight) || $this->weight < $bestWeight) {
      $candidate = $this->monoPlugin->findTitle($path, $routerItem);
      if (isset($candidate)) {
        $bestCandidate = $candidate;
        $bestWeight = $this->weight;
      }
    }
  }

  /**
   * @return int|false
   */
  function getBestWeight() {
    return $this->weight;
  }
}
