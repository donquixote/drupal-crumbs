<?php

namespace Drupal\crumbs\PluginSystem\Weights;

class TrivialWeightsFamily implements WeightsFamilyInterface {

  /**
   * @var int
   */
  private $weight;

  /**
   * @param int $weight
   */
  function __construct($weight) {
    $this->weight = $weight;
  }

  /**
   * @param string $key
   *
   * @return int|false
   */
  function keyGetWeight($key) {
    return $this->weight;
  }

  /**
   * Gets the best weight in this family.
   *
   * @return int|false
   */
  function getBestWeight() {
    return $this->weight;
  }
}
