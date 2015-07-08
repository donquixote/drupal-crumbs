<?php

namespace Drupal\crumbs\PluginSystem\Weights;

use Drupal\crumbs\PluginSystem\Tree\TreeUtil;
use Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface;

class WeightsFamilyUtil {

  /**
   * @param array $weights
   *
   * @return \Drupal\crumbs\PluginSystem\Weights\WeightsFamilyInterface
   */
  static function createFromWeights(array $weights) {
    $weights = TreeUtil::spliceCandidates($weights);
    $fallbackWeight = isset($weights['*'])
      ? $weights['*']
      : 0;
    unset($weights['*']);
    $families = array();
    foreach ($weights as $key => $weight_s) {
      if (is_array($weight_s)) {
        $families[$key] = static::createFromWeights($weight_s);
        unset($weights[$key]);
      }
    }
    return static::create($fallbackWeight, $weights, $families);
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\TreePosition\TreePositionInterface $position
   *
   * @return \Drupal\crumbs\PluginSystem\Weights\WeightsFamilyInterface
   */
  static function createFromTreePosition(TreePositionInterface $position) {
    if ($position->isLeaf()) {
      throw new \InvalidArgumentException('A leaf position does not have a weights family.');
    }
    $fallbackWeight = $position->getWeightOrFalse();
    $weights = array();
    $families = array();
    foreach ($position->getChildren() as $key => $childPosition) {
      if ($childPosition->isLeaf()) {
        $weights[$key] = $childPosition->getWeightOrFalse();
      }
      else {
        $families[$key] = static::createFromTreePosition($childPosition);
      }
    }
    return static::create($fallbackWeight, $weights, $families);
  }

  /**
   * @param int $fallbackWeight
   * @param int[] $weights
   * @param WeightsFamilyInterface[] $weightFamilies
   *
   * @return \Drupal\crumbs\PluginSystem\Weights\WeightsFamilyInterface
   */
  static function create($fallbackWeight, array $weights, array $weightFamilies) {
    if (!empty($families)) {
      return new NestedWeightsFamily($fallbackWeight, $weights, $families);
    }
    elseif (!empty($weights)) {
      return new SimpleWeightsFamily($fallbackWeight, $weights);
    }
    else {
      return new TrivialWeightsFamily($fallbackWeight);
    }
  }

}
