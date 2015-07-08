<?php

namespace Drupal\crumbs\PluginSystem\Weights;

interface WeightsFamilyInterface {

  /**
   * @param string $key
   *
   * @return int|false
   */
  function keyGetWeight($key);

  /**
   * @return int|false
   */
  function getBestWeight();

}
