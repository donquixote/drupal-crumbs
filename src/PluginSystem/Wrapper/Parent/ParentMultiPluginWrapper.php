<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Parent;

use Drupal\crumbs\ParentFinder\Approval\CheckerInterface;
use Drupal\crumbs\PluginSystem\Weights\WeightsFamilyInterface;

class ParentMultiPluginWrapper implements ParentPluginWrapperInterface {

  /**
   * @var string
   */
  private $keyPrefix;

  /**
   * @var int|false
   */
  private $bestWeight;

  /**
   * @var \crumbs_MultiPlugin_FindParentInterface
   */
  private $multiPlugin;

  /**
   * @var \Drupal\crumbs\PluginSystem\Weights\NestedWeightsFamily
   */
  private $weightsFamily;

  /**
   * @param string $keyPrefix
   * @param \crumbs_MultiPlugin_FindParentInterface $plugin
   * @param \Drupal\crumbs\PluginSystem\Weights\WeightsFamilyInterface $weightsFamily
   */
  function __construct($keyPrefix, \crumbs_MultiPlugin_FindParentInterface $plugin, WeightsFamilyInterface $weightsFamily) {
    $this->keyPrefix = $keyPrefix;
    $this->multiPlugin = $plugin;
    $this->weightsFamily = $weightsFamily;
    $this->bestWeight = $weightsFamily->getBestWeight();
  }

  /**
   * @param \Drupal\crumbs\ParentFinder\Approval\CheckerInterface $checker
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestParent(CheckerInterface $checker, &$bestWeight, $path, array $routerItem) {
    $candidates = $this->multiPlugin->findParent($path, $routerItem);
    if (is_array($candidates) && !empty($candidates)) {
      foreach ($candidates as $key => $candidate) {
        $weight = $this->weightsFamily->keyGetWeight($key);
        if (!isset($bestWeight) || $weight < $bestWeight) {
          if ($checker->checkParentPath($candidate, $this->keyPrefix)) {
            $bestWeight = $weight;
          }
        }
      }
    }
  }

  /**
   * @return int|false
   */
  function getBestWeight() {
    return $this->bestWeight;
  }
}
