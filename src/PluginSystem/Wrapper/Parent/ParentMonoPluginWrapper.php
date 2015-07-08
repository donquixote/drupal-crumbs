<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Parent;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;

class ParentMonoPluginWrapper implements ParentPluginWrapperInterface {

  /**
   * @var string
   */
  private $key;

  /**
   * @var int
   */
  private $weight;

  /**
   * @var \crumbs_MonoPlugin_FindParentInterface
   */
  private $monoPlugin;

  /**
   * @param string $key
   * @param \crumbs_MonoPlugin_FindParentInterface $plugin
   * @param int $weight
   */
  function __construct($key, \crumbs_MonoPlugin_FindParentInterface $plugin, $weight) {
    $this->key = $key;
    $this->monoPlugin = $plugin;
    $this->weight = $weight;
  }

  /**
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestParent(CheckerInterface $checker, &$bestWeight, $path, array $routerItem) {
    if (!isset($bestWeight) || $this->weight < $bestWeight) {
      $candidate = $this->monoPlugin->findParent($path, $routerItem);
      if (isset($candidate)) {
        if ($checker->checkParentPath($candidate, $this->key)) {
          $bestWeight = $this->weight;
        }
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
