<?php

namespace Drupal\crumbs\PluginSystem\Wrapper\Title;

use Drupal\crumbs\PluginSystem\Weights\WeightsFamilyInterface;

class TitleMultiPluginWrapper implements TitlePluginWrapperInterface {

  /**
   * @var string
   */
  private $keyPrefix;

  /**
   * @var int|false
   */
  private $bestWeight;

  /**
   * @var \crumbs_MultiPlugin_FindTitleInterface
   */
  private $multiPlugin;

  /**
   * @var \Drupal\crumbs\PluginSystem\Weights\NestedWeightsFamily
   */
  private $weightsFamily;

  /**
   * @param string $keyPrefix
   * @param \crumbs_MultiPlugin_FindTitleInterface $plugin
   * @param \Drupal\crumbs\PluginSystem\Weights\WeightsFamilyInterface $weightsFamily
   */
  function __construct($keyPrefix, \crumbs_MultiPlugin_FindTitleInterface $plugin, WeightsFamilyInterface $weightsFamily) {
    $this->keyPrefix = $keyPrefix;
    $this->multiPlugin = $plugin;
    $this->weightsFamily = $weightsFamily;
    $this->bestWeight = $weightsFamily->getBestWeight();
  }

  /**
   * @param string|null $bestCandidate
   * @param int|null $bestWeight
   * @param string $path
   * @param array $routerItem
   */
  function findBestTitle(&$bestCandidate, &$bestWeight, $path, array $routerItem) {
    $candidates = $this->multiPlugin->findTitle($path, $routerItem);
    if (is_array($candidates) && !empty($candidates)) {
      foreach ($candidates as $key => $candidate) {
        $weight = $this->weightsFamily->keyGetWeight($key);
        if (!isset($bestWeight) || $weight < $bestWeight) {
          $bestCandidate = $candidate;
          $bestWeight = $weight;
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
