<?php

namespace Drupal\crumbs\PluginSystem\Weights;

class SimpleWeightsFamily implements WeightsFamilyInterface {

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
   * @param int $fallbackWeight
   * @param int[] $weights
   */
  function __construct($fallbackWeight, array $weights) {
    $this->fallbackWeight = $fallbackWeight;
    $this->weights = $weights;
  }

  /**
   * @param string $key
   *
   * @return int|false
   */
  function keyGetWeight($key) {
    return isset($this->weights[$key])
      ? $this->weights[$key]
      : $this->fallbackWeight;
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
    return $bestWeight;
  }

}
