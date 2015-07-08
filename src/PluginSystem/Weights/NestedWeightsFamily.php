<?php

namespace Drupal\crumbs\PluginSystem\Weights;

class NestedWeightsFamily implements WeightsFamilyInterface {

  /**
   * The fallback weight.
   *
   * @var int
   */
  private $fallbackWeight;

  /**
   * Weights, sorted.
   *
   * @var int[]
   *   Format: $[$key] = $weight
   */
  private $weights;

  /**
   * @var WeightsFamilyInterface[]
   */
  private $weightFamilies;

  /**
   * @param int $fallbackWeight
   * @param int[] $weights
   * @param WeightsFamilyInterface[] $weightFamilies
   */
  function __construct($fallbackWeight, array $weights, array $weightFamilies) {
    $this->fallbackWeight = $fallbackWeight;
    $this->weights = $weights;
    $this->weightFamilies = $weightFamilies;
  }

  /**
   * @param string $key
   *
   * @return int|false
   */
  function keyGetWeight($key) {
    if (isset($this->weights[$key])) {
      return $this->weights[$key];
    }
    elseif (FALSE !== $pos = strpos($key, '.')) {
      $prefix = substr($key, 0, $pos);
      if (isset($this->weightFamilies[$prefix])) {
        $suffix = substr($key, $pos + 1);
        return $this->weightFamilies[$prefix]->keyGetWeight($suffix);
      }
    }

    return $this->fallbackWeight;
  }

  /**
   * @return int|false
   */
  function getBestWeight() {
    $bestWeight = $this->fallbackWeight;
    foreach ($this->weights as $weight) {
      if ($weight !== FALSE) {
        if ($bestWeight === FALSE || $weight < $bestWeight) {
          $bestWeight = $weight;
        }
      }
    }
    foreach ($this->weightFamilies as $family) {
      $weight = $family->getBestWeight();
      if ($weight !== FALSE) {
        if ($bestWeight === FALSE || $weight < $bestWeight) {
          $bestWeight = $weight;
        }
      }
    }
    return $bestWeight;
  }

}
